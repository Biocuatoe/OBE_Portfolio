/* ================================================================
 iSchool OBE System - app.js
 Global utilities: Toast, Sidebar, Chart.js helpers
 ================================================================ */

'use strict';

// ── Toast Notification System ────────────────────────────────────
const Toast = (() => {
 const container = document.getElementById('toast-container');

 function show(message, type = 'info', duration = 3500) {
     if (!container) return;

     const icons = {
         success: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>`,
         error:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
         info:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
     };

     const toast = document.createElement('div');
     toast.className = `toast toast-${type}`;
     toast.innerHTML = `${icons[type] || icons.info}<span>${message}</span>`;
     container.appendChild(toast);

     setTimeout(() => {
         toast.style.transition = 'opacity .3s, transform .3s';
         toast.style.opacity = '0';
         toast.style.transform = 'translateY(10px)';
         setTimeout(() => toast.remove(), 320);
     }, duration);
 }

 return { show, success: m => show(m, 'success'), error: m => show(m, 'error'), info: m => show(m, 'info') };
})();

// ── Sidebar Toggle (Mobile) ──────────────────────────────────────
const sidebar      = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');

if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });

    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 &&
            sidebar.classList.contains('open') &&
            !sidebar.contains(e.target) &&
            e.target !== sidebarToggle)
        {
            sidebar.classList.remove('open');
        }
    });
}

// ── CSRF Token helper ────────────────────────────────────────────
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

// ── Chart.js helpers (for Admin Dashboard) ───────────────────────
// Chart.js 4.x is loaded via CDN in layouts/main.php
window.DashboardCharts = {
    /**
     * Render a bar chart of student counts per program.
     * @param {string} canvasId - Canvas element ID
     * @param {string[]} labels - Program codes
     * @param {number[]} counts - Student counts
     * @param {string[]} names  - Full program names (for tooltip title)
     */
    renderProgramBar(canvasId, labels, counts, names) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['Chưa có dữ liệu'],
                datasets: [{
                    label: 'Sinh viên',
                    data: counts.length ? counts : [0],
                    backgroundColor: 'rgba(99,102,241,0.75)',
                    borderColor: '#6366f1',
                    borderWidth: 1,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: ctx2 => names[ctx2.dataIndex] || labels[ctx2.dataIndex],
                            label: ctx2 => ` ${ctx2.parsed.y} sinh viên`,
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 } } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(51,65,85,0.5)' },
                        ticks: { color: '#64748b', font: { size: 11 }, stepSize: 1, callback: v => Number.isInteger(v) ? v : '' }
                    }
                }
            }
        });
    },

    /**
     * Render a doughnut chart of PLO pass/fail ratio.
     * @param {string} canvasId  - Canvas element ID
     * @param {number} passCount - Students meeting threshold (≥70%)
     * @param {number} failCount - Students below threshold
     */
    renderPloDoughnut(canvasId, passCount, failCount) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        const total = passCount + failCount;
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Đạt (≥70%)', 'Chưa đạt (<70%)'],
                datasets: [{
                    data: [passCount || 0, failCount || 0],
                    backgroundColor: ['#10b981', '#f43f5e'],
                    borderColor: '#1e293b',
                    borderWidth: 3,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx2 => {
                                const pct = total > 0 ? Math.round(ctx2.parsed / total * 100) : 0;
                                return ` ${ctx2.parsed} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
};

// ── Activity log filter (Admin Dashboard) ─────────────────────────
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        // Hook point: fetch(`/admin/activity-logs/filter?range=${this.dataset.range}`)
        // and update #activityFeed.innerHTML with the response.
    });
});

// ── Expose globals ───────────────────────────────────────────────
window.Toast        = Toast;
window.getCsrfToken = getCsrfToken;
