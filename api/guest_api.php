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
    case 'checkin':
        $qr_code = trim($_POST['qr_code'] ?? '');
        $stmt = $pdo->prepare("SELECT v.*, g.identity_number, g.fullname FROM visits v JOIN guests g ON v.guest_id = g.id WHERE v.qr_code = ? AND v.status = 'waiting'");
        $stmt->execute([$qr_code]);
        $visit = $stmt->fetch();
        
        if (!$visit) {
            echo json_encode(['success' => false, 'message' => 'QR tidak valid atau sudah check-in']);
            exit;
        }
        
        // Check blacklist
        $stmt = $pdo->prepare("SELECT id, reason FROM blacklist WHERE identity_number = ?");
        $stmt->execute([$visit['identity_number']]);
        $bl = $stmt->fetch();
        if ($bl) {
            securityLog($pdo, $_SESSION['user_id'], 'BLACKLIST_ATTEMPT', "Tamu blacklist mencoba masuk: {$visit['fullname']}", $visit['guest_id']);
            echo json_encode(['success' => false, 'message' => "⛔ AKSES DITOLAK! Tamu termasuk BLACKLIST. Alasan: " . ($bl['reason'] ?? '-'), 'blacklisted' => true]);
            exit;
        }
        
        $pdo->prepare("UPDATE visits SET check_in_time = NOW(), status = 'checked_in', security_in_id = ? WHERE id = ?")
            ->execute([$_SESSION['user_id'], $visit['id']]);
        securityLog($pdo, $_SESSION['user_id'], 'CHECK_IN', "Check-in berhasil: {$visit['fullname']}", $visit['guest_id']);
        echo json_encode(['success' => true, 'message' => "✅ Check-in {$visit['fullname']} berhasil!"]);
        break;
        
    case 'register':
        $identity = trim($_POST['identity_number'] ?? '');
        $fullname = trim($_POST['fullname'] ?? '');
        $purpose  = trim($_POST['purpose'] ?? '');
        
        if (empty($fullname) || empty($identity) || empty($purpose)) {
            echo json_encode(['success' => false, 'message' => 'Nama, No Identitas, dan Keperluan wajib diisi!']);
            exit;
        }
        
        // Check blacklist
        $stmt = $pdo->prepare("SELECT id FROM blacklist WHERE identity_number = ?");
        $stmt->execute([$identity]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Identitas ini terblokir oleh sistem (blacklist).']);
            exit;
        }
        
        $pdo->prepare("INSERT INTO guests (fullname, identity_number, phone, institution, vehicle_number) VALUES (?,?,?,?,?)")
            ->execute([$fullname, $identity, $_POST['phone'] ?? '', $_POST['institution'] ?? '', $_POST['vehicle_number'] ?? '']);
        $guestId = $pdo->lastInsertId();
        
        $qrCode = 'DIG-' . strtoupper(substr(uniqid(), -6)) . '-' . rand(100, 999);
        $pdo->prepare("INSERT INTO visits (guest_id, purpose, destination, status, qr_code) VALUES (?,?,?,'waiting',?)")
            ->execute([$guestId, $purpose, $_POST['destination'] ?? 'Umum', $qrCode]);
        
        securityLog($pdo, $_SESSION['user_id'] ?? null, 'GUEST_REGISTER', "Tamu baru terdaftar: $fullname", $guestId);
        echo json_encode(['success' => true, 'qr_code' => $qrCode, 'guest_name' => $fullname]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>