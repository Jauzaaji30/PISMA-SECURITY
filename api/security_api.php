<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'get_today_guests':
        $search = $_GET['search'] ?? '';
        $status_filter = $_GET['status'] ?? '';
        
        $sql = "SELECT v.*, g.fullname, g.institution, g.phone, g.identity_number, g.vehicle_number 
                FROM visits v 
                JOIN guests g ON v.guest_id = g.id 
                WHERE (DATE(v.created_at) = CURDATE() OR v.status = 'waiting')";
        
        $params = [];
        if ($search) {
            $sql .= " AND (g.fullname LIKE ? OR g.identity_number LIKE ? OR v.qr_code LIKE ?)";
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
        }
        if ($status_filter && $status_filter !== 'all') {
            $sql .= " AND v.status = ?";
            $params[] = $status_filter;
        }
        $sql .= " ORDER BY v.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['guests' => $stmt->fetchAll()]);
        break;
        
    case 'get_logs':
        $limit = intval($_GET['limit'] ?? 30);
        $stmt = $pdo->query("SELECT sl.*, u.fullname as user_fullname FROM security_logs sl LEFT JOIN users u ON sl.user_id = u.id ORDER BY sl.created_at DESC LIMIT $limit");
        echo json_encode(['logs' => $stmt->fetchAll()]);
        break;
    
    case 'checkout':
        $qr_code = $_POST['qr_code'] ?? '';
        $stmt = $pdo->prepare("SELECT v.*, g.fullname FROM visits v JOIN guests g ON v.guest_id = g.id WHERE v.qr_code = ? AND v.status = 'checked_in'");
        $stmt->execute([$qr_code]);
        $visit = $stmt->fetch();
        
        if (!$visit) {
            echo json_encode(['success' => false, 'message' => 'QR tidak valid atau tamu belum check-in']);
            exit;
        }
        
        $pdo->prepare("UPDATE visits SET checkout_time = NOW(), status = 'completed' WHERE id = ?")
            ->execute([$visit['id']]);
        securityLog($pdo, $_SESSION['user_id'], 'CHECK_OUT', "Check-out: {$visit['fullname']}", $visit['guest_id']);
        echo json_encode(['success' => true, 'message' => "Check-out {$visit['fullname']} berhasil"]);
        break;
    
    case 'get_blacklist':
        $stmt = $pdo->query("SELECT * FROM blacklist ORDER BY created_at DESC");
        echo json_encode(['blacklist' => $stmt->fetchAll()]);
        break;
    
    case 'add_blacklist':
        $identity = trim($_POST['identity_number'] ?? '');
        $reason = trim($_POST['reason'] ?? 'Tidak ada alasan');
        if (empty($identity)) {
            echo json_encode(['success' => false, 'message' => 'No. Identitas wajib diisi']);
            exit;
        }
        // Check existing
        $check = $pdo->prepare("SELECT id FROM blacklist WHERE identity_number = ?");
        $check->execute([$identity]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Identitas sudah ada di blacklist']);
            exit;
        }
        $pdo->prepare("INSERT INTO blacklist (identity_number, reason) VALUES (?, ?)")->execute([$identity, $reason]);
        securityLog($pdo, $_SESSION['user_id'], 'BLACKLIST_ADD', "Tambah blacklist: $identity");
        echo json_encode(['success' => true, 'message' => 'Berhasil ditambahkan ke blacklist']);
        break;
    
    case 'remove_blacklist':
        $id = intval($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM blacklist WHERE id = ?")->execute([$id]);
        securityLog($pdo, $_SESSION['user_id'], 'BLACKLIST_REMOVE', "Hapus blacklist ID: $id");
        echo json_encode(['success' => true, 'message' => 'Berhasil dihapus dari blacklist']);
        break;
    
    case 'live_stats':
        $today_total = $pdo->query("SELECT COUNT(*) as c FROM visits WHERE DATE(created_at) = CURDATE()")->fetch()['c'];
        $today_checkin = $pdo->query("SELECT COUNT(*) as c FROM visits WHERE DATE(check_in_time) = CURDATE() AND status IN ('checked_in','completed')")->fetch()['c'];
        $today_checkout = $pdo->query("SELECT COUNT(*) as c FROM visits WHERE DATE(checkout_time) = CURDATE() AND status = 'completed'")->fetch()['c'];
        $waiting = $pdo->query("SELECT COUNT(*) as c FROM visits WHERE status = 'waiting'")->fetch()['c'];
        echo json_encode([
            'today_total' => $today_total,
            'today_checkin' => $today_checkin,
            'today_checkout' => $today_checkout,
            'waiting' => $waiting
        ]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>