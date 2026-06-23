<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak. Hanya Admin.']);
    exit;
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'dashboard_stats':
        $total_guests  = $pdo->query("SELECT COUNT(*) as c FROM guests")->fetch()['c'];
        $today_total   = $pdo->query("SELECT COUNT(*) as c FROM visits WHERE DATE(created_at) = CURDATE()")->fetch()['c'];
        $today_checkin = $pdo->query("SELECT COUNT(*) as c FROM visits WHERE DATE(check_in_time) = CURDATE() AND status IN ('checked_in','completed')")->fetch()['c'];
        $today_checkout= $pdo->query("SELECT COUNT(*) as c FROM visits WHERE DATE(checkout_time) = CURDATE() AND status = 'completed'")->fetch()['c'];
        $waiting       = $pdo->query("SELECT COUNT(*) as c FROM visits WHERE status = 'waiting'")->fetch()['c'];
        $blacklist      = $pdo->query("SELECT COUNT(*) as c FROM blacklist")->fetch()['c'];
        $total_users   = $pdo->query("SELECT COUNT(*) as c FROM users WHERE is_active = 1")->fetch()['c'];
        
        // Weekly chart data (last 7 days)
        $weekly = $pdo->query("
            SELECT DATE(created_at) as date, COUNT(*) as total 
            FROM visits 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
            GROUP BY DATE(created_at) 
            ORDER BY date ASC
        ")->fetchAll();
        
        // Busiest hours today
        $hours = $pdo->query("
            SELECT HOUR(check_in_time) as hour, COUNT(*) as total 
            FROM visits 
            WHERE DATE(check_in_time) = CURDATE() AND check_in_time IS NOT NULL
            GROUP BY HOUR(check_in_time) 
            ORDER BY hour ASC
        ")->fetchAll();
        
        echo json_encode([
            'total_guests'   => $total_guests,
            'today_total'    => $today_total,
            'today_checkin'  => $today_checkin,
            'today_checkout' => $today_checkout,
            'waiting'        => $waiting,
            'blacklist_count'=> $blacklist,
            'total_users'    => $total_users,
            'weekly_data'    => $weekly,
            'hourly_data'    => $hours,
        ]);
        break;
        
    case 'audit_logs':
        $limit = intval($_GET['limit'] ?? 50);
        $stmt = $pdo->query("
            SELECT sl.*, u.fullname as user_fullname 
            FROM security_logs sl 
            LEFT JOIN users u ON sl.user_id = u.id 
            ORDER BY sl.created_at DESC 
            LIMIT $limit
        ");
        echo json_encode(['logs' => $stmt->fetchAll()]);
        break;
    
    case 'get_users':
        $stmt = $pdo->query("SELECT id, username, fullname, role, is_active, last_login, created_at FROM users ORDER BY created_at DESC");
        echo json_encode(['users' => $stmt->fetchAll()]);
        break;
    
    case 'toggle_user':
        $id = intval($_POST['id'] ?? 0);
        $user = $pdo->prepare("SELECT id, is_active FROM users WHERE id = ?");
        $user->execute([$id]);
        $u = $user->fetch();
        if (!$u) { echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']); exit; }
        $new_status = $u['is_active'] ? 0 : 1;
        $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$new_status, $id]);
        securityLog($pdo, $_SESSION['user_id'], 'TOGGLE_USER', ($new_status ? 'Mengaktifkan' : 'Menonaktifkan') . " user ID: $id");
        echo json_encode(['success' => true, 'new_status' => $new_status]);
        break;
    
    case 'create_user':
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullname = trim($_POST['fullname'] ?? '');
        $role = $_POST['role'] ?? 'security';
        
        if (empty($username) || empty($password) || empty($fullname)) {
            echo json_encode(['success' => false, 'message' => 'Semua field harus diisi!']);
            exit;
        }
        
        if (strlen($password) < 4) {
            echo json_encode(['success' => false, 'message' => 'Password minimal 4 karakter!']);
            exit;
        }
        
        // Check existing
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Username sudah digunakan!']);
            exit;
        }
        
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, role, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$username, $hashed, $fullname, $role]);
        
        securityLog($pdo, $_SESSION['user_id'], 'CREATE_USER', "Membuat user baru: $username ($role)");
        echo json_encode(['success' => true, 'message' => 'User berhasil ditambahkan!']);
        break;
    
    case 'get_all_guests':
        $page = intval($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';
        
        $sql = "SELECT v.*, g.fullname, g.institution, g.identity_number, g.phone, g.vehicle_number
                FROM visits v JOIN guests g ON v.guest_id = g.id";
        $params = [];
        if ($search) {
            $sql .= " WHERE g.fullname LIKE ? OR g.identity_number LIKE ?";
            $params = ["%$search%", "%$search%"];
        }
        $sql .= " ORDER BY v.created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $count_sql = "SELECT COUNT(*) as c FROM visits v JOIN guests g ON v.guest_id = g.id";
        if ($search) $count_sql .= " WHERE g.fullname LIKE '%$search%' OR g.identity_number LIKE '%$search%'";
        $total = $pdo->query($count_sql)->fetch()['c'];
        
        echo json_encode(['visits' => $stmt->fetchAll(), 'total' => $total, 'pages' => ceil($total / $limit)]);
        break;
        
    case 'export_csv':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="security_logs_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'User', 'Aksi', 'Deskripsi', 'IP Address', 'Waktu']);
        $stmt = $pdo->query("SELECT sl.id, u.fullname, sl.action, sl.description, sl.ip_address, sl.created_at FROM security_logs sl LEFT JOIN users u ON sl.user_id = u.id ORDER BY sl.created_at DESC");
        while ($row = $stmt->fetch()) {
            fputcsv($output, $row);
        }
        fclose($output);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>