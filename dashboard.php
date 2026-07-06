<?php
require_once 'api/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PISMA Security</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { 
            font-family: 'Outfit', sans-serif; 
            background-color: #f1f5f9;
        }
        .nav-btn {
            transition: all 0.2s ease;
        }
        .nav-btn.active {
            box-shadow: inset 0 -3px 0 0 #3b82f6;
            color: #2563eb;
            font-weight: 600;
        }
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }
        .view-section {
            animation: fadeIn 0.4s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="text-slate-800">

    <!-- Top Navigation -->
    <nav class="glass-header sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center">
                    <div class="bg-gradient-to-tr from-blue-600 to-indigo-500 p-2.5 rounded-xl shadow-lg shadow-blue-500/30">
                        <i class="fas fa-shield-halved text-white text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <span class="font-bold text-xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-blue-700 to-indigo-700">PISMA Security</span>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="hidden md:flex space-x-1 items-end">
                    <?php if($_SESSION['role'] == 'admin'): ?>
                    <button onclick="switchView('admin', this)" class="nav-btn px-4 py-4 text-sm font-medium text-slate-500 hover:text-slate-700">
                        <i class="fas fa-chart-line mr-1"></i> Admin Panel
                    </button>
                    <?php endif; ?>
                    <button onclick="switchView('security', this)" class="nav-btn active px-4 py-4 text-sm font-medium text-slate-500 hover:text-slate-700">
                        <i class="fas fa-user-shield mr-1"></i> Security Mode
                    </button>
                    <button onclick="switchView('guest', this)" class="nav-btn px-4 py-4 text-sm font-medium text-slate-500 hover:text-slate-700">
                        <i class="fas fa-user-plus mr-1"></i> Registrasi Tamu
                    </button>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($_SESSION['fullname']) ?></div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider font-semibold"><?= $_SESSION['role'] ?></div>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold border-2 border-white shadow-sm">
                        <?= strtoupper(substr($_SESSION['fullname'], 0, 1)) ?>
                    </div>
                    <a href="logout.php" class="p-2 text-slate-400 hover:text-red-500 transition-colors" title="Logout">
                        <i class="fas fa-sign-out-alt text-lg"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- Mobile Navigation Menu -->
        <div class="md:hidden flex border-t border-slate-200 overflow-x-auto">
            <?php if($_SESSION['role'] == 'admin'): ?>
            <button onclick="switchView('admin', this)" class="nav-btn flex-1 py-3 text-xs font-medium text-slate-500 whitespace-nowrap px-4"><i class="fas fa-chart-line mr-1"></i> Admin</button>
            <?php endif; ?>
            <button onclick="switchView('security', this)" class="nav-btn active flex-1 py-3 text-xs font-medium text-slate-500 whitespace-nowrap px-4"><i class="fas fa-shield mr-1"></i> Security</button>
            <button onclick="switchView('guest', this)" class="nav-btn flex-1 py-3 text-xs font-medium text-slate-500 whitespace-nowrap px-4"><i class="fas fa-plus mr-1"></i> Tamu</button>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- ================= GUEST VIEW ================= -->
        <div id="guestView" class="hidden view-section">
            <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-xl shadow-slate-200/50 overflow-hidden border border-slate-100">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white text-center">
                    <i class="fas fa-user-plus text-4xl mb-3 opacity-90"></i>
                    <h2 class="text-2xl font-bold">Registrasi Tamu Baru</h2>
                    <p class="text-blue-100 text-sm mt-1 font-light">Lengkapi form di bawah untuk generate Security QR Code.</p>
                </div>
                <div class="p-8">
                    <form id="guestRegisterForm" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Nama Lengkap *</label>
                                <input type="text" id="fullname" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">No. Identitas (KTP/SIM) *</label>
                                <input type="text" id="identity_number" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">No. HP / Whatsapp</label>
                                <input type="text" id="phone" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Instansi / Perusahaan</label>
                                <input type="text" id="institution" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Tujuan Divisi/Orang</label>
                                <input type="text" id="destination" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">No. Kendaraan</label>
                                <input type="text" id="vehicle_number" placeholder="Contoh: B 1234 CD" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Keperluan Kunjungan *</label>
                            <textarea id="purpose" rows="3" required class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"></textarea>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-0.5 active:translate-y-0">
                                <i class="fas fa-qrcode mr-2"></i> Daftar & Generate QR Check-in
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================= SECURITY VIEW ================= -->
        <div id="securityView" class="view-section">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Check-in Panel -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-2xl shadow-lg shadow-slate-200/50 p-6 border border-slate-100 card-hover">
                        <div class="flex items-center mb-4">
                            <div class="bg-indigo-100 text-indigo-600 p-2.5 rounded-lg mr-3"><i class="fas fa-camera text-xl"></i></div>
                            <h3 class="font-bold text-lg">Check-in Tamu</h3>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i class="fas fa-qrcode text-slate-400"></i>
                            </div>
                            <input type="text" id="scanQRInput" placeholder="Scan / Ketik kode DIG-..." class="w-full border border-slate-200 rounded-xl pl-10 pr-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-mono uppercase bg-slate-50">
                        </div>
                        <button onclick="checkinGuest()" class="w-full mt-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-xl transition shadow-md shadow-indigo-500/20">
                            Proses Check-in
                        </button>
                    </div>

                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl shadow-lg shadow-slate-800/40 p-6 border border-slate-700 text-white">
                        <div class="flex items-center mb-4">
                            <div class="bg-slate-700 text-blue-400 p-2.5 rounded-lg mr-3"><i class="fas fa-history text-lg"></i></div>
                            <h3 class="font-bold text-lg">Log Aktivitas</h3>
                        </div>
                        <div id="securityLog" class="space-y-3 text-sm max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                            <!-- Logs injected here -->
                            <div class="text-center text-slate-500 py-4 italic">Memuat log...</div>
                        </div>
                    </div>
                </div>

                <!-- Live Guest List -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg shadow-slate-200/50 p-6 border border-slate-100 flex flex-col h-full">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-slate-100">
                        <div class="flex items-center">
                            <div class="bg-blue-100 text-blue-600 p-2.5 rounded-lg mr-3"><i class="fas fa-users text-xl"></i></div>
                            <h3 class="font-bold text-lg">Daftar Tamu Hari Ini</h3>
                        </div>
                        <button onclick="loadSecurityData()" class="text-slate-400 hover:text-blue-500 transition p-2 bg-slate-50 rounded-full"><i class="fas fa-sync-alt"></i></button>
                    </div>
                    
                    <div id="guestListContainer" class="space-y-3 overflow-y-auto pr-2 flex-grow min-h-[300px]">
                        <!-- Guests injected here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= ADMIN VIEW ================= -->
        <div id="adminView" class="hidden view-section space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl shadow-lg shadow-slate-200/40 p-6 border border-slate-100 flex items-center card-hover overflow-hidden relative">
                    <div class="absolute -right-6 -bottom-6 text-blue-50 opacity-50"><i class="fas fa-users" style="font-size: 8rem;"></i></div>
                    <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mr-4 relative z-10"><i class="fas fa-users"></i></div>
                    <div class="relative z-10">
                        <p class="text-sm text-slate-500 font-medium uppercase tracking-wider">Total Tamu Masuk</p>
                        <h3 class="text-3xl font-bold text-slate-800" id="statTotal">0</h3>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl shadow-lg shadow-slate-200/40 p-6 border border-slate-100 flex items-center card-hover overflow-hidden relative">
                    <div class="absolute -right-6 -bottom-6 text-emerald-50 opacity-50"><i class="fas fa-check-circle" style="font-size: 8rem;"></i></div>
                    <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl mr-4 relative z-10"><i class="fas fa-check-double"></i></div>
                    <div class="relative z-10">
                        <p class="text-sm text-slate-500 font-medium uppercase tracking-wider">Telah Check-in</p>
                        <h3 class="text-3xl font-bold text-slate-800" id="statCheckedIn">0</h3>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg shadow-slate-200/40 p-6 border border-slate-100 flex items-center card-hover overflow-hidden relative">
                    <div class="absolute -right-6 -bottom-6 text-red-50 opacity-50"><i class="fas fa-ban" style="font-size: 8rem;"></i></div>
                    <div class="w-14 h-14 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-2xl mr-4 relative z-10"><i class="fas fa-shield-virus"></i></div>
                    <div class="relative z-10">
                        <p class="text-sm text-slate-500 font-medium uppercase tracking-wider">Terdeteksi Blacklist</p>
                        <h3 class="text-3xl font-bold text-slate-800" id="statBlacklist">0</h3>
                    </div>
                </div>
            </div>

            <!-- Chart & Logs -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Data Chart -->
                <div class="lg:col-span-1 bg-white rounded-2xl shadow-lg shadow-slate-200/40 p-6 border border-slate-100">
                    <h3 class="font-bold text-lg mb-6 text-slate-700">Rasio Kunjungan</h3>
                    <div class="relative h-64 flex justify-center items-center">
                        <canvas id="visitChart"></canvas>
                    </div>
                </div>

                <!-- Audit Log -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg shadow-slate-200/40 border border-slate-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div class="flex items-center">
                            <div class="bg-slate-800 text-white p-2 rounded-lg mr-3"><i class="fas fa-list-ul"></i></div>
                            <h3 class="font-bold text-lg text-slate-800">Audit Trail Keamanan</h3>
                        </div>
                        <button onclick="exportCSV()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm shadow-emerald-500/30 flex items-center">
                            <i class="fas fa-download mr-2"></i> Export CSV
                        </button>
                    </div>
                    <div class="overflow-x-auto flex-grow max-h-80">
                        <table class="min-w-full text-sm text-left">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-xs sticky top-0">
                                <tr>
                                    <th class="px-6 py-4 font-semibold">Waktu</th>
                                    <th class="px-6 py-4 font-semibold">Tipe Aksi</th>
                                    <th class="px-6 py-4 font-semibold">Deskripsi Aktivitas</th>
                                    <th class="px-6 py-4 font-semibold">IP Address</th>
                                </tr>
                            </thead>
                            <tbody id="auditTableBody" class="divide-y divide-slate-100 text-slate-700">
                                <!-- Data injected via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script>
        let chartInstance = null;
        const role = '<?= $_SESSION['role'] ?>';
        
        // Setup initial view
        document.addEventListener('DOMContentLoaded', () => {
            const defaultNav = document.querySelectorAll('.nav-btn')[role === 'admin' ? 1 : 0]; // 1 is security for admin, 0 for pure security role
            switchView('security', document.querySelector('.nav-btn.active'));
        });

        function switchView(view, btnElement = null) {
            // Update UI buttons
            if(btnElement) {
                document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
                
                // Add active to the clicked button everywhere (desktop + mobile)
                document.querySelectorAll('.nav-btn').forEach(btn => {
                    if (btn.innerHTML === btnElement.innerHTML) {
                        btn.classList.add('active');
                    }
                });
            }

            // Hide all
            document.getElementById('guestView').classList.add('hidden');
            document.getElementById('securityView').classList.add('hidden');
            document.getElementById('adminView').classList.add('hidden');
            
            // Show requested
            if(view === 'guest') {
                document.getElementById('guestView').classList.remove('hidden');
            }
            else if(view === 'security') { 
                document.getElementById('securityView').classList.remove('hidden'); 
                loadSecurityData(); 
            }
            else if(view === 'admin' && role === 'admin') { 
                document.getElementById('adminView').classList.remove('hidden'); 
                loadAdminData(); 
            }
        }
        
        async function loadSecurityData() {
            try {
                const resp = await fetch('api/security_api.php?action=get_today_guests');
                const data = await resp.json();
                
                if(data.guests && data.guests.length > 0) {
                    document.getElementById('guestListContainer').innerHTML = data.guests.map(g => `
                        <div class="group bg-white p-4 rounded-xl border ${g.status === 'checked_in' ? 'border-emerald-100 bg-emerald-50/30' : 'border-slate-200 hover:border-blue-300'} transition flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full ${g.status === 'checked_in' ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'} flex items-center justify-center font-bold text-lg mr-4">
                                    ${g.fullname.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-md">${g.fullname}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5"><i class="far fa-building mr-1"></i> ${g.institution || 'Personal'} &bull; Menuju: <span class="font-medium">${g.destination}</span></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono text-xs font-semibold py-1 px-2 mb-1 ${g.status === 'checked_in' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'} rounded border ${g.status === 'checked_in' ? 'border-emerald-200' : 'border-slate-200'}">
                                    ${g.qr_code}
                                </div>
                                <span class="text-[11px] uppercase font-bold tracking-wider ${g.status === 'checked_in' ? 'text-emerald-500' : 'text-amber-500'}">
                                    ${g.status === 'checked_in' ? '<i class="fas fa-check-circle"></i> Checked In' : '<i class="fas fa-clock"></i> Waiting'}
                                </span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    document.getElementById('guestListContainer').innerHTML = `
                        <div class="text-center py-10 flex flex-col items-center justify-center border-2 border-dashed border-slate-200 rounded-xl">
                            <i class="fas fa-box-open text-slate-300 text-4xl mb-3"></i>
                            <p class="text-slate-500 font-medium">Belum ada tamu hari ini</p>
                        </div>
                    `;
                }

                const logResp = await fetch('api/security_api.php?action=get_logs');
                const logData = await logResp.json();
                
                if(logData.logs && logData.logs.length > 0) {
                    document.getElementById('securityLog').innerHTML = logData.logs.map(l => {
                        let colorClass = 'text-blue-400';
                        if(l.action.includes('BLACKLIST')) colorClass = 'text-red-400';
                        if(l.action.includes('REGISTER')) colorClass = 'text-emerald-400';
                        return `
                            <div class="border-b border-slate-700/50 pb-3 last:border-0 relative pl-4 before:content-[''] before:absolute before:left-0 before:top-2 before:w-1.5 before:h-1.5 before:bg-slate-600 before:rounded-full">
                                <div class="flex justify-between items-start">
                                    <strong class="${colorClass} text-xs font-bold tracking-wider">${l.action}</strong>
                                    <span class="text-slate-500 text-[10px]"><i class="far fa-clock bg-slate-800"></i> ${l.created_at.split(' ')[1]}</span>
                                </div>
                                <p class="text-slate-300 mt-1 leading-snug">${l.description}</p>
                            </div>
                        `;
                    }).join('');
                }
            } catch(e) {
                console.error("Error loading security data", e);
            }
        }
        
        async function checkinGuest() {
            const input = document.getElementById('scanQRInput');
            const qr = input.value.trim();
            if(!qr) return;

            const originalBtnText = event.target.innerHTML;
            event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const resp = await fetch('api/guest_api.php?action=checkin', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `qr_code=${encodeURIComponent(qr)}`
                });
                const result = await resp.json();
                
                if(result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Check-in Berhasil',
                        text: result.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadSecurityData(); // refresh list
                    input.value = '';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ditolak',
                        text: result.message
                    });
                }
            } catch(e) {
                Swal.fire('Error', 'Gagal memproses check-in', 'error');
            }
            event.target.innerHTML = originalBtnText;
        }
        
        document.getElementById('guestRegisterForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const originalVal = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Memproses...';
            btn.disabled = true;

            const formData = new URLSearchParams({
                fullname: document.getElementById('fullname').value,
                identity_number: document.getElementById('identity_number').value,
                phone: document.getElementById('phone').value,
                institution: document.getElementById('institution').value,
                vehicle_number: document.getElementById('vehicle_number').value,
                destination: document.getElementById('destination').value,
                purpose: document.getElementById('purpose').value
            });

            try {
                const resp = await fetch('api/guest_api.php?action=register', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: formData
                });
                const result = await resp.json();
                
                if(result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registrasi Berhasil!',
                        html: `
                            <p class="mb-4">Tamu telah terdaftar dalam sistem.</p>
                            <div class="bg-gray-100 p-4 rounded-xl text-center">
                                <span class="text-xs uppercase text-gray-500 font-bold tracking-wider">QR Code / Token</span><br>
                                <span class="font-mono text-2xl font-bold text-indigo-700 tracking-widest bg-white mt-2 inline-block px-4 py-2 border shadow-sm rounded-lg">${result.qr_code}</span>
                            </div>
                            <p class="text-xs mt-3 text-gray-500">Berikan kode ini kepada tamu untuk proses Check-in di pos.</p>
                        `,
                        confirmButtonText: 'Tutup & Clear Form',
                        confirmButtonColor: '#3b82f6'
                    }).then(() => {
                        e.target.reset();
                    });
                } else {
                    Swal.fire({icon: 'error', title: 'Registrasi Gagal', text: result.message});
                }
            } catch(e) {
                Swal.fire('Error', 'Gagal memproses pendaftaran', 'error');
            }
            
            btn.innerHTML = originalVal;
            btn.disabled = false;
        });
        
        async function loadAdminData() {
            try {
                const resp = await fetch('api/report_api.php?action=dashboard_stats');
                const data = await resp.json();
                
                document.getElementById('statTotal').innerText = data.total_guests || 0;
                document.getElementById('statCheckedIn').innerText = data.checked_in || 0;
                document.getElementById('statBlacklist').innerText = data.blacklist_count || 0;
                
                // Render Chart
                if(chartInstance) chartInstance.destroy();
                const ctx = document.getElementById('visitChart').getContext('2d');
                const waiting = Math.max(0, (data.total_guests || 0) - (data.checked_in || 0));
                
                chartInstance = new Chart(ctx, { 
                    type: 'doughnut', 
                    data: { 
                        labels: ['Checked-In', 'Waiting'], 
                        datasets: [{ 
                            data: [data.checked_in || 0, waiting], 
                            backgroundColor: ['#10b981', '#cbd5e1'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }] 
                    },
                    options: {
                        cutout: '75%',
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });

                // Render Logs
                const auditResp = await fetch('api/report_api.php?action=audit_logs');
                const audit = await auditResp.json();
                
                if(audit.logs && audit.logs.length > 0) {
                    document.getElementById('auditTableBody').innerHTML = audit.logs.map(l => {
                        let actionBadge = '';
                        if(l.action === 'LOGIN') actionBadge = '<span class="bg-blue-100 text-blue-700 py-1 px-2 rounded font-bold text-[10px]">LOGIN</span>';
                        else if(l.action === 'CHECK_IN') actionBadge = '<span class="bg-emerald-100 text-emerald-700 py-1 px-2 rounded font-bold text-[10px]">CHECK_IN</span>';
                        else if(l.action.includes('BLACKLIST')) actionBadge = '<span class="bg-red-100 text-red-700 py-1 px-2 rounded font-bold text-[10px]">ALERT</span>';
                        else actionBadge = `<span class="bg-slate-100 text-slate-700 py-1 px-2 rounded font-bold text-[10px]">${l.action}</span>`;

                        return `
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-medium">${l.created_at}</td>
                                <td class="px-6 py-4">${actionBadge}</td>
                                <td class="px-6 py-4 font-medium text-slate-800">${l.description}</td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-400">${l.ip_address}</td>
                            </tr>
                        `;
                    }).join('');
                }
            } catch(e) {
                console.error("Error loading admin data", e);
            }
        }
        
        function exportCSV() {
            window.location.href = 'api/report_api.php?action=export_csv';
        }
    </script>
</body>
</html>