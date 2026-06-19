<?php /* app/Views/admin/dashboard.php */ ?>

<!-- Stats Overview -->
<div class="stats-grid">
    <div class="stat-card stat-card--primary">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?= $stats['students'] ?></div>
            <div class="stat-label">Sinh viên</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon--green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?= $stats['lecturers'] ?></div>
            <div class="stat-label">Giảng viên</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon--blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?= $stats['courses'] ?></div>
            <div class="stat-label">Môn học</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon--amber">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?= $stats['plos'] ?></div>
            <div class="stat-label">Chuẩn đầu ra PLO</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="charts-row">
    <!-- Bar chart: Sinh viên theo chương trình -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">SV theo chương trình đào tạo</div>
                <div class="chart-subtitle">Phân bố sinh viên đã đăng ký</div>
            </div>
            <div class="chart-legend">
                <div class="legend-dot" style="background:#6366f1"></div>
                <span class="legend-text">Sinh viên</span>
            </div>
        </div>
        <div class="chart-canvas-wrapper">
            <canvas id="programChart" height="200"></canvas>
        </div>
    </div>

    <!-- Doughnut: Tỷ lệ đạt / không đạt PLO -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">Tỷ lệ đạt chuẩn PLO</div>
                <div class="chart-subtitle">Threshold: 70%</div>
            </div>
        </div>
        <div class="doughnut-wrapper">
            <canvas id="ploDoughnut" height="160"></canvas>
            <div class="doughnut-center">
                <span class="doughnut-pct" id="ploPassRate">--</span>
                <span class="doughnut-label">Đạt</span>
            </div>
        </div>
        <div class="doughnut-legend-row">
            <div class="doughnut-legend-item">
                <div class="legend-dot" style="background:#10b981"></div>
                <span>Đạt</span>
                <strong id="ploPassCount">--</strong>
            </div>
            <div class="doughnut-legend-item">
                <div class="legend-dot" style="background:#f43f5e"></div>
                <span>Chưa đạt</span>
                <strong id="ploFailCount">--</strong>
            </div>
        </div>
    </div>
</div>

