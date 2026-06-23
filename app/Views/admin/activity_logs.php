<?php
/* app/Views/admin/activity_logs.php — Premium Timeline Layout */
function timeAgo(string $createdAt): string {
    $dt = new DateTime($createdAt);
    $now = new DateTime();
    $diff = $now->getTimestamp() - $dt->getTimestamp();

    if ($diff < 60)        return $diff . ' giây trước';
    if ($diff < 3600)     return floor($diff / 60) . ' phút trước';
    if ($diff < 86400)    return floor($diff / 3600) . ' giờ trước';
    if ($diff < 604800)   return floor($diff / 86400) . ' ngày trước';
    return $dt->format('d/m/Y');
}

function actionMeta(string $action): array {
    $a = strtolower($action);

    if (strpos($a, 'login') !== false) {
        return ['icon' => 'key',    'color' => '#64748b', 'bg' => '#f1f5f9', 'label' => 'Đăng nhập'];
    }
    if (strpos($a, 'logout') !== false) {
        return ['icon' => 'logout', 'color' => '#64748b', 'bg' => '#f1f5f9', 'label' => 'Đăng xuất'];
    }
    if (preg_match('/\b(create|add|insert|enroll|register)\b/', $a)) {
        return ['icon' => 'plus',   'color' => '#10b981', 'bg' => '#ecfdf5', 'label' => 'Tạo mới'];
    }
    if (preg_match('/\b(update|edit|save|grade|submit)\b/', $a)) {
        return ['icon' => 'pencil', 'color' => '#4f46e5', 'bg' => '#eef2ff', 'label' => 'Cập nhật'];
    }
    if (preg_match('/\b(delete|remove|drop)\b/', $a)) {
        return ['icon' => 'trash',  'color' => '#ef4444', 'bg' => '#fef2f2', 'label' => 'Xóa'];
    }
    if (preg_match('/\b(activate|enable)\b/', $a)) {
        return ['icon' => 'check',  'color' => '#10b981', 'bg' => '#ecfdf5', 'label' => 'Kích hoạt'];
    }
    if (preg_match('/\b(deactivate|disable)\b/', $a)) {
        return ['icon' => 'x',      'color' => '#ef4444', 'bg' => '#fef2f2', 'label' => 'Vô hiệu hóa'];
    }
    return ['icon' => 'activity', 'color' => '#64748b', 'bg' => '#f8fafc', 'label' => $action];
}

function roleIcon(string $role): array {
    return match($role) {
        'admin'    => ['bg' => 'rgba(79,70,229,0.10)',  'color' => '#4f46e5', 'letter' => 'A'],
        'lecturer' => ['bg' => 'rgba(14,165,233,0.10)', 'color' => '#0ea5e9', 'letter' => 'G'],
        'student'  => ['bg' => 'rgba(16,185,129,0.10)',  'color' => '#10b981', 'letter' => 'S'],
        default    => ['bg' => '#f1f5f9',                'color' => '#94a3b8', 'letter' => 'U'],
    };
}

function roleLabel(string $role): string {
    return match($role) {
        'admin'    => 'Admin',
        'lecturer' => 'Giảng viên',
        'student'  => 'Sinh viên',
        default    => ucfirst($role),
    };
}

