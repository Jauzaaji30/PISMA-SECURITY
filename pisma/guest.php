<?php
// guest.php — public guest registration page (no login required)
require_once 'api/config.php';
// Do NOT call requireLogin() — this page is public
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Tamu - PISMA Security System</title>
    <meta name="description" content="Daftarkan kunjungan Anda secara digital dan dapatkan QR Code check-in.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .bg-animate {
            background: linear-gradient(-45deg, #0f172a, #1e3a8a, #1e1b4b, #0c1a3a);
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
        }
        @keyframes gradientBG {
            0%  { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100%{ background-position: 0% 50%; }
        }
        .glass-card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .form-group label { color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; }
        .form-input {
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.1);
            color: #f1f5f9;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            width: 100%;
            transition: all 0.25s;
            font-family: 'Outfit', sans-serif;
            font-size: 0.9rem;
        }
        .form-input:focus {
            outline: none;
            border-color: #60a5fa;
            background: rgba(96,165,250,0.08);
            box-shadow: 0 0 0 4px rgba(96,165,250,0.15);
        }
        .form-input::placeholder { color: #475569; }
        .step-badge {
            width: 2rem; height: 2rem;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.8rem; color: white; flex-shrink: 0;
        }
        .floating { animation: floatUp 6s ease-in-out infinite alternate; }
        @keyframes floatUp {
            0%  { transform: translateY(0px) rotate(-2deg); }
            100%{ transform: translateY(-20px) rotate(2deg); }
        }
    </style>
</head>
<body class="bg-animate min-h-screen text-slate-100 overflow-x-hidden">

    <!-- Decorative blobs -->
    <div class="fixed top-[-20%] right-[-15%] w-[500px] h-[500px] rounded-full bg-blue-600/20 blur-[100px] pointer-events-none"></div>
    <div class="fixed bottom-[-20%] left-[-15%] w-[500px] h-[500px] rounded-full bg-indigo-600/20 blur-[100px] pointer-events-none"></div>

    <!-- Header -->
    <header class="relative z-10 py-5 px-6">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="bg-gradient-to-tr from-blue-600 to-indigo-500 p-2.5 rounded-xl shadow-lg shadow-blue-700/40">
                    <i class="fas fa-shield-halved text-white text-lg"></i>
                </div>
                <div>
                    <div class="font-bold text-lg tracking-tight">PISMA Security</div>
                    <div class="text-xs text-blue-300/70 font-light">Sistem Pelaporan Tamu Digital</div>
                </div>
            </div>
            <a href="index.php" class="text-sm text-blue-300 hover:text-white transition flex items-center space-x-2 glass-card px-4 py-2 rounded-xl">
                <i class="fas fa-lock text-xs"></i>
                <span>Login Petugas</span>
            </a>
        </div>
    </header>

    <!-- Hero -->
    <section class="relative z-10 text-center pt-8 pb-12 px-4">
        <div class="floating inline-block p-5 bg-blue-600/20 border border-blue-500/30 rounded-3xl mb-6 shadow-2xl shadow-blue-800/50">
            <i class="fas fa-clipboard-list text-5xl text-blue-400"></i>
        </div>
        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-3">
            Selamat Datang, Tamu 👋
        </h1>
        <p class="text-slate-400 max-w-xl mx-auto text-base font-light leading-relaxed">
            Lengkapi data diri Anda di bawah untuk mendapatkan <strong class="text-blue-400">QR Code Check-in</strong> yang dibutuhkan oleh petugas keamanan di pintu masuk.
        </p>
    </section>

    <!-- Main Form -->
    <main class="relative z-10 max-w-3xl mx-auto px-4 pb-16">
        <div class="glass-card rounded-3xl overflow-hidden shadow-2xl shadow-black/40">
            <!-- Progress Steps -->
            <div class="bg-white/5 px-8 py-5 border-b border-white/10 flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div class="step-badge">1</div>
                    <span class="text-sm font-semibold text-blue-300">Data Diri</span>
                </div>
                <div class="flex-1 h-0.5 bg-white/10 rounded"></div>
                <div class="flex items-center space-x-2">
                    <div class="step-badge bg-gradient-to-br from-slate-600 to-slate-700" id="step2badge">2</div>
                    <span class="text-sm font-semibold text-slate-500" id="step2label">Informasi Kunjungan</span>
                </div>
                <div class="flex-1 h-0.5 bg-white/10 rounded"></div>
                <div class="flex items-center space-x-2">
                    <div class="step-badge bg-gradient-to-br from-slate-600 to-slate-700" id="step3badge">3</div>
                    <span class="text-sm font-semibold text-slate-500" id="step3label">QR Code</span>
                </div>
            </div>

            <form id="guestForm" class="p-8 space-y-8">
                <!-- Step 1: Personal Info -->
                <div id="stepPersonal">
                    <h3 class="text-lg font-bold mb-5 flex items-center">
                        <div class="step-badge mr-3">1</div> Data Diri Tamu
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="form-group md:col-span-2">
                            <label class="block mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                                <input type="text" id="fullname" required placeholder="Tulis nama lengkap sesuai KTP" class="form-input pl-10">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="block mb-1.5">No. Identitas (KTP/SIM) <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <i class="fas fa-id-card absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                                <input type="text" id="identity_number" required placeholder="16 digit NIK / No. SIM" class="form-input pl-10">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="block mb-1.5">No. HP / WhatsApp</label>
                            <div class="relative">
                                <i class="fas fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                                <input type="text" id="phone" placeholder="08xx-xxxx-xxxx" class="form-input pl-10">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="block mb-1.5">Instansi / Perusahaan</label>
                            <div class="relative">
                                <i class="fas fa-building absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                                <input type="text" id="institution" placeholder="Asal Instansi (opsional)" class="form-input pl-10">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="block mb-1.5">No. Kendaraan</label>
                            <div class="relative">
                                <i class="fas fa-car absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                                <input type="text" id="vehicle_number" placeholder="Contoh: B 1234 CD (opsional)" class="form-input pl-10">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-white/10">
                        <h3 class="text-lg font-bold mb-5 flex items-center">
                            <div class="step-badge mr-3">2</div> Informasi Kunjungan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="form-group">
                                <label class="block mb-1.5">Bertemu dengan / Menuju Divisi <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-map-pin absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                                    <input type="text" id="destination" required placeholder="Contoh: Dept. HRD / Pak Budi" class="form-input pl-10">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="block mb-1.5">Keperluan Singkat <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <i class="fas fa-briefcase absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                                    <input type="text" id="purpose" required placeholder="Contoh: Wawancara kerja / pengiriman" class="form-input pl-10">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Agreement -->
                <div class="bg-white/5 rounded-2xl p-5 border border-white/10 flex items-start space-x-4">
                    <input type="checkbox" id="agreeCheck" class="mt-1 w-5 h-5 rounded accent-blue-500 cursor-pointer flex-shrink-0">
                    <label for="agreeCheck" class="text-sm text-slate-400 leading-relaxed cursor-pointer">
                        Saya menyatakan bahwa data yang saya isi adalah <strong class="text-slate-200">benar dan valid</strong>. Saya memahami bahwa data kunjungan ini akan dicatat dalam sistem keamanan gedung.
                    </label>
                </div>

                <button type="submit" id="submitBtn" disabled class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 opacity-50 text-white font-bold py-4 rounded-2xl transition-all duration-300 shadow-xl shadow-blue-600/30 text-base tracking-wide flex items-center justify-center space-x-2 cursor-not-allowed">
                    <i class="fas fa-qrcode"></i>
                    <span>Generate QR Code Check-in</span>
                </button>
            </form>
        </div>

        <!-- Info cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
            <div class="glass-card rounded-2xl p-5 border border-white/10 text-center">
                <div class="text-3xl mb-2">🔒</div>
                <div class="text-sm font-semibold text-slate-200">Data Aman</div>
                <div class="text-xs text-slate-500 mt-1 font-light">Informasi disimpan terenkripsi dan terlindungi</div>
            </div>
            <div class="glass-card rounded-2xl p-5 border border-white/10 text-center">
                <div class="text-3xl mb-2">⚡</div>
                <div class="text-sm font-semibold text-slate-200">Cepat & Mudah</div>
                <div class="text-xs text-slate-500 mt-1 font-light">Proses registrasi hanya butuh 1 menit</div>
            </div>
            <div class="glass-card rounded-2xl p-5 border border-white/10 text-center">
                <div class="text-3xl mb-2">📋</div>
                <div class="text-sm font-semibold text-slate-200">Tanpa Kertas</div>
                <div class="text-xs text-slate-500 mt-1 font-light">Registrasi 100% digital, no buku tamu fisik</div>
            </div>
        </div>
    </main>

    <script>
        // Enable submit only when checkbox is checked
        const checkbox = document.getElementById('agreeCheck');
        const submitBtn = document.getElementById('submitBtn');
        checkbox.addEventListener('change', () => {
            if (checkbox.checked) {
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                submitBtn.disabled = false;
            } else {
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.disabled = true;
            }
        });

        document.getElementById('guestForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const origHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses Pendaftaran...';
            btn.disabled = true;

            const formData = new URLSearchParams({
                fullname: document.getElementById('fullname').value.trim(),
                identity_number: document.getElementById('identity_number').value.trim(),
                phone: document.getElementById('phone').value.trim(),
                institution: document.getElementById('institution').value.trim(),
                vehicle_number: document.getElementById('vehicle_number').value.trim(),
                destination: document.getElementById('destination').value.trim(),
                purpose: document.getElementById('purpose').value.trim(),
            });

            try {
                const resp = await fetch('api/guest_api.php?action=register', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: formData
                });
                const result = await resp.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '🎉 Registrasi Berhasil!',
                        html: `
                            <div class="text-gray-600 mb-4">Halo, <strong>${result.guest_name}</strong>! Kode check-in Anda adalah:</div>
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-5 border border-blue-100">
                                <div class="text-xs uppercase text-blue-400 font-bold tracking-widest mb-2">QR Code / Token</div>
                                <div class="font-mono text-2xl font-bold text-indigo-700 tracking-widest">${result.qr_code}</div>
                            </div>
                            <div class="mt-4 text-sm text-gray-500">📌 Tunjukkan kode ini kepada petugas keamanan di pintu masuk untuk melakukan check-in.</div>
                        `,
                        confirmButtonText: 'Saya mengerti',
                        confirmButtonColor: '#3b82f6',
                        background: '#fff',
                        showClass: { popup: 'animate__animated animate__bounceIn' }
                    }).then(() => {
                        e.target.reset();
                        checkbox.checked = false;
                        submitBtn.classList.add('opacity-50','cursor-not-allowed');
                        submitBtn.disabled = true;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Registrasi Gagal',
                        text: result.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (err) {
                Swal.fire('Error', 'Tidak dapat terhubung ke server.', 'error');
            }

            btn.innerHTML = origHTML;
            if (checkbox.checked) btn.disabled = false;
        });
    </script>
</body>
</html>
