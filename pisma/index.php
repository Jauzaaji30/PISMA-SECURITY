<?php
require_once 'api/config.php';

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dash.php');
    } else {
        header('Location: security_dash.php');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        
        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        securityLog($pdo, $user['id'], 'LOGIN', 'User login successfully');
        
        if ($user['role'] === 'admin') {
            header('Location: admin_dash.php');
        } else {
            header('Location: security_dash.php');
        }
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication - PISMA Security System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 for nice alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .bg-animate {
            background: linear-gradient(-45deg, #0f172a, #1e3a8a, #312e81, #020617);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .form-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #60a5fa;
            box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.2);
            outline: none;
        }
        .form-input::placeholder { color: #94a3b8; }
        
        .form-container {
            transition: opacity 0.4s ease, transform 0.4s ease;
        }
        .hidden-form {
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
            position: absolute;
            visibility: hidden;
        }
        .active-form {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
            position: relative;
            visibility: visible;
        }
        
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            z-index: 0;
            animation: float 10s infinite ease-in-out alternate;
        }
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -50px) scale(1.1); }
        }
    </style>
</head>
<body class="bg-animate min-h-screen flex items-center justify-center relative overflow-hidden text-slate-100">
    
    <div class="floating-shape bg-blue-600/30 w-96 h-96 top-[-10%] left-[-10%]"></div>
    <div class="floating-shape bg-indigo-600/30 w-96 h-96 bottom-[-10%] right-[-10%]" style="animation-duration: 15s; animation-direction: alternate-reverse;"></div>

    <div class="glass-panel rounded-3xl w-full max-w-md p-8 relative z-10 mx-4">
        
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-blue-500/30 transform transition hover:scale-105 duration-300">
                <i class="fas fa-crown text-white text-4xl"></i>
            </div>
            <h2 class="text-3xl font-bold text-white tracking-tight"> PISMA SECURITY</h2>
            <p class="text-blue-200/80 mt-2 text-sm font-light">Sistem Cerdas Pelaporan Tamu</p>
        </div>
        
        <?php if($error): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-xl mb-6 backdrop-blur-sm flex items-center">
                <i class="fas fa-circle-exclamation mr-3 text-red-400"></i>
                <span class="text-sm font-medium"><?= $error ?></span>
            </div>
        <?php endif; ?>

        <div class="relative w-full overflow-hidden" style="min-height: 320px;">
            
            <!-- Login Form -->
            <div id="loginForm" class="form-container active-form w-full left-0 top-0">
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="login">
                    <div>
                        <label class="block text-sm font-medium text-blue-200 mb-1.5 ml-1">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center justify-center pointer-events-none">
                                <i class="fas fa-user text-blue-300/60"></i>
                            </div>
                            <input type="text" name="username" required placeholder="Masukkan username" class="form-input w-full rounded-xl pl-11 pr-4 py-3 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-blue-200 mb-1.5 ml-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center justify-center pointer-events-none">
                                <i class="fas fa-lock text-blue-300/60"></i>
                            </div>
                            <input type="password" name="password" required placeholder="••••••••" class="form-input w-full rounded-xl pl-11 pr-4 py-3 text-sm">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold py-3.5 rounded-xl transition duration-300 shadow-lg shadow-blue-500/25 flex justify-center items-center group mt-2 text-sm uppercase tracking-wide">
                        <span>Masuk ke Sistem</span>
                        <i class="fas fa-arrow-right ml-2 opacity-70 group-hover:transform group-hover:translate-x-1 duration-300"></i>
                    </button>
                </form>
                
                <div class="mt-6 text-center text-sm">
                    <p class="text-blue-200/60 font-light">Belum punya akun? <button onclick="toggleForm('register')" class="text-blue-400 hover:text-blue-300 font-medium ml-1 transition">Sign Up (Petugas)</button></p>
                </div>
            </div>
            
            <!-- Register Form -->
            <div id="registerForm" class="form-container hidden-form w-full left-0 top-0">
                <form id="formRegisterAccount" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-blue-200 mb-1 ml-1">Nama Lengkap <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i class="fas fa-id-card text-blue-300/60 text-xs"></i>
                            </div>
                            <input type="text" name="reg_fullname" id="reg_fullname" required placeholder="Nama Lengkap" class="form-input w-full rounded-xl pl-9 pr-3 py-2.5 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-blue-200 mb-1 ml-1">Username Baru <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i class="fas fa-user-plus text-blue-300/60 text-xs"></i>
                            </div>
                            <input type="text" name="reg_username" id="reg_username" required placeholder="Username unik" class="form-input w-full rounded-xl pl-9 pr-3 py-2.5 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-blue-200 mb-1 ml-1">Buat Password <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i class="fas fa-key text-blue-300/60 text-xs"></i>
                            </div>
                            <input type="password" name="reg_password" id="reg_password" required placeholder="Minimal 4 karakter" class="form-input w-full rounded-xl pl-9 pr-3 py-2.5 text-sm">
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1 ml-1">* Password minimal 4 karakter</p>
                    </div>
                    <button type="submit" id="btnSubmitReg" class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-semibold py-3 rounded-xl transition duration-300 shadow-lg shadow-teal-500/25 flex justify-center items-center mt-4 text-sm tracking-wide">
                        <i class="fas fa-user-check mr-2 opacity-80"></i> Buat Akun
                    </button>
                </form>
                
                <div class="mt-5 text-center text-sm">
                    <p class="text-blue-200/60 font-light">Sudah terdaftar? <button onclick="toggleForm('login')" class="text-blue-400 hover:text-blue-300 font-medium ml-1 transition">Kembali ke Login</button></p>
                </div>
            </div>
            
        </div>
        
    </div>
    
    <div class="absolute bottom-6 w-full text-center text-xs font-light tracking-wide text-blue-300/50 z-10">
        &copy; <?= date("Y") ?> PISMA Security Enterprise. All rights reserved.<br>
        Demo: admin/rahasia &bull; <a href="guest.php" class="underline hover:text-blue-300 transition">Masuk sebagai Tamu</a>
    </div>

    <script>
        function toggleForm(formType) {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            
            if (formType === 'register') {
                loginForm.classList.remove('active-form');
                loginForm.classList.add('hidden-form');
                setTimeout(() => {
                    registerForm.classList.remove('hidden-form');
                    registerForm.classList.add('active-form');
                }, 100);
            } else {
                registerForm.classList.remove('active-form');
                registerForm.classList.add('hidden-form');
                setTimeout(() => {
                    loginForm.classList.remove('hidden-form');
                    loginForm.classList.add('active-form');
                }, 100);
            }
        }

        document.getElementById('formRegisterAccount').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitReg');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses...';
            btn.disabled = true;

            const fullname = document.getElementById('reg_fullname').value.trim();
            const username = document.getElementById('reg_username').value.trim();
            const password = document.getElementById('reg_password').value;

            if (!fullname || !username || !password) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Semua field harus diisi!',
                    background: '#1e293b',
                    color: '#f8fafc',
                    confirmButtonColor: '#3b82f6'
                });
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }

            if (password.length < 4) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Password minimal 4 karakter!',
                    background: '#1e293b',
                    color: '#f8fafc',
                    confirmButtonColor: '#3b82f6'
                });
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }

            const formData = new URLSearchParams({
                action: 'register',
                fullname: fullname,
                username: username,
                password: password
            });

            try {
                const resp = await fetch('api/auth_api.php?action=register', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: formData
                });
                const result = await resp.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message,
                        background: '#1e293b',
                        color: '#f8fafc',
                        confirmButtonColor: '#3b82f6'
                    }).then(() => {
                        e.target.reset();
                        toggleForm('login');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: result.message,
                        background: '#1e293b',
                        color: '#f8fafc',
                        confirmButtonColor: '#3b82f6'
                    });
                }
            } catch (err) {
                console.error('Error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Koneksi',
                    text: 'Tidak dapat terhubung ke server. Pastikan server berjalan.',
                    background: '#1e293b',
                    color: '#f8fafc'
                });
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>