function entityLabel(string $entity): string {
    return match(strtolower($entity)) {
        'user'      => 'Tài khoản',
        'program'   => 'Chương trình',
        'course'    => 'Môn học',
        'plo'       => 'PLO',
        'clo'       => 'CLO',
        'rubric'    => 'Rubric',
        'score'     => 'Điểm',
        'assignment'=> 'Phân công',
        default     => ucfirst($entity),
    };
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-heading">Nhật ký hoạt động</h2>
        <p class="page-sub">Theo dõi mọi thay đổi trong hệ thống OBE</p>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-grid" style="margin-bottom:24px">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(79,70,229,0.10);color:#4f46e5">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
        </div>
        <div>
            <div class="stat-value"><?= number_format($total_pages * 30) ?></div>
            <div class="stat-label">Tổng bản ghi</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16,185,129,0.10);color:#10b981">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div>
            <?php
            $latest = $logs[0]['created_at'] ?? null;
            ?>
            <div class="stat-value" style="font-size:15px"><?= $latest ? timeAgo($latest) : '—' ?></div>
            <div class="stat-label">Hoạt động gần nhất</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(14,165,233,0.10);color:#0ea5e9">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div>
            <div class="stat-value"><?= count($users_list) ?></div>
            <div class="stat-label">Người dùng</div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar" style="margin-bottom:20px">
    <form method="GET" action="/admin/activity-log" id="filterForm" style="display:contents;gap:12px;flex-wrap:wrap">
        <div class="form-group" style="margin-bottom:0">
            <select name="user_id" id="filter-user" class="form-control">
                <option value="">— Người dùng —</option>
                <?php foreach ($users_list as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $filter_user_id == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom:0">
            <select name="action" id="filter-action" class="form-control">
                <option value="">— Hành động —</option>
                <?php foreach ($distinct_actions as $act): ?>
                    <option value="<?= htmlspecialchars($act) ?>" <?= $filter_action === $act ? 'selected' : '' ?>>
                        <?= htmlspecialchars($act) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom:0">
            <input type="date" name="date_from" id="filter-date-from" class="form-control" value="<?= htmlspecialchars($filter_date_from) ?>" placeholder="Từ ngày">
        </div>

        <div class="form-group" style="margin-bottom:0">
            <input type="date" name="date_to" id="filter-date-to" class="form-control" value="<?= htmlspecialchars($filter_date_to) ?>" placeholder="Đến ngày">
        </div>

        <div style="display:flex;gap:6px;align-items:center;padding-bottom:2px">
            <button type="submit" class="btn btn-primary btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Lọc
            </button>
            <a href="/admin/activity-log" class="btn btn-secondary btn-sm">Xoá</a>
        </div>
    </form>
</div>

<!-- Timeline Container -->
<div class="card" style="padding:0">
    <?php if (empty($logs)): ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            <p>Không có nhật ký nào phù hợp.</p>
        </div>
    <?php else: ?>
        <div class="timeline-container">
            <?php
            $iconMap = [
                'key'     => '<path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>',
                'logout'  => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
                'plus'    => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
                'pencil'  => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
                'trash'   => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
                'check'   => '<polyline points="20 6 9 17 4 12"/>',
                'x'       => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
                'activity'=> '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
            ];

            $nowGroup = '';
            foreach ($logs as $i => $log):
                $dt = new DateTime($log['created_at']);
                $today = (new DateTime('today'))->format('Y-m-d');
                $yesterday = (new DateTime('yesterday'))->format('Y-m-d');
                $logDate = $dt->format('Y-m-d');

                $dateGroup = match($logDate) {
                    $today     => 'Hôm nay',
                    $yesterday => 'Hôm qua',
                    default   => $dt->format('d/m/Y'),
                };

                $showDateDivider = $dateGroup !== $nowGroup;
                if ($showDateDivider) {
                    $nowGroup = $dateGroup;
                }

                $meta = actionMeta($log['action']);
                $userRole = roleIcon($log['role']);
                $entity = entityLabel($log['entity'] ?? '');
                $timeStr = $dt->format('H:i');
                $ip = htmlspecialchars($log['ip_address'] ?? '—');
                $fullName = htmlspecialchars($log['full_name'] ?? $log['username'] ?? '—');
            ?>
                <?php if ($showDateDivider): ?>
                    <div class="timeline-date-divider">
                        <span><?= $dateGroup ?></span>
                    </div>
                <?php endif; ?>

                <div class="timeline-item" style="--item-color:<?= $meta['color'] ?>;--item-bg:<?= $meta['bg'] ?>">
                    <!-- Icon dot -->
                    <div class="timeline-icon" style="background:<?= $meta['bg'] ?>;color:<?= $meta['color'] ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="16" height="16">
                            <?= $iconMap[$meta['icon']] ?? $iconMap['activity'] ?>
                        </svg>
                    </div>

                    <!-- Content card -->
                    <div class="timeline-card">
                        <div class="timeline-card-header">
                            <div class="timeline-user">
                                <div class="timeline-avatar" style="background:<?= $userRole['bg'] ?>;color:<?= $userRole['color'] ?>">
                                    <?= $userRole['letter'] ?>
                                </div>
                                <div>
                                    <span class="timeline-name"><?= $fullName ?></span>
                                    <span class="timeline-role" style="color:<?= $userRole['color'] ?>"><?= roleLabel($log['role']) ?></span>
                                </div>
                            </div>
                            <div class="timeline-meta">
                                <span class="timeline-time"><?= $timeStr ?></span>
                                <span class="timeline-action-badge" style="background:<?= $meta['bg'] ?>;color:<?= $meta['color'] ?>">
                                    <?= $meta['label'] ?>
                                </span>
                            </div>
                        </div>

                        <div class="timeline-card-body">
                            <p class="timeline-description">
                                <strong><?= htmlspecialchars($log['action']) ?></strong>
                                <?php if (!empty($entity) && $entity !== '—'): ?>
                                    trên <span class="entity-chip"><?= $entity ?></span>
                                <?php endif; ?>
                            </p>
                            <div class="timeline-footer">
                                <span class="timeline-ip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="10" height="10">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="2" y1="12" x2="22" y2="12"/>
                                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                    </svg>
                                    <?= $ip ?>
                                </span>
                                <span class="timeline-relative"><?= timeAgo($log['created_at']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php
                $baseUrl = '/admin/activity-log?page=';
                $qsParts = [];
                if ($filter_user_id)   $qsParts[] = 'user_id=' . urlencode($filter_user_id);
                if ($filter_action)    $qsParts[] = 'action=' . urlencode($filter_action);
                if ($filter_date_from) $qsParts[] = 'date_from=' . urlencode($filter_date_from);
                if ($filter_date_to)   $qsParts[] = 'date_to=' . urlencode($filter_date_to);
                $qs = $qsParts ? '&' . implode('&', $qsParts) : '';
                ?>
                <?php if ($current_page > 1): ?>
                    <a href="<?= $baseUrl . ($current_page - 1) . $qs ?>" class="page-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
                    </a>
                <?php else: ?>
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg></span>
                <?php endif; ?>

                <?php
                $start = max(1, $current_page - 2);
                $end   = min($total_pages, $current_page + 2);
                if ($start > 1): ?>
                    <a href="<?= $baseUrl . 1 . $qs ?>" class="page-btn">1</a>
                    <?php if ($start > 2): ?><span class="page-ellipsis">…</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i === $current_page): ?>
                        <span class="page-btn active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= $baseUrl . $i . $qs ?>" class="page-btn"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?><span class="page-ellipsis">…</span><?php endif; ?>
                    <a href="<?= $baseUrl . $total_pages . $qs ?>" class="page-btn"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="<?= $baseUrl . ($current_page + 1) . $qs ?>" class="page-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                <?php else: ?>
                    <span class="page-btn disabled"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
/* ── Page Header ─────────────────────────────────────────── */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
}
.page-heading {
    font-family: 'Lexend Deca', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 3px;
}
.page-sub { font-size: 13px; color: #94a3b8; }

/* ── Stats ───────────────────────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.stat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 1px 3px rgba(15,23,42,0.04);
}
.stat-icon {
    width: 38px;
    height: 38px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stat-value {
    font-family: 'Lexend Deca', sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}
.stat-label { font-size: 11px; color: #94a3b8; margin-top: 1px; }

/* ── Timeline ────────────────────────────────────────────── */
.timeline-container {
    padding: 8px 24px 8px 24px;
}

.timeline-date-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 0 12px;
    margin-left: 0;
}
.timeline-date-divider::before,
.timeline-date-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e2e8f0;
}
.timeline-date-divider span {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    white-space: nowrap;
}

