<?php
// api/config.php
$host = 'localhost';
$dbname = 'dig_security';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

function securityLog($pdo, $userId, $action, $description, $guestId = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $pdo->prepare("INSERT INTO security_logs (user_id, action, description, ip_address, guest_id) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$userId, $action, $description, $ip, $guestId]);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}
?>