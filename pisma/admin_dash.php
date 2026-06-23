<?php
require_once 'api/config.php';
requireLogin();
if ($_SESSION['role'] !== 'admin') {
    header('Location: security_dash.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PISMA SECURITY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { font-family: 'Outfit', sans-serif; }
        body { background: #0a0f1e; color: #e2e8f0; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
        
        .sidebar { background: linear-gradient(180deg, #0f172a 0%, #0a1628 100%); border-right: 1px solid rgba(255,255,255,0.06); }
        .glass { background: rgba(255,255,255,0.04); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); }
        .glass-light { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); }
        
        .stat-card { border-radius: 1.25rem; padding: 1.5rem; position: relative; overflow: hidden; transition: transform 0.25s, box-shadow 0.25s; }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-card::before { content:''; position:absolute; inset:0; border-radius:1.25rem; padding:1px; background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.02)); -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); -webkit-mask-composite: xor; mask-composite: exclude; pointer-events:none; }
        
        .stat-blue   { background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(37,99,235,0.05)); }
        .stat-green  { background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(5,150,105,0.05)); }
        .stat-amber  { background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(180,83,9,0.05)); }
        .stat-purple { background: linear-gradient(135deg, rgba(139,92,246,0.15), rgba(109,40,217,0.05)); }
        .stat-red    { background: linear-gradient(135deg, rgba(239,68,68,0.15), rgba(185,28,28,0.05)); }
        .stat-teal   { background: linear-gradient(135deg, rgba(20,184,166,0.15), rgba(13,148,136,0.05)); }
        
        .nav-link { padding: 0.65rem 1rem; border-radius: 0.85rem; font-size: 0.85rem; font-weight: 500; transition: all 0.2s; display: flex; align-items: center; gap: 0.85rem; color: #64748b; }
        .nav-link:hover { background: rgba(255,255,255,0.06); color: #94a3b8; }
        .nav-link.active { background: rgba(99,102,241,0.2); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); }
        
        .form-input { background: rgba(255,255,255,0.07); border: 1.5px solid rgba(255,255,255,0.1); color: #f1f5f9; border-radius: 0.75rem; padding: 0.6rem 1rem; transition: all 0.25s; font-family: 'Outfit', sans-serif; font-size: 0.875rem; }
        .form-input:focus { outline: none; border-color: #818cf8; background: rgba(99,102,241,0.1); }
        .form-input::placeholder { color: #475569; }
        
        .badge { display:inline-flex; align-items:center; gap:0.25rem; padding:0.2rem 0.6rem; border-radius:0.5rem; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; }
        .badge-admin    { background:rgba(139,92,246,0.2); color:#a78bfa; border:1px solid rgba(139,92,246,0.3); }
        .badge-security { background:rgba(59,130,246,0.2); color:#93c5fd; border:1px solid rgba(59,130,246,0.3); }
        .badge-active   { background:rgba(16,185,129,0.2); color:#6ee7b7; border:1px solid rgba(16,185,129,0.3); }
        .badge-inactive { background:rgba(239,68,68,0.15); color:#fca5a5; border:1px solid rgba(239,68,68,0.3); }
        .badge-login    { background:rgba(59,130,246,0.2); color:#93c5fd; border:1px solid rgba(59,130,246,0.3); }
        .badge-checkin  { background:rgba(16,185,129,0.2); color:#6ee7b7; border:1px solid rgba(16,185,129,0.3); }
        .badge-checkout { background:rgba(99,102,241,0.2); color:#a5b4fc; border:1px solid rgba(99,102,241,0.3); }
        .badge-blacklist{ background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.3); }
        .badge-register { background:rgba(20,184,166,0.2); color:#5eead4; border:1px solid rgba(20,184,166,0.3); }
        .badge-default  { background:rgba(100,116,139,0.2); color:#94a3b8; border:1px solid rgba(100,116,139,0.3); }

        @keyframes fadeUp { from { opacity:0; transform:translateY(15px); } to { opacity:1; transform:translateY(0); } }
        .fade-up { animation: fadeUp 0.4s ease forwards; }
        
        table { border-collapse: separate; border-spacing: 0; }
        thead th { position: sticky; top: 0; z-index: 5; background: #0f172a; }
        tbody tr:hover td { background: rgba(255,255,255,0.025); }
        
        .modal-bg {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
    </style>
</head>
<body class="min-h-screen">

    <div class="flex min-h-screen">

        <!-- ===== SIDEBAR ===== -->
        <aside class="sidebar w-64 flex-shrink-0 flex flex-col p-5 sticky top-0 h-screen hidden lg:flex z-20">
            <div class="flex items-center space-x-3 mb-8">
                <div class="bg-gradient-to-tr from-purple-600 to-indigo-500 p-2.5 rounded-xl shadow-lg shadow-purple-700/40">
                    <i class="fas fa-crown text-white text-lg"></i>
                </div>
                <div>
                    <div class="font-bold text-base">PISMA SECURITY</div>
                    <div class="text-xs text-purple-300/70">Admin Panel</div>
                </div>
            </div>

            <nav class="space-y-1 flex-1">
                <button onclick="showSection('overviewSection', this)" class="nav-link active w-full text-left">
                    <i class="fas fa-chart-line w-4 text-center"></i><span>Overview</span>
                </button>
                <button onclick="showSection('guestDataSection', this)" class="nav-link w-full text-left">
                    <i class="fas fa-table w-4 text-center"></i><span>Data Kunjungan</span>
                </button>
                <button onclick="showSection('userSection', this)" class="nav-link w-full text-left">
                    <i class="fas fa-users-cog w-4 text-center"></i><span>Manajemen User</span>
                </button>
                <button onclick="showSection('auditSection', this)" class="nav-link w-full text-left">
                    <i class="fas fa-scroll w-4 text-center"></i><span>Audit Trail</span>
                </button>
                <hr class="border-white/10 my-3">
                <a href="security_dash.php" class="nav-link" style="color:#60a5fa;">
                    <i class="fas fa-shield-halved w-4 text-center"></i><span>Security Mode</span>
                </a>
                <a href="guest.php" target="_blank" class="nav-link" style="color:#34d399;">
                    <i class="fas fa-user-plus w-4 text-center"></i><span>Portal Tamu</span>
                </a>
            </nav>

            <div class="glass-light rounded-2xl p-4 mt-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-purple-600/40 border border-purple-500/30 flex items-center justify-center font-bold text-purple-300">
                        <?= strtoupper(substr($_SESSION['fullname'],0,1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm truncate"><?= htmlspecialchars($_SESSION['fullname']) ?></div>
                        <div class="text-[11px] text-purple-400/80 uppercase tracking-wider font-semibold">Administrator</div>
                    </div>
                </div>
                <a href="logout.php" class="mt-3 w-full flex items-center justify-center space-x-2 text-xs text-red-400 hover:text-red-300 hover:bg-red-500/10 py-2 rounded-xl transition">
                    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- ===== MAIN ===== -->
        <main class="flex-1 overflow-y-auto">

            <div class="sticky top-0 z-10 glass border-b border-white/10 px-6 lg:px-8 py-4 flex justify-between items-center">
                <div>
                    <h1 class="font-bold text-lg" id="pageTitle">Overview</h1>
                    <p class="text-xs text-slate-500" id="pageSubtitle">Ringkasan data keamanan real-time</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="refreshCurrent()" class="text-slate-400 hover:text-purple-400 bg-white/5 border border-white/10 p-2 rounded-xl transition">
                        <i class="fas fa-sync-alt text-sm" id="refreshIcon"></i>
                    </button>
                    <button onclick="exportCSV()" class="bg-emerald-600 hover:bg-emerald-500 text-white text-sm px-4 py-2 rounded-xl font-medium flex items-center gap-2 transition shadow-lg shadow-emerald-700/20">
                        <i class="fas fa-download text-xs"></i><span>Export CSV</span>
                    </button>
                </div>
            </div>

            <div class="p-6 lg:p-8">

                <div class="lg:hidden flex space-x-2 mb-6 overflow-x-auto pb-2">
                    <button onclick="showSection('overviewSection',this)" class="nav-link active whitespace-nowrap text-xs"><i class="fas fa-chart-line mr-1"></i>Overview</button>
                    <button onclick="showSection('guestDataSection',this)" class="nav-link whitespace-nowrap text-xs"><i class="fas fa-table mr-1"></i>Kunjungan</button>
                    <button onclick="showSection('userSection',this)" class="nav-link whitespace-nowrap text-xs"><i class="fas fa-users-cog mr-1"></i>Users</button>
                    <button onclick="showSection('auditSection',this)" class="nav-link whitespace-nowrap text-xs"><i class="fas fa-scroll mr-1"></i>Audit</button>
                </div>

                <!-- ===== OVERVIEW SECTION ===== -->
                <div id="overviewSection" class="fade-up">
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                        <div class="stat-card stat-blue">
                            <div class="text-xs text-blue-300/70 uppercase tracking-wider font-semibold mb-2">Total Semua Tamu</div>
                            <div class="text-3xl font-extrabold text-white mb-1" id="stat_total_guests">–</div>
                            <div class="text-xs text-slate-500">Sepanjang waktu</div>
                            <div class="absolute right-4 top-4 text-blue-400/20 text-5xl"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="stat-card stat-green">
                            <div class="text-xs text-emerald-300/70 uppercase tracking-wider font-semibold mb-2">Check-in Hari Ini</div>
                            <div class="text-3xl font-extrabold text-emerald-400 mb-1" id="stat_today_checkin">–</div>
                            <div class="text-xs text-slate-500">Berhasil masuk</div>
                            <div class="absolute right-4 top-4 text-emerald-400/20 text-5xl"><i class="fas fa-check-double"></i></div>
                        </div>
                        <div class="stat-card stat-purple">
                            <div class="text-xs text-purple-300/70 uppercase tracking-wider font-semibold mb-2">Check-out Hari Ini</div>
                            <div class="text-3xl font-extrabold text-purple-400 mb-1" id="stat_today_checkout">–</div>
                            <div class="text-xs text-slate-500">Telah keluar gedung</div>
                            <div class="absolute right-4 top-4 text-purple-400/20 text-5xl"><i class="fas fa-door-open"></i></div>
                        </div>
                        <div class="stat-card stat-amber">
                            <div class="text-xs text-amber-300/70 uppercase tracking-wider font-semibold mb-2">Masih Bertamu</div>
                            <div class="text-3xl font-extrabold text-amber-400 mb-1" id="stat_waiting">–</div>
                            <div class="text-xs text-slate-500">Belum check-out</div>
                            <div class="absolute right-4 top-4 text-amber-400/20 text-5xl"><i class="fas fa-clock"></i></div>
                        </div>
                        <div class="stat-card stat-red">
                            <div class="text-xs text-red-300/70 uppercase tracking-wider font-semibold mb-2">Total Blacklist</div>
                            <div class="text-3xl font-extrabold text-red-400 mb-1" id="stat_blacklist">–</div>
                            <div class="text-xs text-slate-500">Identitas terblokir</div>
                            <div class="absolute right-4 top-4 text-red-400/20 text-5xl"><i class="fas fa-ban"></i></div>
                        </div>
                        <div class="stat-card stat-teal">
                            <div class="text-xs text-teal-300/70 uppercase tracking-wider font-semibold mb-2">Total Petugas</div>
                            <div class="text-3xl font-extrabold text-teal-400 mb-1" id="stat_users">–</div>
                            <div class="text-xs text-slate-500">User aktif terdaftar</div>
                            <div class="absolute right-4 top-4 text-teal-400/20 text-5xl"><i class="fas fa-user-shield"></i></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 glass rounded-2xl p-6">
                            <div class="flex justify-between items-center mb-5">
                                <div>
                                    <h3 class="font-bold text-base">Tren Kunjungan 7 Hari Terakhir</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Jumlah tamu harian</p>
                                </div>
                            </div>
                            <div class="h-64"><canvas id="weeklyChart"></canvas></div>
                        </div>
                        <div class="glass rounded-2xl p-6">
                            <div class="mb-5">
                                <h3 class="font-bold text-base">Status Kunjungan</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Hari ini</p>
                            </div>
                            <div class="h-64 flex items-center justify-center"><canvas id="donutChart"></canvas></div>
                        </div>
                    </div>
                </div>

                <!-- ===== GUEST DATA SECTION ===== -->
                <div id="guestDataSection" class="hidden fade-up">
                    <div class="flex flex-wrap gap-3 items-center justify-between mb-6">
                        <div class="text-slate-400 text-sm" id="guestDataCount">Memuat data...</div>
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" id="adminGuestSearch" placeholder="Cari nama / No. ID..." oninput="loadGuestData(1)" class="form-input pl-9 text-sm py-2 w-56">
                            </div>
                        </div>
                    </div>
                    <div class="glass rounded-2xl overflow-hidden">
                        <div class="overflow-x-auto max-h-[560px] overflow-y-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-slate-400 text-xs uppercase">
                                        <th class="px-5 py-4 text-left font-semibold">Nama / Identitas</th>
                                        <th class="px-5 py-4 text-left font-semibold">Institusi</th>
                                        <th class="px-5 py-4 text-left font-semibold">Tujuan</th>
                                        <th class="px-5 py-4 text-left font-semibold">QR Code</th>
                                        <th class="px-5 py-4 text-left font-semibold">Status</th>
                                        <th class="px-5 py-4 text-left font-semibold">Check-in</th>
                                        <th class="px-5 py-4 text-left font-semibold">Check-out</th>
                                    </tr>
                                </thead>
                                <tbody id="guestDataBody" class="divide-y divide-white/5">
                                    <tr><td colspan="7" class="text-center py-12 text-slate-500">Memuat data...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="guestPagination" class="flex justify-center gap-2 mt-5"></div>
                </div>

                <!-- ===== USER SECTION ===== -->
                <div id="userSection" class="hidden fade-up">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="text-slate-400 text-sm">Kelola akun petugas sistem Pisma Security</div>
                        <button onclick="showCreateUserModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2 transition">
                            <i class="fas fa-plus"></i> Tambah Petugas
                        </button>
                    </div>
                    <div class="glass rounded-2xl overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-slate-400 text-xs uppercase">
                                        <th class="px-5 py-4 text-left font-semibold">Nama Petugas</th>
                                        <th class="px-5 py-4 text-left font-semibold">Username</th>
                                        <th class="px-5 py-4 text-left font-semibold">Role</th>
                                        <th class="px-5 py-4 text-left font-semibold">Status</th>
                                        <th class="px-5 py-4 text-left font-semibold">Login Terakhir</th>
                                        <th class="px-5 py-4 text-left font-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="userTableBody" class="divide-y divide-white/5">
                                    <tr><td colspan="6" class="text-center py-12 text-slate-500">Memuat data...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ===== AUDIT SECTION ===== -->
                <div id="auditSection" class="hidden fade-up">
                    <div class="glass rounded-2xl overflow-hidden">
                        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-slate-400 text-xs uppercase">
                                        <th class="px-5 py-4 text-left font-semibold">Waktu</th>
                                        <th class="px-5 py-4 text-left font-semibold">Aksi</th>
                                        <th class="px-5 py-4 text-left font-semibold">User</th>
                                        <th class="px-5 py-4 text-left font-semibold">Deskripsi</th>
                                        <th class="px-5 py-4 text-left font-semibold">IP Address</th>
                                    </tr>
                                </thead>
                                <tbody id="auditBody" class="divide-y divide-white/5">
                                    <tr><td colspan="5" class="text-center py-12 text-slate-500">Memuat audit log...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Modal Create User -->
    <div id="createUserModal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="glass rounded-2xl w-full max-w-md p-6 m-4">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-xl font-bold">Tambah Petugas Baru</h3>
                <button onclick="closeCreateUserModal()" class="text-slate-400 hover:text-white text-xl">&times;</button>
            </div>
            <form id="createUserForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Nama Lengkap *</label>
                    <input type="text" id="new_fullname" required class="form-input w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Username *</label>
                    <input type="text" id="new_username" required class="form-input w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Password *</label>
                    <input type="password" id="new_password" required class="form-input w-full">
                    <p class="text-[10px] text-slate-500 mt-1">Minimal 4 karakter</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Role *</label>
                    <select id="new_role" class="form-input w-full">
                        <option value="security">Security</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-xl transition mt-4">
                    <i class="fas fa-save mr-2"></i> Simpan Petugas
                </button>
            </form>
        </div>
    </div>

    <script>
        let weeklyChart = null, donutChart = null;
        let currentSection = 'overviewSection';
        const sections = ['overviewSection','guestDataSection','userSection','auditSection'];
        const sectionTitles = {
            'overviewSection': ['Overview', 'Ringkasan data keamanan real-time'],
            'guestDataSection': ['Data Kunjungan', 'Rekap seluruh data kunjungan tamu'],
            'userSection': ['Manajemen User', 'Kelola akun petugas keamanan'],
            'auditSection': ['Audit Trail', 'Rekam jejak seluruh aktivitas sistem']
        };

        function showSection(id, btn) {
            sections.forEach(s => {
                const el = document.getElementById(s);
                if (el) el.classList.add('hidden');
            });
            const target = document.getElementById(id);
            if (target) {
                target.classList.remove('hidden');
                target.classList.remove('fade-up');
                void target.offsetWidth;
                target.classList.add('fade-up');
            }
            
            document.querySelectorAll('.nav-link').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            
            const [title, sub] = sectionTitles[id] || ['Dashboard',''];
            document.getElementById('pageTitle').textContent = title;
            document.getElementById('pageSubtitle').textContent = sub;
            currentSection = id;
            
            if (id === 'overviewSection') loadOverview();
            if (id === 'guestDataSection') loadGuestData(1);
            if (id === 'userSection') loadUsers();
            if (id === 'auditSection') loadAudit();
        }

        function refreshCurrent() {
            const icon = document.getElementById('refreshIcon');
            if (icon) icon.classList.add('fa-spin');
            setTimeout(() => { if(icon) icon.classList.remove('fa-spin'); }, 1000);
            if (currentSection === 'overviewSection') loadOverview();
            if (currentSection === 'guestDataSection') loadGuestData(currentGuestPage || 1);
            if (currentSection === 'userSection') loadUsers();
            if (currentSection === 'auditSection') loadAudit();
        }

        function showCreateUserModal() {
            document.getElementById('createUserModal').classList.remove('hidden');
        }

        function closeCreateUserModal() {
            document.getElementById('createUserModal').classList.add('hidden');
            document.getElementById('createUserForm').reset();
        }

        document.getElementById('createUserForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fullname = document.getElementById('new_fullname').value.trim();
            const username = document.getElementById('new_username').value.trim();
            const password = document.getElementById('new_password').value;
            const role = document.getElementById('new_role').value;

            if (!fullname || !username || !password) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Semua field harus diisi!', background: '#0f172a', color: '#f1f5f9' });
                return;
            }

            if (password.length < 4) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Password minimal 4 karakter!', background: '#0f172a', color: '#f1f5f9' });
                return;
            }

            try {
                const res = await fetch('api/report_api.php?action=create_user', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `fullname=${encodeURIComponent(fullname)}&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}&role=${role}`
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false, background: '#0f172a', color: '#f1f5f9' });
                    closeCreateUserModal();
                    loadUsers();
                    loadOverview();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, background: '#0f172a', color: '#f1f5f9' });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal terhubung ke server', background: '#0f172a', color: '#f1f5f9' });
            }
        });

        // Close modal when clicking outside
        document.getElementById('createUserModal')?.addEventListener('click', (e) => {
            if (e.target === document.getElementById('createUserModal')) closeCreateUserModal();
        });

        async function loadOverview() {
            try {
                const r = await fetch('api/report_api.php?action=dashboard_stats');
                const d = await r.json();
                
                document.getElementById('stat_total_guests').textContent   = d.total_guests ?? '–';
                document.getElementById('stat_today_checkin').textContent  = d.today_checkin ?? '–';
                document.getElementById('stat_today_checkout').textContent = d.today_checkout ?? '–';
                document.getElementById('stat_waiting').textContent        = d.waiting ?? '–';
                document.getElementById('stat_blacklist').textContent      = d.blacklist_count ?? '–';
                document.getElementById('stat_users').textContent          = d.total_users ?? '–';

                const weeklyLabels = [];
                const weeklyValues = [];
                for (let i = 6; i >= 0; i--) {
                    const dt = new Date();
                    dt.setDate(dt.getDate() - i);
                    weeklyLabels.push(dt.toLocaleDateString('id-ID', { weekday:'short', day:'numeric', month:'short' }));
                    const found = (d.weekly_data || []).find(w => w.date === dt.toISOString().split('T')[0]);
                    weeklyValues.push(found ? parseInt(found.total) : 0);
                }
                
                if (weeklyChart) weeklyChart.destroy();
                const wCtx = document.getElementById('weeklyChart').getContext('2d');
                weeklyChart = new Chart(wCtx, {
                    type: 'bar',
                    data: {
                        labels: weeklyLabels,
                        datasets: [{
                            label: 'Jumlah Tamu',
                            data: weeklyValues,
                            backgroundColor: 'rgba(99,102,241,0.6)',
                            borderColor: 'rgba(99,102,241,1)',
                            borderWidth: 1.5,
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { color:'#64748b', stepSize:1 }, grid: { color: 'rgba(255,255,255,0.05)' }, border: { display: false } },
                            x: { ticks: { color:'#64748b', font:{ size:11 } }, grid: { display:false }, border: { display: false } }
                        }
                    }
                });

                const checkin  = parseInt(d.today_checkin) || 0;
                const checkout = parseInt(d.today_checkout) || 0;
                const waiting  = parseInt(d.waiting) || 0;
                const inside   = Math.max(0, checkin - checkout);
                
                if (donutChart) donutChart.destroy();
                const dCtx = document.getElementById('donutChart').getContext('2d');
                donutChart = new Chart(dCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Check-in', 'Selesai', 'Waiting', 'Di Rumah'],
                        datasets: [{
                            data: [checkin, checkout, waiting, inside],
                            backgroundColor: ['#6366f1','#10b981','#f59e0b','#3b82f6'],
                            borderWidth: 0, hoverOffset: 6
                        }]
                    },
                    options: {
                        cutout: '72%',
                        plugins: {
                            legend: { position:'bottom', labels: { color:'#94a3b8', font:{size:11}, boxWidth:10, padding:12 } }
                        }
                    }
                });
            } catch(e) { console.error('Failed to load overview', e); }
        }

        let currentGuestPage = 1;
        async function loadGuestData(page = 1) {
            currentGuestPage = page;
            const search = document.getElementById('adminGuestSearch')?.value || '';
            const body = document.getElementById('guestDataBody');
            if (body) body.innerHTML = '<tr><td colspan="7" class="text-center py-10 text-slate-500"><i class="fas fa-circle-notch fa-spin mr-2"></i>Memuat...</td></tr>';
            
            try {
                const r = await fetch(`api/report_api.php?action=get_all_guests&page=${page}&search=${encodeURIComponent(search)}`);
                const data = await r.json();
                
                const countEl = document.getElementById('guestDataCount');
                if (countEl) countEl.textContent = `Total ${data.total} kunjungan`;
                
                if (!data.visits || data.visits.length === 0) {
                    if (body) body.innerHTML = '<tr><td colspan="7" class="text-center py-10 text-slate-500">Tidak ada data</td></tr>';
                    return;
                }
                
                const statusBadge = s => s === 'checked_in' ? '<span class="badge badge-checkin">Checked-in</span>'
                    : s === 'completed' ? '<span class="badge badge-checkout">Selesai</span>'
                    : '<span class="badge" style="background:rgba(245,158,11,0.2);color:#fbbf24;border:1px solid rgba(245,158,11,0.3)">Waiting</span>';
                
                if (body) {
                    body.innerHTML = data.visits.map(v => `
                        <tr class="transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-200">${escapeHtml(v.fullname)}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">${escapeHtml(v.identity_number)}</div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-400 text-sm">${escapeHtml(v.institution) || '—'}</td>
                            <td class="px-5 py-3.5 text-slate-400 text-sm max-w-[150px] truncate" title="${escapeHtml(v.destination)}">${escapeHtml(v.destination)}</td>
                            <td class="px-5 py-3.5"><span class="font-mono text-blue-300 text-xs bg-blue-900/30 border border-blue-800/40 px-2 py-1 rounded-lg">${escapeHtml(v.qr_code)}</span></td>
                            <td class="px-5 py-3.5">${statusBadge(v.status)}</td>
                            <td class="px-5 py-3.5 text-slate-400 text-xs">${v.check_in_time || '—'}</td>
                            <td class="px-5 py-3.5 text-slate-400 text-xs">${v.checkout_time || '—'}</td>
                        </tr>
                    `).join('');
                }
                
                const pages = parseInt(data.pages);
                const pg = document.getElementById('guestPagination');
                if (pg) {
                    if (pages <= 1) { pg.innerHTML = ''; return; }
                    let btns = '';
                    for (let i=1; i<=pages; i++) {
                        btns += `<button onclick="loadGuestData(${i})" class="px-3 py-1.5 rounded-lg text-sm transition ${i===page ? 'bg-indigo-600 text-white font-bold' : 'bg-white/5 text-slate-400 hover:bg-white/10'}">${i}</button>`;
                    }
                    pg.innerHTML = btns;
                }
            } catch(e) { console.error(e); if(body) body.innerHTML = '<tr><td colspan="7" class="text-center py-10 text-red-400">Gagal memuat data</td></tr>'; }
        }

        async function loadUsers() {
            const body = document.getElementById('userTableBody');
            if (!body) return;
            try {
                const r = await fetch('api/report_api.php?action=get_users');
                const data = await r.json();
                if (!data.users || data.users.length === 0) {
                    body.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-slate-500">Tidak ada user</td></tr>';
                    return;
                }
                body.innerHTML = data.users.map(u => `
                    <tr class="transition-colors" id="userRow_${u.id}">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl ${u.role === 'admin' ? 'bg-purple-600/30 text-purple-300' : 'bg-blue-600/30 text-blue-300'} flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    ${escapeHtml(u.fullname.charAt(0).toUpperCase())}
                                </div>
                                <div class="font-semibold text-slate-200">${escapeHtml(u.fullname)}</div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-400 text-sm">${escapeHtml(u.username)}</td>
                        <td class="px-5 py-3.5"><span class="badge ${u.role === 'admin' ? 'badge-admin' : 'badge-security'}">${u.role}</span></td>
                        <td class="px-5 py-3.5"><span class="badge ${parseInt(u.is_active) ? 'badge-active' : 'badge-inactive'}">${parseInt(u.is_active) ? 'Aktif' : 'Nonaktif'}</span></td>
                        <td class="px-5 py-3.5 text-slate-400 text-xs">${u.last_login || 'Belum pernah login'}</td>
                        <td class="px-5 py-3.5">
                            <button onclick="toggleUser(${u.id}, ${u.is_active})" class="text-xs px-3 py-1.5 rounded-lg border transition font-medium ${parseInt(u.is_active) ? 'border-red-600/30 text-red-400 hover:bg-red-600/10' : 'border-emerald-600/30 text-emerald-400 hover:bg-emerald-600/10'}">
                                ${parseInt(u.is_active) ? '<i class="fas fa-ban mr-1"></i>Nonaktifkan' : '<i class="fas fa-check mr-1"></i>Aktifkan'}
                            </button>
                        </td>
                    </tr>
                `).join('');
            } catch(e) { body.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-red-400">Gagal memuat data</td></tr>'; }
        }

        async function toggleUser(id, currentStatus) {
            const action = parseInt(currentStatus) ? 'nonaktifkan' : 'aktifkan';
            const conf = await Swal.fire({
                title: `${action.charAt(0).toUpperCase() + action.slice(1)} User?`, icon: 'question',
                showCancelButton: true, confirmButtonText: 'Ya, lanjutkan', cancelButtonText: 'Batal',
                background:'#0f172a', color:'#f1f5f9', confirmButtonColor:'#6366f1'
            });
            if (!conf.isConfirmed) return;
            const r = await fetch('api/report_api.php?action=toggle_user', {
                method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${id}`
            });
            const res = await r.json();
            if (res.success) {
                Swal.fire({icon:'success',title:'Berhasil',timer:1500,showConfirmButton:false,background:'#0f172a',color:'#f1f5f9'});
                loadUsers();
                loadOverview();
            }
        }

        async function loadAudit() {
            const body = document.getElementById('auditBody');
            if (!body) return;
            try {
                const r = await fetch('api/report_api.php?action=audit_logs&limit=100');
                const data = await r.json();
                if (!data.logs || data.logs.length === 0) {
                    body.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-slate-500">Tidak ada data audit</td></tr>';
                    return;
                }
                const actionBadge = a => {
                    const map = { 'LOGIN':'badge-login','CHECK_IN':'badge-checkin','CHECK_OUT':'badge-checkout','GUEST_REGISTER':'badge-register' };
                    const cls = map[a] || (a.includes('BLACKLIST') ? 'badge-blacklist' : 'badge-default');
                    return `<span class="badge ${cls}">${a.replace('_',' ')}</span>`;
                };
                body.innerHTML = data.logs.map(l => `
                    <tr class="transition-colors">
                        <td class="px-5 py-3.5 text-xs text-slate-500 whitespace-nowrap">${escapeHtml(l.created_at)}</td>
                        <td class="px-5 py-3.5">${actionBadge(l.action)}</td>
                        <td class="px-5 py-3.5 text-slate-400 text-sm">${escapeHtml(l.user_fullname) || 'Sistem'}</td>
                        <td class="px-5 py-3.5 text-slate-300 text-sm">${escapeHtml(l.description)}</td>
                        <td class="px-5 py-3.5 font-mono text-xs text-slate-500">${escapeHtml(l.ip_address)}</td>
                    </tr>
                `).join('');
            } catch(e) { body.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-red-400">Gagal memuat audit</td></tr>'; }
        }

        function exportCSV() {
            window.location.href = 'api/report_api.php?action=export_csv';
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        loadOverview();
    </script>
</body>
</html>