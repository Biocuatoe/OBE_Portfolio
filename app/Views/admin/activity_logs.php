<?php /* app/Views/admin/activity_logs.php */ ?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-heading">Nhật ký hoạt động</h2>
        <p class="page-sub">Audit trail — theo dõi mọi thay đổi trong hệ thống</p>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-card">
    <form method="GET" action="/admin/activity-logs" class="filter-form" id="filterForm">
        <div class="filter-row">
            <div class="filter-group">
                <label class="filter-label" for="filter-user">Người dùng</label>
                <select name="user_id" id="filter-user" class="filter-select">
                    <option value="">— Tất cả người dùng —</option>
                    <?php foreach ($users_list as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $filter_user_id == $u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['full_name']) ?> (<?= $u['role'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label" for="filter-action">Hành động</label>
                <select name="action" id="filter-action" class="filter-select">
                    <option value="">— Tất cả hành động —</option>
                    <?php foreach ($distinct_actions as $act): ?>
                        <option value="<?= htmlspecialchars($act) ?>" <?= $filter_action === $act ? 'selected' : '' ?>>
                            <?= htmlspecialchars($act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label" for="filter-date-from">Từ ngày</label>
                <input type="date" name="date_from" id="filter-date-from" class="filter-input" value="<?= htmlspecialchars($filter_date_from) ?>">
            </div>

            <div class="filter-group">
                <label class="filter-label" for="filter-date-to">Đến ngày</label>
                <input type="date" name="date_to" id="filter-date-to" class="filter-input" value="<?= htmlspecialchars($filter_date_to) ?>">
            </div>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Lọc
            </button>
            <a href="/admin/activity-logs" class="btn btn-ghost btn-sm">Xoá lọc</a>
        </div>
    </form>
</div>

<!-- Logs Table Card -->
<div class="section-card">
    <div class="table-info-bar">
        <?php
        $from = (($current_page - 1) * 30) + 1;
        $to   = min($current_page * 30, count($logs) > 0 ? (($current_page - 1) * 30) + count($logs) : 0);
        $total = $total_pages * 30;
        ?>
        Hiển thị <?= $from ?>-<?= $to ?> trên <?= $total ?> kết quả
    </div>

    <div class="table-wrapper">
        <table class="data-table" id="logsTable">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Người dùng</th>
                    <th>Hành động</th>
                    <th>Đối tượng</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                <p>Không có nhật ký nào phù hợp.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php
                    function formatLogTime(string $createdAt): string {
                        $dt = new DateTime($createdAt);
                        $now = new DateTime('today');
                        if ($dt->format('Y-m-d') === $now->format('Y-m-d')) {
                            return 'Hôm nay ' . $dt->format('H:i');
                        }
                        return $dt->format('d/m/Y H:i');
                    }

                    function actionColor(string $action): string {
                        $a = strtolower($action);
                        if (strpos($a, 'login') !== false) return 'action--indigo';
                        if (strpos($a, 'logout') !== false) return 'action--gray';
                        if (preg_match('/\b(create|add|insert|enroll)\b/', $a)) return 'action--green';
                        if (preg_match('/\b(update|edit|save|grade)\b/', $a)) return 'action--sky';
                        if (preg_match('/\b(delete|remove)\b/', $a)) return 'action--rose';
                        if (preg_match('/\b(activate|deactivate)\b/', $a)) return 'action--amber';
                        return 'action--gray';
                    }

                    function roleBadgeClass(string $role): string {
                        return match($role) {
                            'admin'    => 'badge--indigo',
                            'lecturer' => 'badge--sky',
                            'student'  => 'badge--emerald',
                            default    => 'badge--gray',
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
                    ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                            $initial = mb_strtoupper(mb_substr($log['full_name'] ?? $log['username'], 0, 1));
                            $colorClass = actionColor($log['action']);
                        ?>
                        <tr class="log-row">
                            <td class="text-mono text-sm">
                                <?= formatLogTime($log['created_at']) ?>
                            </td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar user-avatar--<?= $log['role'] ?>"><?= $initial ?></div>
                                    <div>
                                        <div class="cell-primary"><?= htmlspecialchars($log['full_name']) ?></div>
                                        <div>
                                            <span class="role-badge <?= roleBadgeClass($log['role']) ?>"><?= roleLabel($log['role']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="action-tag <?= $colorClass ?>">
                                    <span class="action-dot"></span>
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="cell-sub">
                                    <?= $log['entity'] ? htmlspecialchars($log['entity']) : '—' ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-mono text-sm text-muted"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $baseUrl = '/admin/activity-logs?page=';
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
                <span class="page-btn page-btn--disabled">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
            <?php endif; ?>

            <?php
            $start = max(1, $current_page - 2);
            $end   = min($total_pages, $current_page + 2);
            if ($start > 1): ?>
                <a href="<?= $baseUrl . 1 . $qs ?>" class="page-num">1</a>
                <?php if ($start > 2): ?><span class="page-ellipsis">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i === $current_page): ?>
                    <span class="page-num page-num--active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $baseUrl . $i . $qs ?>" class="page-num"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?><span class="page-ellipsis">…</span><?php endif; ?>
                <a href="<?= $baseUrl . $total_pages . $qs ?>" class="page-num"><?= $total_pages ?></a>
            <?php endif; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="<?= $baseUrl . ($current_page + 1) . $qs ?>" class="page-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            <?php else: ?>
                <span class="page-btn page-btn--disabled">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* ── Page Header ──────────────────────────────────────────────────── */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
}
.page-heading {
    font-family: 'Lexend Deca', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 2px;
}
.page-sub { font-size: 13px; color: var(--text-muted); }

/* ── Filter Card ─────────────────────────────────────────────────── */
.filter-card {
    background: var(--surface-1);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-md);
    padding: 16px;
    margin-bottom: 16px;
}
.filter-form {}
.filter-row {
    display: grid;
    grid-template-columns: 1fr 1fr 160px 160px;
    gap: 12px;
    margin-bottom: 12px;
}
.filter-actions { display: flex; gap: 8px; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 4px; }
.filter-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; color: var(--text-muted); }
.filter-select, .filter-input {
    background: var(--surface-0);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm);
    padding: 7px 10px;
    font-family: 'Lexend Deca', sans-serif;
    font-size: 13px;
    color: var(--text-primary);
    transition: border-color var(--transition);
}
.filter-select:focus, .filter-input:focus { outline: none; border-color: var(--accent); }
.filter-select option { background: var(--surface-1); }
.filter-input[type="date"] { cursor: pointer; }

/* ── Section Card ───────────────────────────────────────────────── */
.section-card {
    background: var(--surface-1);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-md);
    overflow: hidden;
}