.timeline-item {
    display: flex;
    gap: 14px;
    position: relative;
    padding-bottom: 16px;
}
/* Vertical connector line */
.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 17px;
    top: 38px;
    bottom: 0;
    width: 1px;
    background: linear-gradient(to bottom, #e2e8f0, #f1f5f9);
}

.timeline-icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 2px solid var(--item-bg);
    box-shadow: 0 0 0 4px var(--item-bg, #f8fafc);
    position: relative;
    z-index: 1;
    margin-top: 2px;
}

.timeline-card {
    flex: 1;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(15,23,42,0.04);
    transition: box-shadow 0.15s ease, border-color 0.15s ease;
}
.timeline-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(15,23,42,0.08);
}

.timeline-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    gap: 10px;
}

.timeline-user {
    display: flex;
    align-items: center;
    gap: 9px;
}
.timeline-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 11px;
    flex-shrink: 0;
}
.timeline-name {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    display: block;
    line-height: 1.3;
}
.timeline-role {
    font-size: 10px;
    font-weight: 500;
    display: block;
    line-height: 1.3;
}

.timeline-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.timeline-time {
    font-family: 'Lexend Deca', monospace;
    font-size: 11px;
    color: #94a3b8;
}
.timeline-action-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 20px;
    white-space: nowrap;
}

.timeline-card-body {
    padding: 10px 14px;
}
.timeline-description {
    font-size: 13px;
    color: #334155;
    margin: 0 0 8px;
    line-height: 1.5;
}
.timeline-description strong {
    color: #0f172a;
    font-weight: 600;
}
.entity-chip {
    background: #f1f5f9;
    color: #475569;
    padding: 1px 7px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.timeline-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.timeline-ip {
    display: flex;
    align-items: center;
    gap: 4px;
    font-family: 'Lexend Deca', monospace;
    font-size: 10px;
    color: #94a3b8;
}
.timeline-relative {
    font-size: 11px;
    color: #94a3b8;
}

/* ── Pagination ──────────────────────────────────────────── */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 16px 24px;
    border-top: 1px solid #f1f5f9;
}
.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 6px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #475569;
    text-decoration: none;
    border: 1px solid #e2e8f0;
    background: #fff;
    transition: all 0.15s;
}
.page-btn:hover { background: #f8fafc; border-color: #cbd5e1; color: #0f172a; }
.page-btn.active { background: #4f46e5; border-color: #4f46e5; color: #fff; }
.page-btn.disabled { color: #cbd5e1; pointer-events: none; border-color: #f1f5f9; }
.page-ellipsis { font-size: 13px; color: #94a3b8; padding: 0 4px; }

/* ── Empty State ─────────────────────────────────────────── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: #94a3b8;
    text-align: center;
    gap: 12px;
}
.empty-state svg { opacity: 0.4; }
.empty-state p { font-size: 14px; margin: 0; }

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 768px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .page-header { flex-direction: column; }
    .timeline-container { padding: 8px 14px; }
    .timeline-item { gap: 10px; }
    .timeline-item::before { left: 12px; }
    .timeline-card-header { flex-direction: column; align-items: flex-start; gap: 6px; }
}
@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr; }
}
</style>
