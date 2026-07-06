<?php
header('Content-Type: application/json');
require_once 'config.php';

// Allow both GET and POST for register
$action = $_REQUEST['action'] ?? '';

if ($action === 'register') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $role = 'security'; // Default role, admin only via manual DB
    
    // Validate inputs
    if (empty($username) || empty($password) || empty($fullname)) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi!']);
        exit;
    }
    
    // Password length check
    if (strlen($password) < 4) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 4 karakter!']);
        exit;
    }
    
    // Cek username existence
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username sudah digunakan, silakan pilih yang lain.']);
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, role, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$username, $hashed_password, $fullname, $role]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Akun berhasil dibuat! Silakan login.'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid action']);
}
?>