/* ── Table ───────────────────────────────────────────────────────── */
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
    text-align: left;
    padding: 10px 14px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--text-muted);
    border-bottom: 1px solid var(--surface-2);
    white-space: nowrap;
}
.data-table td {
    padding: 11px 14px;
    border-bottom: 1px solid rgba(51,65,85,.4);
    vertical-align: middle;
}
.data-table tbody tr:hover td { background: rgba(51,65,85,.2); }
.data-table tbody tr:last-child td { border-bottom: none; }

/* ── User cell ───────────────────────────────────────────────────── */
.user-cell { display: flex; align-items: center; gap: 10px; }
.user-avatar {
    width: 30px; height: 30px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 12px;
    flex-shrink: 0;
}
.user-avatar--admin    { background: var(--accent-soft); color: var(--accent); }
.user-avatar--lecturer { background: rgba(14,165,233,.12); color: var(--sky); }
.user-avatar--student  { background: rgba(16,185,129,.12); color: var(--emerald); }
.user-avatar--default  { background: var(--surface-2); color: var(--text-secondary); }

.cell-primary { font-weight: 500; font-size: 13px; color: var(--text-primary); }
.cell-sub { font-size: 11px; color: var(--text-muted); }

/* ── Role badges ────────────────────────────────────────────────── */
.role-badge {
    display: inline-flex;
    align-items: center;
    padding: 1px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
    font-family: 'Lexend Deca', sans-serif;
    margin-top: 2px;
}
.badge--indigo  { background: var(--accent-soft); color: var(--accent); }
.badge--sky     { background: rgba(14,165,233,.12); color: var(--sky); }
.badge--emerald { background: rgba(16,185,129,.12); color: var(--emerald); }
.badge--gray    { background: var(--surface-2); color: var(--text-muted); }

