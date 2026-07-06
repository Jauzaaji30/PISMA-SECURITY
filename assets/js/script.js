// ========================================
// PISMA SECURITY SYSTEM - MAIN JAVASCRIPT
// ========================================

// Global Variables
let currentView = 'security';
let refreshInterval = null;

// Toast Notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Loading Indicator
function showLoading(elementId) {
    const el = document.getElementById(elementId);
    if (el) {
        el.innerHTML = '<div class="spinner" style="margin: 20px auto;"></div>';
    }
}

// Format Date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Load Security Data
async function loadSecurityData() {
    try {
        // Load guests
        const guestsResp = await fetch('api/security_api.php?action=get_today_guests');
        const guestsData = await guestsResp.json();
        
        if (guestsData.guests) {
            const container = document.getElementById('guestListContainer');
            if (container) {
                if (guestsData.guests.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-users fa-3x mb-2 opacity-50"></i>
                            <p>Belum ada tamu hari ini</p>
                        </div>
                    `;
                } else {
                    container.innerHTML = guestsData.guests.map(guest => `
                        <div class="guest-item">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center mb-1">
                                        <span class="guest-status ${guest.status === 'checked_in' ? 'status-checked' : 'status-waiting'}"></span>
                                        <h4 class="font-semibold text-gray-800">${escapeHtml(guest.fullname)}</h4>
                                    </div>
                                    <p class="text-xs text-gray-500 mb-1">
                                        <i class="fas fa-building mr-1"></i> ${escapeHtml(guest.institution || 'Umum')}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        <i class="fas fa-tag mr-1"></i> ${escapeHtml(guest.purpose?.substring(0, 50) || '-')}
                                    </p>
                                    ${guest.qr_code ? `<p class="text-xs font-mono text-blue-600 mt-1"><i class="fas fa-qrcode"></i> ${guest.qr_code}</p>` : ''}
                                </div>
                                <div class="text-right">
                                    <span class="badge ${guest.status === 'checked_in' ? 'badge-success' : 'badge-warning'}">
                                        ${guest.status === 'checked_in' ? '<i class="fas fa-check-circle"></i> Check-in' : '<i class="fas fa-clock"></i> Menunggu'}
                                    </span>
                                    ${guest.status !== 'checked_in' ? `
                                        <button onclick="quickCheckin('${guest.qr_code}')" class="btn btn-primary text-xs mt-2 px-3 py-1">
                                            <i class="fas fa-qrcode"></i> Check-in
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
            }
        }
        
        // Load logs
        const logsResp = await fetch('api/security_api.php?action=get_logs');
        const logsData = await logsResp.json();
        
        if (logsData.logs) {
            const logContainer = document.getElementById('securityLog');
            if (logContainer) {
                logContainer.innerHTML = logsData.logs.map(log => `
                    <div class="flex items-start gap-2 p-2 border-b border-gray-100 hover:bg-gray-50">
                        <i class="fas ${log.action === 'CHECK_IN' ? 'fa-sign-in-alt text-green-500' : log.action === 'LOGIN' ? 'fa-sign-in-alt text-blue-500' : 'fa-info-circle text-gray-400'} mt-0.5"></i>
                        <div class="flex-1">
                            <div class="flex justify-between">
                                <span class="text-xs font-medium">${escapeHtml(log.action)}</span>
                                <span class="text-xs text-gray-400">${formatDate(log.created_at)}</span>
                            </div>
                            <p class="text-xs text-gray-600">${escapeHtml(log.description)}</p>
                        </div>
                    </div>
                `).join('');
            }
        }
    } catch (error) {
        console.error('Error loading security data:', error);
        showToast('Gagal memuat data', 'error');
    }
}

// Quick Checkin
async function quickCheckin(qrCode) {
    if (!qrCode) return;
    
    try {
        const response = await fetch('api/guest_api.php?action=checkin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `qr_code=${encodeURIComponent(qrCode)}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            loadSecurityData();
            // Trigger sound effect (optional)
            playBeep();
        } else {
            showToast(result.message, 'error');
            if (result.message.includes('BLACKLIST')) {
                document.getElementById('blacklistAlert').innerHTML = `
                    <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded">
                        <p class="text-red-700 text-sm"><i class="fas fa-ban"></i> ${result.message}</p>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Checkin error:', error);
        showToast('Gagal melakukan check-in', 'error');
    }
}

// Checkin from input
async function checkinGuest() {
    const qrInput = document.getElementById('scanQRInput');
    const qrCode = qrInput?.value.trim();
    
    if (!qrCode) {
        showToast('Masukkan QR Code terlebih dahulu', 'warning');
        return;
    }
    
    await quickCheckin(qrCode);
    if (qrInput) qrInput.value = '';
}

// Register Guest
async function registerGuest(event) {
    event.preventDefault();
    
    const formData = new URLSearchParams({
        fullname: document.getElementById('fullname')?.value || '',
        identity_number: document.getElementById('identity_number')?.value || '',
        phone: document.getElementById('phone')?.value || '',
        institution: document.getElementById('institution')?.value || '',
        vehicle_number: document.getElementById('vehicle_number')?.value || '',
        destination: document.getElementById('destination')?.value || '',
        purpose: document.getElementById('purpose')?.value || ''
    });
    
    // Validation
    if (!formData.get('fullname') || !formData.get('identity_number') || !formData.get('purpose')) {
        showToast('Lengkapi data wajib (*)', 'warning');
        return;
    }
    
    try {
        const response = await fetch('api/guest_api.php?action=register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            const resultDiv = document.getElementById('guestResult');
            if (resultDiv) {
                resultDiv.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            <h4 class="font-semibold text-green-700">Registrasi Berhasil!</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">QR Code untuk check-in:</p>
                        <div class="bg-white p-3 rounded-lg text-center qr-pulse">
                            <code class="font-mono text-lg font-bold text-blue-600">${result.qr_code}</code>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Simpan QR Code ini. Tunjukkan ke petugas keamanan saat check-in.</p>
                    </div>
                `;
                resultDiv.classList.remove('hidden');
            }
            
            // Reset form
            event.target.reset();
            showToast('Registrasi berhasil! QR Code telah dibuat', 'success');
            
            // Auto hide after 10 seconds
            setTimeout(() => {
                if (resultDiv) resultDiv.classList.add('hidden');
            }, 10000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Register error:', error);
        showToast('Gagal melakukan registrasi', 'error');
    }
}

// Load Admin Data
async function loadAdminData() {
    try {
        const response = await fetch('api/report_api.php?action=dashboard_stats');
        const data = await response.json();
        
        document.getElementById('statTotal').innerText = data.total_guests || 0;
        document.getElementById('statCheckedIn').innerText = data.checked_in || 0;
        document.getElementById('statBlacklist').innerText = data.blacklist_count || 0;
        
        // Update chart
        if (window.chartInstance) {
            window.chartInstance.data.datasets[0].data = [data.checked_in || 0, (data.total_guests || 0) - (data.checked_in || 0)];
            window.chartInstance.update();
        }
        
        // Load audit logs
        const auditResp = await fetch('api/report_api.php?action=audit_logs');
        const auditData = await auditResp.json();
        
        if (auditData.logs) {
            const tbody = document.getElementById('auditTableBody');
            if (tbody) {
                tbody.innerHTML = auditData.logs.map(log => `
                    <tr>
                        <td class="p-2 text-xs">${formatDate(log.created_at)}</td>
                        <td class="p-2"><span class="badge badge-info text-xs">${escapeHtml(log.action)}</span></td>
                        <td class="p-2 text-sm">${escapeHtml(log.description)}</td>
                        <td class="p-2 text-xs font-mono">${escapeHtml(log.ip_address)}</td>
                    </tr>
                `).join('');
            }
        }
    } catch (error) {
        console.error('Error loading admin data:', error);
        showToast('Gagal memuat data admin', 'error');
    }
}

// Export CSV
async function exportCSV() {
    window.location.href = 'api/report_api.php?action=export_csv';
    showToast('Mengunduh file CSV...', 'info');
}

// Switch View
function switchView(view) {
    currentView = view;
    
    // Hide all views
    document.getElementById('guestView')?.classList.add('hidden');
    document.getElementById('securityView')?.classList.add('hidden');
    document.getElementById('adminView')?.classList.add('hidden');
    
    // Show selected view
    if (view === 'guest') {
        document.getElementById('guestView')?.classList.remove('hidden');
        // Stop auto refresh
        if (refreshInterval) clearInterval(refreshInterval);
    } else if (view === 'security') {
        document.getElementById('securityView')?.classList.remove('hidden');
        loadSecurityData();
        // Auto refresh every 10 seconds
        if (refreshInterval) clearInterval(refreshInterval);
        refreshInterval = setInterval(loadSecurityData, 10000);
    } else if (view === 'admin') {
        document.getElementById('adminView')?.classList.remove('hidden');
        loadAdminData();
        if (refreshInterval) clearInterval(refreshInterval);
        refreshInterval = setInterval(loadAdminData, 15000);
    }
}

// Play beep sound (optional - menggunakan Web Audio API)
function playBeep() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 880;
        gainNode.gain.value = 0.3;
        
        oscillator.start();
        gainNode.gain.exponentialRampToValueAtTime(0.00001, audioContext.currentTime + 0.3);
        oscillator.stop(audioContext.currentTime + 0.3);
        
        // Close context after sound
        setTimeout(() => audioContext.close(), 500);
    } catch(e) {
        // Silent fail if audio not supported
    }
}

// Escape HTML to prevent XSS
function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Attach event listeners
    const registerForm = document.getElementById('guestRegisterForm');
    if (registerForm) {
        registerForm.addEventListener('submit', registerGuest);
    }
    
    const scanInput = document.getElementById('scanQRInput');
    if (scanInput) {
        scanInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                checkinGuest();
            }
        });
    }
    
    // Initialize chart for admin
    const chartCanvas = document.getElementById('visitChart');
    if (chartCanvas) {
        const ctx = chartCanvas.getContext('2d');
        window.chartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Sedang Check-in', 'Menunggu'],
                datasets: [{
                    data: [0, 0],
                    backgroundColor: ['#3b82f6', '#e2e8f0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    // Start with security view by default
    const role = window.role || 'security';
    if (role === 'admin') {
        switchView('admin');
    } else {
        switchView('security');
    }
    
    console.log('PISMA Security System Ready!');
});