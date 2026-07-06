<?php
require_once 'api/config.php';
requireLogin();
// Only admin OR security role allowed
if ($_SESSION['role'] !== 'security' && $_SESSION['role'] !== 'admin') {
    header('Location: index.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Dashboard - PISMA Security</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { font-family: 'Outfit', sans-serif; }
        body { background: #0f172a; color: #e2e8f0; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        .glass { background: rgba(255,255,255,0.04); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); }
        .glass-light { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); }
        .stat-card { background: linear-gradient(135deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02)); border: 1px solid rgba(255,255,255,0.08); transition: transform 0.2s, border-color 0.2s; }
        .stat-card:hover { transform: translateY(-3px); border-color: rgba(96,165,250,0.3); }
        .form-input { background: rgba(255,255,255,0.07); border: 1.5px solid rgba(255,255,255,0.1); color: #f1f5f9; border-radius: 0.75rem; padding: 0.75rem 1rem; width: 100%; transition: all 0.25s; }
        .form-input:focus { outline: none; border-color: #60a5fa; background: rgba(96,165,250,0.1); box-shadow: 0 0 0 3px rgba(96,165,250,0.15); }
        .form-input::placeholder { color: #475569; }
        .badge-waiting { background:#854d0e22; color:#fbbf24; border:1px solid #92400e55; }
        .badge-checkin { background:#064e3b22; color:#34d399; border:1px solid #06503b55; }
        .badge-done    { background:#1e1b4b22; color:#818cf8; border:1px solid #3730a322; }
        .nav-link { transition: all 0.2s; padding: 0.6rem 1.2rem; border-radius: 0.75rem; font-size: 0.85rem; font-weight: 500; }
        .nav-link:hover { background: rgba(255,255,255,0.08); }
        .nav-link.active { background: rgba(96,165,250,0.15); color: #60a5fa; border: 1px solid rgba(96,165,250,0.3); }
        @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
        .fade-up { animation: fadeUp 0.4s ease forwards; }
        @keyframes pulse-border { 0%,100% { border-color: rgba(239,68,68,0.4); } 50% { border-color: rgba(239,68,68,0.9); } }
        .alert-pulse { animation: pulse-border 1.5s ease infinite; }
    </style>
</head>
<body class="min-h-screen">

    <!-- Sidebar + Main Layout -->
    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside class="w-64 flex-shrink-0 glass border-r border-white/10 flex flex-col p-5 sticky top-0 h-screen z-20 hidden lg:flex">
            <!-- Logo -->
            <div class="flex items-center space-x-3 mb-8">
                <div class="bg-gradient-to-tr from-blue-600 to-indigo-500 p-2.5 rounded-xl shadow-lg shadow-blue-700/40">
                    <i class="fas fa-crown text-white text-lg"></i>
                </div>
                <div>
                    <div class="font-bold text-base">PISMA Security</div>
                    <div class="text-xs text-blue-300/70">Security Mode</div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="space-y-1 flex-1">
                <button onclick="showPanel('scanPanel', this)" class="nav-link active w-full text-left flex items-center space-x-3">
                    <i class="fas fa-qrcode w-4 text-center"></i><span>Scan Check-in</span>
                </button>
                <button onclick="showPanel('guestPanel', this)" class="nav-link w-full text-left flex items-center space-x-3">
                    <i class="fas fa-users w-4 text-center"></i><span>Daftar Tamu</span>
                </button>
                <button onclick="showPanel('blacklistPanel', this)" class="nav-link w-full text-left flex items-center space-x-3">
                    <i class="fas fa-ban w-4 text-center text-red-400"></i><span>Blacklist</span>
                </button>
                <button onclick="showPanel('logPanel', this)" class="nav-link w-full text-left flex items-center space-x-3">
                    <i class="fas fa-history w-4 text-center"></i><span>Log Aktivitas</span>
                </button>
                <hr class="border-white/10 my-3">
                <?php if($_SESSION['role'] === 'admin'): ?>
                <a href="admin_dash.php" class="nav-link w-full text-left flex items-center space-x-3 text-purple-400">
                    <i class="fas fa-chart-line w-4 text-center"></i><span>Admin Panel</span>
                </a>
                <?php endif; ?>
                <a href="guest.php" target="_blank" class="nav-link w-full text-left flex items-center space-x-3 text-emerald-400">
                    <i class="fas fa-user-plus w-4 text-center"></i><span>Portal Tamu</span>
                </a>
            </nav>

            <!-- User Info -->
            <div class="glass-light rounded-2xl p-4 mt-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600/40 border border-blue-500/30 flex items-center justify-center font-bold text-blue-300">
                        <?= strtoupper(substr($_SESSION['fullname'], 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm truncate"><?= htmlspecialchars($_SESSION['fullname']) ?></div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider"><?= $_SESSION['role'] ?></div>
                    </div>
                </div>
                <a href="logout.php" class="mt-3 w-full flex items-center justify-center space-x-2 text-xs text-red-400 hover:text-red-300 hover:bg-red-500/10 py-2 rounded-xl transition">
                    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 lg:p-8 overflow-y-auto">

            <!-- Mobile Header -->
            <div class="lg:hidden flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="bg-gradient-to-tr from-blue-600 to-indigo-500 p-2 rounded-lg"><i class="fas fa-shield-halved text-white"></i></div>
                    <span class="font-bold">PISMA Security</span>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-xs text-slate-400"><?= htmlspecialchars($_SESSION['fullname']) ?></span>
                    <a href="logout.php" class="text-red-400"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>

            <!-- Mobile Nav Pills -->
            <div class="lg:hidden flex space-x-2 mb-6 overflow-x-auto pb-2">
                <button onclick="showPanel('scanPanel', this)" class="nav-link active whitespace-nowrap text-xs"><i class="fas fa-qrcode mr-1"></i>Scan</button>
                <button onclick="showPanel('guestPanel', this)" class="nav-link whitespace-nowrap text-xs"><i class="fas fa-users mr-1"></i>Tamu</button>
                <button onclick="showPanel('blacklistPanel', this)" class="nav-link whitespace-nowrap text-xs"><i class="fas fa-ban mr-1"></i>Blacklist</button>
                <button onclick="showPanel('logPanel', this)" class="nav-link whitespace-nowrap text-xs"><i class="fas fa-history mr-1"></i>Log</button>
            </div>

            <!-- Live Stats Bar -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="stat-card rounded-2xl p-4">
                    <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Tamu Hari Ini</div>
                    <div class="text-2xl font-bold text-white" id="ls_total">–</div>
                </div>
                <div class="stat-card rounded-2xl p-4">
                    <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Sudah Check-in</div>
                    <div class="text-2xl font-bold text-emerald-400" id="ls_checkin">–</div>
                </div>
                <div class="stat-card rounded-2xl p-4">
                    <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Sudah Check-out</div>
                    <div class="text-2xl font-bold text-indigo-400" id="ls_checkout">–</div>
                </div>
                <div class="stat-card rounded-2xl p-4">
                    <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Menunggu</div>
                    <div class="text-2xl font-bold text-amber-400" id="ls_waiting">–</div>
                </div>
            </div>

            <!-- ===== SCAN PANEL ===== -->
            <div id="scanPanel" class="fade-up space-y-6">
                <h2 class="text-xl font-bold">Scan QR Check-in & Check-out</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Check-in -->
                    <div class="glass rounded-2xl p-6">
                        <div class="flex items-center space-x-3 mb-5">
                            <div class="w-10 h-10 bg-emerald-500/20 rounded-xl flex items-center justify-center"><i class="fas fa-sign-in-alt text-emerald-400"></i></div>
                            <div>
                                <div class="font-bold">Check-in Tamu</div>
                                <div class="text-xs text-slate-400">Scan QR untuk tamu masuk</div>
                            </div>
                        </div>
                        <div class="relative mb-3">
                            <i class="fas fa-qrcode absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="scanCheckinInput" placeholder="Ketik atau scan kode DIG-..." class="form-input pl-10 font-mono uppercase tracking-wider text-sm" autocomplete="off">
                        </div>
                        <button onclick="processCheckin()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-emerald-700/30">
                            <i class="fas fa-check mr-2"></i>Proses Check-in
                        </button>
                    </div>
                    <!-- Check-out -->
                    <div class="glass rounded-2xl p-6">
                        <div class="flex items-center space-x-3 mb-5">
                            <div class="w-10 h-10 bg-indigo-500/20 rounded-xl flex items-center justify-center"><i class="fas fa-sign-out-alt text-indigo-400"></i></div>
                            <div>
                                <div class="font-bold">Check-out Tamu</div>
                                <div class="text-xs text-slate-400">Proses tamu yang sudah selesai berkunjung</div>
                            </div>
                        </div>
                        <div class="relative mb-3">
                            <i class="fas fa-qrcode absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="scanCheckoutInput" placeholder="Ketik atau scan kode DIG-..." class="form-input pl-10 font-mono uppercase tracking-wider text-sm" autocomplete="off">
                        </div>
                        <button onclick="processCheckout()" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-indigo-700/30">
                            <i class="fas fa-door-open mr-2"></i>Proses Check-out
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===== GUEST LIST PANEL ===== -->
            <div id="guestPanel" class="hidden fade-up">
                <div class="flex flex-wrap gap-3 items-center justify-between mb-6">
                    <h2 class="text-xl font-bold">Daftar Tamu Hari Ini</h2>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" id="guestSearch" oninput="loadGuests()" placeholder="Cari nama / QR..." class="form-input pl-9 text-sm py-2.5 w-56">
                        </div>
                        <select id="guestStatusFilter" onchange="loadGuests()" class="form-input text-sm py-2.5 w-36">
                            <option value="all">Semua Status</option>
                            <option value="waiting">Waiting</option>
                            <option value="checked_in">Checked-in</option>
                            <option value="completed">Completed</option>
                        </select>
                        <button onclick="loadGuests()" class="text-slate-400 hover:text-blue-400 bg-white/5 border border-white/10 p-2.5 rounded-xl transition">
                            <i class="fas fa-sync-alt text-sm"></i>
                        </button>
                    </div>
                </div>
                <div id="guestListContainer" class="space-y-3">
                    <div class="text-center py-12 text-slate-500">Memuat data tamu...</div>
                </div>
            </div>

            <!-- ===== BLACKLIST PANEL ===== -->
            <div id="blacklistPanel" class="hidden fade-up">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-red-400"><i class="fas fa-ban mr-2"></i>Manajemen Blacklist</h2>
                    <button onclick="showAddBlacklist()" class="bg-red-600 hover:bg-red-500 text-white text-sm px-4 py-2.5 rounded-xl font-medium transition flex items-center space-x-2 shadow-lg shadow-red-700/30">
                        <i class="fas fa-plus"></i><span>Tambah Blacklist</span>
                    </button>
                </div>
                <div id="blacklistContainer" class="space-y-3">
                    <div class="text-center py-12 text-slate-500">Memuat data blacklist...</div>
                </div>
            </div>

            <!-- ===== LOG PANEL ===== -->
            <div id="logPanel" class="hidden fade-up">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold">Log Aktivitas</h2>
                    <select id="logLimit" onchange="loadLogs()" class="form-input text-sm py-2 w-36">
                        <option value="30">30 entri</option>
                        <option value="50">50 entri</option>
                        <option value="100">100 entri</option>
                    </select>
                </div>
                <div class="glass rounded-2xl overflow-hidden">
                    <div id="logContainer" class="divide-y divide-white/5 max-h-[600px] overflow-y-auto">
                        <div class="p-8 text-center text-slate-500">Memuat log...</div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        const panels = ['scanPanel', 'guestPanel', 'blacklistPanel', 'logPanel'];
        
        function showPanel(id, btn) {
            panels.forEach(p => {
                const el = document.getElementById(p);
                el.classList.add('hidden');
                el.classList.remove('fade-up');
            });
            
            const target = document.getElementById(id);
            target.classList.remove('hidden');
            setTimeout(() => target.classList.add('fade-up'), 10);
            
            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(b => b.classList.remove('active'));
            if (btn) {
                document.querySelectorAll('.nav-link').forEach(b => {
                    if(b.dataset.panel === id || b.textContent.trim() === btn.textContent.trim()) {
                        b.classList.add('active');
                    }
                });
                btn.classList.add('active');
            }
            
            // Load data when panel opens
            if (id === 'guestPanel')     loadGuests();
            if (id === 'blacklistPanel') loadBlacklist();
            if (id === 'logPanel')       loadLogs();
        }

        // Live stats (refresh every 15 seconds)
        async function loadLiveStats() {
            try {
                const r = await fetch('api/security_api.php?action=live_stats');
                const d = await r.json();
                document.getElementById('ls_total').textContent   = d.today_total ?? 0;
                document.getElementById('ls_checkin').textContent = d.today_checkin ?? 0;
                document.getElementById('ls_checkout').textContent= d.today_checkout ?? 0;
                document.getElementById('ls_waiting').textContent = d.waiting ?? 0;
            } catch(e) {}
        }
        loadLiveStats();
        setInterval(loadLiveStats, 15000);

        // === CHECK-IN ===
        async function processCheckin() {
            const input = document.getElementById('scanCheckinInput');
            const qr = input.value.trim();
            if (!qr) { Swal.fire({toast:true,position:'top-end',icon:'warning',title:'Masukkan QR Code!',timer:2000,showConfirmButton:false}); return; }
            
            try {
                const r = await fetch('api/guest_api.php?action=checkin', {
                    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
                    body: `qr_code=${encodeURIComponent(qr)}`
                });
                const res = await r.json();
                
                if (res.success) {
                    Swal.fire({ icon:'success', title:'Check-in Berhasil! ✅', text: res.message, timer:2500, showConfirmButton:false, background:'#0f172a', color:'#f1f5f9' });
                } else {
                    Swal.fire({ icon: res.blacklisted ? 'error' : 'warning', title: res.blacklisted ? '⛔ BLACKLIST ALERT' : 'Gagal', text: res.message, background:'#0f172a', color:'#f1f5f9', confirmButtonColor: res.blacklisted ? '#dc2626' : '#3b82f6' });
                }
                input.value = '';
                loadLiveStats();
            } catch(e) { Swal.fire('Error','Gagal terhubung ke server','error'); }
        }
        document.getElementById('scanCheckinInput').addEventListener('keydown', e => { if(e.key==='Enter') processCheckin(); });

        // === CHECK-OUT ===
        async function processCheckout() {
            const input = document.getElementById('scanCheckoutInput');
            const qr = input.value.trim();
            if (!qr) { Swal.fire({toast:true,position:'top-end',icon:'warning',title:'Masukkan QR Code!',timer:2000,showConfirmButton:false}); return; }
            
            try {
                const r = await fetch('api/security_api.php?action=checkout', {
                    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
                    body: `qr_code=${encodeURIComponent(qr)}`
                });
                const res = await r.json();
                if (res.success) {
                    Swal.fire({ icon:'success', title:'Check-out Berhasil! 👋', text: res.message, timer:2500, showConfirmButton:false, background:'#0f172a', color:'#f1f5f9' });
                } else {
                    Swal.fire({ icon:'warning', title:'Gagal', text: res.message, background:'#0f172a', color:'#f1f5f9' });
                }
                input.value = '';
                loadLiveStats();
            } catch(e) { Swal.fire('Error','Gagal terhubung ke server','error'); }
        }
        document.getElementById('scanCheckoutInput').addEventListener('keydown', e => { if(e.key==='Enter') processCheckout(); });

        // === LOAD GUESTS ===
        async function loadGuests() {
            const search = document.getElementById('guestSearch')?.value || '';
            const status = document.getElementById('guestStatusFilter')?.value || 'all';
            const container = document.getElementById('guestListContainer');
            
            try {
                const r = await fetch(`api/security_api.php?action=get_today_guests&search=${encodeURIComponent(search)}&status=${status}`);
                const data = await r.json();
                
                if (!data.guests || data.guests.length === 0) {
                    container.innerHTML = `<div class="glass rounded-2xl p-12 text-center"><i class="fas fa-user-slash text-4xl text-slate-600 mb-3 block"></i><p class="text-slate-400">Belum ada data tamu</p></div>`;
                    return;
                }
                
                container.innerHTML = data.guests.map(g => {
                    const statusBadge = g.status === 'checked_in' ? `<span class="badge-checkin text-xs px-2 py-1 rounded-lg font-bold"><i class="fas fa-check mr-1"></i>Checked-in</span>`
                        : g.status === 'completed' ? `<span class="badge-done text-xs px-2 py-1 rounded-lg font-bold"><i class="fas fa-flag-checkered mr-1"></i>Selesai</span>`
                        : `<span class="badge-waiting text-xs px-2 py-1 rounded-lg font-bold"><i class="fas fa-clock mr-1"></i>Waiting</span>`;
                    
                    return `
                    <div class="glass rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-blue-500/30 transition">
                        <div class="w-11 h-11 rounded-xl bg-blue-600/20 border border-blue-500/20 flex items-center justify-center font-bold text-blue-300 text-lg flex-shrink-0">
                            ${g.fullname.charAt(0).toUpperCase()}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="font-bold">${g.fullname}</span>
                                ${statusBadge}
                            </div>
                            <div class="text-xs text-slate-400 space-x-3">
                                <span><i class="fas fa-building mr-1"></i>${g.institution || 'Personal'}</span>
                                <span><i class="fas fa-map-pin mr-1"></i>${g.destination}</span>
                                ${g.vehicle_number ? `<span><i class="fas fa-car mr-1"></i>${g.vehicle_number}</span>` : ''}
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="font-mono text-xs text-blue-300 bg-blue-900/30 border border-blue-800/50 px-3 py-1.5 rounded-lg mb-1">${g.qr_code}</div>
                            <div class="text-[11px] text-slate-500">${g.check_in_time ? '⏰ '+g.check_in_time.split(' ')[1] : 'Belum masuk'}</div>
                        </div>
                    </div>`;
                }).join('');
            } catch(e) { container.innerHTML = '<div class="p-8 text-center text-red-400">Gagal memuat data</div>'; }
        }

        // === LOAD BLACKLIST ===
        async function loadBlacklist() {
            const container = document.getElementById('blacklistContainer');
            try {
                const r = await fetch('api/security_api.php?action=get_blacklist');
                const data = await r.json();
                
                if (!data.blacklist || data.blacklist.length === 0) {
                    container.innerHTML = `<div class="glass rounded-2xl p-12 text-center"><i class="fas fa-shield-halved text-4xl text-emerald-600/50 mb-3 block"></i><p class="text-slate-400">Tidak ada data blacklist. Sistem aman.</p></div>`;
                    return;
                }
                
                container.innerHTML = data.blacklist.map(b => `
                    <div class="glass rounded-2xl p-4 flex items-center justify-between gap-4 border border-red-900/30 alert-pulse">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-red-500/20 rounded-xl flex items-center justify-center"><i class="fas fa-ban text-red-400"></i></div>
                            <div>
                                <div class="font-bold font-mono text-red-300">${b.identity_number}</div>
                                <div class="text-xs text-slate-400 mt-0.5"><i class="fas fa-info-circle mr-1"></i>${b.reason || 'Tidak ada keterangan'}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Ditambahkan: ${b.created_at}</div>
                            </div>
                        </div>
                        <?php if($_SESSION['role'] === 'admin'): ?>
                        <button onclick="removeBlacklist(${b.id})" class="text-red-400 hover:text-white hover:bg-red-600 border border-red-600/30 p-2 rounded-xl transition flex-shrink-0" title="Hapus dari blacklist">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                        <?php endif; ?>
                    </div>`
                ).join('');
            } catch(e) { container.innerHTML = '<div class="p-8 text-center text-red-400">Gagal memuat data</div>'; }
        }

        function showAddBlacklist() {
            Swal.fire({
                title: 'Tambah ke Blacklist',
                html: `
                    <input id="bl_identity" class="swal2-input" placeholder="No. Identitas (KTP/SIM)">
                    <input id="bl_reason" class="swal2-input" placeholder="Alasan blacklist">
                `,
                background: '#1e293b', color: '#f1f5f9',
                confirmButtonColor: '#dc2626', confirmButtonText: 'Tambahkan',
                showCancelButton: true, cancelButtonText: 'Batal',
                preConfirm: async () => {
                    const identity = document.getElementById('bl_identity').value.trim();
                    const reason   = document.getElementById('bl_reason').value.trim();
                    if (!identity) { Swal.showValidationMessage('No. identitas wajib diisi!'); return; }
                    const r = await fetch('api/security_api.php?action=add_blacklist', {
                        method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
                        body: `identity_number=${encodeURIComponent(identity)}&reason=${encodeURIComponent(reason)}`
                    });
                    return r.json();
                }
            }).then(result => {
                if (result.isConfirmed && result.value?.success) {
                    Swal.fire({icon:'success',title:'Berhasil!',text:result.value.message,background:'#1e293b',color:'#f1f5f9',timer:2000,showConfirmButton:false});
                    loadBlacklist();
                } else if (result.isConfirmed) {
                    Swal.fire({icon:'error',title:'Gagal',text:result.value?.message,background:'#1e293b',color:'#f1f5f9'});
                }
            });
        }

        async function removeBlacklist(id) {
            const conf = await Swal.fire({title:'Hapus Blacklist?',text:'Identitas ini akan diizinkan masuk kembali.',icon:'warning',showCancelButton:true,confirmButtonColor:'#dc2626',background:'#1e293b',color:'#f1f5f9'});
            if (!conf.isConfirmed) return;
            const r = await fetch('api/security_api.php?action=remove_blacklist', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`id=${id}`});
            const res = await r.json();
            if (res.success) { Swal.fire({icon:'success',title:'Dihapus',timer:1500,showConfirmButton:false,background:'#1e293b',color:'#f1f5f9'}); loadBlacklist(); }
        }

        // === LOAD LOGS ===
        async function loadLogs() {
            const limit = document.getElementById('logLimit').value;
            const container = document.getElementById('logContainer');
            try {
                const r = await fetch(`api/security_api.php?action=get_logs&limit=${limit}`);
                const data = await r.json();
                if (!data.logs || data.logs.length === 0) {
                    container.innerHTML = '<div class="p-8 text-center text-slate-500">Belum ada log aktivitas.</div>';
                    return;
                }
                const colors = { 'LOGIN':'text-blue-400', 'CHECK_IN':'text-emerald-400', 'CHECK_OUT':'text-indigo-400', 'BLACKLIST_ATTEMPT':'text-red-400', 'BLACKLIST_ADD':'text-red-400', 'GUEST_REGISTER':'text-teal-400' };
                container.innerHTML = data.logs.map(l => {
                    const clr = colors[l.action] || 'text-slate-400';
                    return `
                    <div class="px-5 py-3 flex items-start space-x-4 hover:bg-white/5 transition">
                        <div class="w-2 h-2 rounded-full bg-current mt-2 flex-shrink-0 ${clr}"></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="font-bold text-xs ${clr} uppercase tracking-wider">${l.action}</span>
                                <span class="text-slate-600 text-[11px]">|</span>
                                <span class="text-slate-400 text-xs">${l.user_fullname || 'Sistem'}</span>
                            </div>
                            <div class="text-sm text-slate-300">${l.description}</div>
                        </div>
                        <div class="text-[11px] text-slate-500 flex-shrink-0">${l.created_at.split(' ')[1]}<br><span class="text-slate-600">${l.created_at.split(' ')[0]}</span></div>
                    </div>`;
                }).join('');
            } catch(e) { container.innerHTML = '<div class="p-8 text-center text-red-400">Gagal memuat log</div>'; }
        }
    </script>
</body>
</html>
