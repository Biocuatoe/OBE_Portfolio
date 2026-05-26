<?php /* app/Views/admin/dashboard.php */ ?>
<!-- Stats Overview -->
<div class="stats-grid">
    <div class="stat-card stat-card--primary">
        <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <div class="stat-body">
            <div class="stat-value"><?= $stats['students'] ?></div>
            <div class="stat-label">Sinh viên</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon--green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
        <div class="stat-body">
            <div class="stat-value"><?= $stats['lecturers'] ?></div>
            <div class="stat-label">Giảng viên</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon--blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></div>
        <div class="stat-body">
            <div class="stat-value"><?= $stats['courses'] ?></div>
            <div class="stat-label">Môn học</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon--amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
        <div class="stat-body">
            <div class="stat-value"><?= $stats['plos'] ?></div>
            <div class="stat-label">Chuẩn đầu ra PLO</div>
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

<!-- Quick Actions + Recent Logs -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <div class="section-card">
        <div class="section-header"><h3 class="section-title">Thao tác nhanh</h3></div>
        <div style="display:flex;flex-direction:column;gap:10px">
            <a href="/admin/programs" class="quick-action-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                Quản lý chương trình đào tạo
            </a>
            <a href="/admin/courses" class="quick-action-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                Quản lý môn học
            </a>
            <a href="/admin/users" class="quick-action-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Quản lý người dùng
            </a>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header"><h3 class="section-title">Hoạt động gần đây</h3></div>
        <div class="activity-list">
            <?php foreach (array_slice($recent_logs, 0, 8) as $log): ?>
            <div class="activity-item">
                <div class="activity-dot <?= $log['role'] ?>"></div>
                <div class="activity-content">
                    <span class="activity-user"><?= htmlspecialchars($log['full_name']) ?></span>
                    <span class="activity-action"><?= htmlspecialchars($log['action']) ?></span>
                </div>
                <span class="activity-time"><?= date('H:i d/m', strtotime($log['created_at'])) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.quick-action-btn {
    display:flex; align-items:center; gap:10px;
    padding:12px 16px;
    background:var(--surface-0);
    border:1px solid var(--surface-2);
    border-radius:var(--radius-md);
    color:var(--text-secondary);
    text-decoration:none;
    font-size:13px;
    font-weight:500;
    transition:all var(--transition);
}
.quick-action-btn:hover { border-color:var(--accent); color:var(--text-primary); }

.activity-list { display:flex; flex-direction:column; gap:10px; }
.activity-item { display:flex; align-items:center; gap:10px; }
.activity-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.activity-dot.admin    { background:var(--accent); }
.activity-dot.lecturer { background:var(--emerald); }
.activity-dot.student  { background:var(--sky); }
.activity-content { flex:1; font-size:12px; }
.activity-user { font-weight:600; color:var(--text-primary); margin-right:4px; }
.activity-action { color:var(--text-muted); }
.activity-time { font-size:11px; color:var(--text-muted); white-space:nowrap; }
.btn-link { font-size:12px; color:var(--accent); text-decoration:none; }
.btn-link:hover { text-decoration:underline; }
</style>
