<?php
/**
 * SCRIPT UNTUK HASH PASSWORD
 * Jalankan file ini untuk generate hash password baru
 * 
 * Cara menggunakan:
 * 1. Akses file ini via browser: http://localhost/digsecurity/hash_password.php
 * 2. Atau jalankan via terminal: php hash_password.php
 */

// Jika diakses via browser
if (php_sapi_name() !== 'cli') {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Password Hash Generator - PISMA Security</title>
        <style>
            body { font-family: monospace; max-width: 800px; margin: 50px auto; padding: 20px; background: #0f172a; color: #e2e8f0; }
            .container { background: #1e293b; padding: 30px; border-radius: 15px; }
            input, button { padding: 10px 15px; margin: 10px 0; border-radius: 8px; border: none; }
            input { background: #0f172a; color: white; width: 100%; }
            button { background: #3b82f6; color: white; cursor: pointer; font-weight: bold; }
            button:hover { background: #2563eb; }
            .result { background: #0f172a; padding: 15px; border-radius: 8px; margin-top: 20px; overflow-x: auto; }
            .hash { color: #10b981; font-family: monospace; word-break: break-all; }
            .warning { color: #f59e0b; }
            hr { border-color: #334155; margin: 20px 0; }
            table { width: 100%; border-collapse: collapse; }
            th, td { text-align: left; padding: 8px; border-bottom: 1px solid #334155; }
            th { color: #94a3b8; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🔐 Password Hash Generator</h1>
            <p>Gunakan tool ini untuk generate hash password yang akan diupdate ke database.</p>
            
            <form method='POST'>
                <input type='text' name='password' placeholder='Masukkan password yang ingin dihash' required>
                <button type='submit'>Generate Hash</button>
            </form>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        $password = $_POST['password'];
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        echo "<div class='result'>
            <strong>📝 Password Asli:</strong><br>
            <code>{$password}</code>
            <br><br>
            <strong>🔒 Hash Result:</strong><br>
            <code class='hash'>{$hash}</code>
            <br><br>
            <strong>📋 SQL Update Query:</strong><br>
            <code style='background:#0f172a; padding:10px; display:block; border-radius:5px;'>
            UPDATE users SET password = '{$hash}' WHERE username = 'nama_user';
            </code>
        </div>";
    }
    
    echo "<hr>
    <h2>📊 Update Password User</h2>
    <form method='POST' action='?action=update'>
        <input type='text' name='username' placeholder='Username' required>
        <input type='password' name='new_password' placeholder='Password Baru' required>
        <button type='submit' style='background:#10b981'>Update Password User</button>
    </form>";
    
    if (isset($_GET['action']) && $_GET['action'] === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'api/config.php';
        
        $username = $_POST['username'];
        $new_password = $_POST['new_password'];
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->execute([$hash, $username]);
            
            if ($stmt->rowCount() > 0) {
                echo "<div class='result' style='background:#064e3b'>
                    ✅ Password untuk user <strong>{$username}</strong> berhasil diupdate!
                </div>";
            } else {
                echo "<div class='result' style='background:#7f1d1d'>
                    ⚠️ User <strong>{$username}</strong> tidak ditemukan!
                </div>";
            }
        } catch (Exception $e) {
            echo "<div class='result' style='background:#7f1d1d'>
                ❌ Error: " . $e->getMessage() . "
            </div>";
        }
    }
    
    echo "<hr>
    <h2>📋 Daftar User Saat Ini</h2>";
    
    try {
        require_once 'api/config.php';
        $users = $pdo->query("SELECT id, username, fullname, role, is_active FROM users ORDER BY id")->fetchAll();
        
        echo "<table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Role</th>
                <th>Status</th>
            </tr>";
        foreach ($users as $user) {
            echo "<tr>
                <td>{$user['id']}</td>
                <td><strong>{$user['username']}</strong></td>
                <td>{$user['fullname']}</td>
                <td>{$user['role']}</td>
                <td>" . ($user['is_active'] ? '✅ Aktif' : '❌ Nonaktif') . "</td>
            </tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "<p class='warning'>⚠️ Koneksi database gagal: " . $e->getMessage() . "</p>";
    }
    
    echo "</div></body></html>";
    exit;
}

// Jika dijalankan via CLI (command line)
echo "========================================\n";
echo "  PASSWORD HASH GENERATOR - CLI MODE\n";
echo "========================================\n\n";

if ($argc < 2) {
    echo "Penggunaan:\n";
    echo "  php hash_password.php <password>\n";
    echo "  php hash_password.php --update <username> <password>\n\n";
    echo "Contoh:\n";
    echo "  php hash_password.php rahasia\n";
    echo "  php hash_password.php --update admin rahasia123\n";
    exit;
}

if ($argv[1] === '--update') {
    if ($argc < 4) {
        echo "Error: Format salah!\n";
        echo "Gunakan: php hash_password.php --update <username> <password>\n";
        exit;
    }
    
    $username = $argv[2];
    $password = $argv[3];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    require_once 'api/config.php';
    
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->execute([$hash, $username]);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Password untuk user '{$username}' berhasil diupdate!\n";
        echo "Hash: {$hash}\n";
    } else {
        echo "❌ User '{$username}' tidak ditemukan!\n";
    }
} else {
    $password = $argv[1];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "Password asli: {$password}\n";
    echo "Hash result  : {$hash}\n\n";
    echo "SQL untuk update:\n";
    echo "UPDATE users SET password = '{$hash}' WHERE username = 'nama_user';\n";
}
?>