/* ── Action tags ────────────────────────────────────────────────── */
.action-tag {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    font-family: 'Lexend Deca', sans-serif;
    white-space: nowrap;
}
.action-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}

.action--indigo { background: var(--accent-soft); color: var(--accent); }
.action--indigo .action-dot { background: var(--accent); }

.action--gray { background: var(--surface-2); color: var(--text-secondary); }
.action--gray .action-dot { background: var(--text-muted); }

.action--green { background: rgba(16,185,129,.12); color: var(--emerald); }
.action--green .action-dot { background: var(--emerald); }

.action--sky { background: rgba(14,165,233,.12); color: var(--sky); }
.action--sky .action-dot { background: var(--sky); }

.action--rose { background: rgba(244,63,94,.12); color: var(--rose); }
.action--rose .action-dot { background: var(--rose); }

.action--amber { background: rgba(245,158,11,.12); color: var(--amber); }
.action--amber .action-dot { background: var(--amber); }

/* ── Utils ───────────────────────────────────────────────────────── */
.text-mono { font-family: 'Lexend Deca', monospace; }
.text-muted { color: var(--text-muted); }
.text-sm { font-size: 12px; }

/* ── Pagination ──────────────────────────────────────────────────── */
.table-info-bar {
    padding: 10px 14px;
    font-size: 12px;
    color: var(--text-muted);
    border-bottom: 1px solid rgba(51,65,85,.4);
}
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 16px;
}
.page-btn {
    width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    border-radius: var(--radius-sm);
    background: none;
    border: 1px solid var(--surface-2);
    color: var(--text-muted);
    cursor: pointer;
    text-decoration: none;
    transition: all var(--transition);
}
.page-btn:hover { border-color: var(--accent); color: var(--accent); }
.page-btn--disabled { opacity: .4; pointer-events: none; }
.page-num {
    min-width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-family: 'Lexend Deca', sans-serif;
    color: var(--text-secondary);
    text-decoration: none;
    transition: all var(--transition);
    padding: 0 6px;
}
.page-num:hover { color: var(--accent); background: var(--accent-soft); }
.page-num--active { background: var(--accent); color: white; font-weight: 600; }
.page-ellipsis { color: var(--text-muted); padding: 0 4px; }

/* ── Empty state ─────────────────────────────────────────────────── */
.empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 48px 20px; gap: 12px;
    color: var(--text-muted);
}
.empty-state svg { opacity: .4; }
.empty-state p { font-size: 14px; }

/* ── Buttons ─────────────────────────────────────────────────────── */
.btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 9px 18px;
    border-radius: var(--radius-sm);
    font-family: 'Lexend Deca', sans-serif; font-weight: 600; font-size: 13px;
    cursor: pointer; border: none; text-decoration: none;
    transition: all var(--transition);
}
.btn-sm { padding: 7px 14px; font-size: 12px; }
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { background: var(--accent-hover); }
.btn-ghost { background: none; color: var(--text-secondary); border: 1px solid var(--surface-2); }
.btn-ghost:hover { border-color: var(--surface-3); color: var(--text-primary); }

@media (max-width: 768px) {
    .filter-row { grid-template-columns: 1fr 1fr; }
    .data-table th:nth-child(4),
    .data-table td:nth-child(4) { display: none; }
}
@media (max-width: 480px) {
    .filter-row { grid-template-columns: 1fr; }
    .data-table th:nth-child(5),
    .data-table td:nth-child(5) { display: none; }
}
</style>