<!-- PLO Attainment Overview -->
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">Tổng quan đạt chuẩn PLO toàn chương trình</h3>
        <a href="/admin/report/attainment/1" class="btn-link">Xem chi tiết →</a>
    </div>
    <div class="plo-bars">
        <?php foreach ($plo_stats as $plo): ?>
            <?php $pct = (float)$plo['avg_pct']; ?>
            <div class="plo-bar-item">
                <div class="plo-bar-header">
                    <div class="plo-bar-info">
                        <span class="plo-code"><?= htmlspecialchars($plo['code']) ?></span>
                        <span class="plo-desc"><?= htmlspecialchars(mb_substr($plo['description'], 0, 60)) ?>...</span>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center">
                        <span class="text-muted text-sm"><?= $plo['student_count'] ?> SV</span>
                        <span class="plo-pct <?= $pct >= 70 ? 'pct--good' : ($pct >= 50 ? 'pct--mid' : 'pct--low') ?>">
                            <?= $pct ?>%
                        </span>
                    </div>
                </div>
                <div class="progress-track">
                    <div class="progress-fill <?= $pct >= 70 ? 'fill--good' : ($pct >= 50 ? 'fill--mid' : 'fill--low') ?>" style="width:<?= min($pct,100) ?>%"></div>
                    <div class="progress-threshold" style="left:70%"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Quick Actions + Activity Logs -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <!-- Quick Actions -->
    <div class="section-card">
        <div class="section-header"><h3 class="section-title">Thao tác nhanh</h3></div>
        <div class="quick-actions-grid">
            <a href="/admin/programs" class="qa-card qa-card--primary">
                <div class="qa-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div class="qa-body">
                    <div class="qa-title">Chương trình đào tạo</div>
                    <div class="qa-sub">Quản lý CTĐT &amp; PLO</div>
                </div>
                <svg class="qa-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="/admin/courses" class="qa-card">
                <div class="qa-icon qa-icon--blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </div>
                <div class="qa-body">
                    <div class="qa-title">Môn học</div>
                    <div class="qa-sub">Danh sách &amp; phân công</div>
                </div>
                <svg class="qa-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="/admin/users" class="qa-card">
                <div class="qa-icon qa-icon--emerald">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="qa-body">
                    <div class="qa-title">Người dùng</div>
                    <div class="qa-sub">GV, SV &amp; admin</div>
                </div>
                <svg class="qa-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="/admin/activity-logs" class="qa-card">
                <div class="qa-icon qa-icon--amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <div class="qa-body">
                    <div class="qa-title">Nhật ký hoạt động</div>
                    <div class="qa-sub">Audit trail</div>
                </div>
                <svg class="qa-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    </div>

    <!-- Activity Feed -->
    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title">Hoạt động gần đây</h3>
            <div class="activity-filter">
                <button class="filter-btn" data-range="today">Hôm nay</button>
                <button class="filter-btn active" data-range="7days">7 ngày</button>
                <button class="filter-btn" data-range="30days">30 ngày</button>
            </div>
        </div>
        <div class="activity-list" id="activityFeed">
            <?php foreach (array_slice($recent_logs, 0, 10) as $log): ?>
                <?php
                    $action = $log['action'] ?? '';
                    $iconClass = '';
                    if (stripos($action, 'login') !== false) {
                        $iconClass = 'action-login';
                    } elseif (stripos($action, 'logout') !== false) {
                        $iconClass = 'action-logout';
                    } elseif (stripos($action, 'create') !== false || stripos($action, 'add') !== false || stripos($action, 'insert') !== false || stripos($action, 'enroll') !== false) {
                        $iconClass = 'action-create';
                    } elseif (stripos($action, 'update') !== false || stripos($action, 'edit') !== false || stripos($action, 'save') !== false || stripos($action, 'grade') !== false) {
                        $iconClass = 'action-update';
                    } elseif (stripos($action, 'delete') !== false || stripos($action, 'remove') !== false) {
                        $iconClass = 'action-delete';
                    } else {
                        $iconClass = 'action-default';
                    }
                ?>
                <div class="activity-item">
                    <div class="activity-icon-wrap <?= $iconClass ?>">
                        <?php if ($iconClass === 'action-login'): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        <?php elseif ($iconClass === 'action-logout'): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <?php elseif ($iconClass === 'action-create'): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <?php elseif ($iconClass === 'action-update'): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        <?php elseif ($iconClass === 'action-delete'): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="activity-content">
                        <span class="activity-user"><?= htmlspecialchars($log['full_name']) ?></span>
                        <span class="activity-action"><?= htmlspecialchars($action) ?></span>
                        <?php if (!empty($log['entity'])): ?>
                            <span class="activity-entity">(<?= htmlspecialchars($log['entity']) ?>)</span>
                        <?php endif; ?>
                    </div>
                    <span class="activity-time"><?= date('H:i d/m', strtotime($log['created_at'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Program Bar Chart ──────────────────────────────────────────────
    const programLabels = <?= json_encode(array_column($program_student_data, 'program_code')) ?>;
    const programCounts = <?= json_encode(array_map(fn($r) => (int)$r['student_count'], $program_student_data)) ?>;
    const programNames  = <?= json_encode(array_column($program_student_data, 'program_name')) ?>;

    if (document.getElementById('programChart')) {
        new Chart(document.getElementById('programChart'), {
            type: 'bar',
            data: {
                labels: programLabels.length ? programLabels : ['Chưa có dữ liệu'],
                datasets: [{
                    label: 'Sinh viên',
                    data: programCounts.length ? programCounts : [0],
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
                            title: ctx => programNames[ctx.dataIndex] || programLabels[ctx.dataIndex],
                            label: ctx => ` ${ctx.parsed.y} sinh viên`,
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(51,65,85,0.5)' },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 },
                            stepSize: 1,
                            callback: v => Number.isInteger(v) ? v : ''
                        }
                    }
                }
            }
        });
    }

    // ── PLO Doughnut Chart ────────────────────────────────────────────
    const ploData = <?= json_encode($plo_attainment_data) ?>;
    const passCount = parseInt(ploData[0]?.pass_count ?? 0);
    const failCount = parseInt(ploData[0]?.fail_count ?? 0);
    const total = passCount + failCount;

    if (document.getElementById('ploDoughnut')) {
        new Chart(document.getElementById('ploDoughnut'), {
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
                            label: ctx => {
                                const pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                                return ` ${ctx.parsed} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Update doughnut center & legend counts
    document.getElementById('ploPassRate').textContent = total > 0 ? Math.round(passCount / total * 100) + '%' : '0%';
    document.getElementById('ploPassCount').textContent = passCount;
    document.getElementById('ploFailCount').textContent = failCount;

    // ── Activity Filter ───────────────────────────────────────────────
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            // Nếu backend hỗ trợ filter theo ngày, gọi AJAX ở đây.
            // Ví dụ: fetch(`/admin/activity-logs?range=${btn.dataset.range}`)
            // Hiện tại chỉ highlight nút đã chọn.
        });
    });
});
</script>

<style>
/* ── Quick Action Cards ─────────────────────────────────────────────── */
.quick-actions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.qa-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: var(--surface-0);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-md);
    color: var(--text-secondary);
    text-decoration: none;
    transition: all var(--transition);
    position: relative;
    overflow: hidden;
}
.qa-card::before {
    content: '';
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity var(--transition);
}
.qa-card:hover {
    border-color: var(--accent);
    color: var(--text-primary);
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(99,102,241,.15);
}
.qa-card--primary { border-color: rgba(99,102,241,.3); }

.qa-icon {
    width: 38px; height: 38px;
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    background: var(--accent-soft);
    color: var(--accent);
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
.qa-icon svg { width: 18px; height: 18px; }
.qa-icon--blue    { background: rgba(14,165,233,.12); color: var(--sky); }
.qa-icon--emerald { background: rgba(16,185,129,.12); color: var(--emerald); }
.qa-icon--amber   { background: rgba(245,158,11,.12); color: var(--amber); }

.qa-body { flex: 1; min-width: 0; position: relative; z-index: 1; }
.qa-title { font-weight: 600; font-size: 13px; color: var(--text-primary); }
.qa-sub { font-size: 11px; color: var(--text-muted); margin-top: 1px; }

.qa-arrow {
    width: 16px; height: 16px;
    color: var(--text-muted);
    flex-shrink: 0;
    transition: transform var(--transition), color var(--transition);
    position: relative; z-index: 1;
}
.qa-card:hover .qa-arrow { transform: translateX(3px); color: var(--accent); }

/* ── Activity Feed ─────────────────────────────────────────────────── */
.activity-filter {
    display: flex;
    gap: 4px;
    background: var(--surface-0);
    border: 1px solid var(--surface-2);
    border-radius: 8px;
    padding: 3px;
}
.filter-btn {
    background: none;
    border: none;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
    color: var(--text-muted);
    cursor: pointer;
    transition: all var(--transition);
    font-family: inherit;
}
.filter-btn:hover { color: var(--text-primary); }
.filter-btn.active {
    background: var(--accent);
    color: white;
}

.activity-list { display:flex; flex-direction:column; gap:8px; max-height:340px; overflow-y:auto; }
.activity-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 0;
    border-bottom: 1px solid rgba(51,65,85,.3);
}
.activity-item:last-child { border-bottom: none; }

.activity-icon-wrap {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.activity-icon-wrap svg { width: 14px; height: 14px; }

.action-login    { background: rgba(99,102,241,.15); color: #a5b4fc; }
.action-logout   { background: rgba(100,116,139,.15); color: #94a3b8; }
.action-create   { background: rgba(16,185,129,.15);  color: #6ee7b7; }
.action-update   { background: rgba(14,165,233,.15);  color: #7dd3fc; }
.action-delete   { background: rgba(244,63,94,.15);   color: #fda4af; }
.action-default  { background: rgba(100,116,139,.15); color: #94a3b8; }

.activity-content { flex:1; font-size:12px; display:flex; flex-wrap:wrap; gap:3px; align-items:center; }
.activity-user { font-weight:600; color:var(--text-primary); }
.activity-action { color:var(--text-secondary); }
.activity-entity { color:var(--text-muted); font-style:italic; }
.activity-time { font-size:11px; color:var(--text-muted); white-space:nowrap; flex-shrink:0; }

/* ── Doughnut center overlay ──────────────────────────────────────── */
.doughnut-wrapper { position: relative; }
.doughnut-center {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
}
.doughnut-pct {
    display: block;
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 800;
    font-size: 28px;
    color: var(--text-primary);
    line-height: 1;
}
.doughnut-label { font-size: 11px; color: var(--text-muted); }
.doughnut-legend-row {
    display: flex;
    justify-content: center;
    gap: 24px;
    margin-top: 12px;
}
.doughnut-legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text-secondary);
}
.doughnut-legend-item strong { color: var(--text-primary); }

.btn-link { font-size:12px; color:var(--accent); text-decoration:none; }
.btn-link:hover { text-decoration:underline; }
</style>
