<?php /* app/Views/admin/activity_logs.php */ ?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">

<?php
$roleLabels = [
    'admin'    => ['label' => 'Admin', 'color' => 'var(--rose)'],
    'lecturer' => ['label' => 'GV', 'color' => 'var(--sky)'],
    'student'  => ['label' => 'SV', 'color' => 'var(--emerald)'],
];

function getActionBadgeClass(string $action): string {
    $action = strtolower($action);
    if (strpos($action, 'login') !== false) return 'badge--sky';
    if (strpos($action, 'logout') !== false) return 'badge--gray';
    if (strpos($action, 'create') !== false || strpos($action, 'add') !== false || strpos($action, 'insert') !== false || strpos($action, 'enroll') !== false) return 'badge--emerald';
    if (strpos($action, 'update') !== false || strpos($action, 'edit') !== false || strpos($action, 'save') !== false || strpos($action, 'grade') !== false) return 'badge--sky';
    if (strpos($action, 'delete') !== false || strpos($action, 'remove') !== false) return 'badge--rose';
    if (strpos($action, 'activate') !== false) return 'badge--emerald';
    if (strpos($action, 'deactivate') !== false) return 'badge--amber';
    return 'badge--gray';
}

function buildQueryParams(array $overrides = []): string {
    $params = [];
    $params['page'] = $overrides['page'] ?? ($_GET['page'] ?? '1');
    if (!empty($_GET['role'])) $params['role'] = $_GET['role'];
    if (!empty($_GET['action'])) $params['action'] = $_GET['action'];
    if (!empty($_GET['from'])) $params['from'] = $_GET['from'];
    if (!empty($_GET['to'])) $params['to'] = $_GET['to'];
    if (!empty($overrides['role'])) {
        if ($overrides['role'] === '') unset($params['role']);
        else $params['role'] = $overrides['role'];
    }
    if (isset($overrides['action']) && $overrides['action'] === '') unset($params['action']);
    if (isset($overrides['from'])) $params['from'] = $overrides['from'];
    if (isset($overrides['to'])) $params['to'] = $overrides['to'];
    return '?' . http_build_query($params);
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-heading">Nhật ký hoạt động</h2>
        <p class="page-sub">Lịch sử thao tác trên hệ thống</p>
    </div>
</div>

<!-- Stats Bar -->
<div class="stats-bar">
    <div class="stat-item">
        <span class="stat-value"><?= number_format($stats_total) ?></span>
        <span class="stat-label">Tổng bản ghi</span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-item">
        <span class="stat-value stat-value--accent"><?= number_format($stats_today) ?></span>
        <span class="stat-label">Hôm nay</span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-item">
        <span class="stat-value stat-value--accent"><?= number_format($stats_7days) ?></span>
        <span class="stat-label">7 ngày qua</span>
    </div>
</div>

<!-- Logs Table Card -->
<div class="section-card">
    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-tabs">
            <a href="/admin/activity-logs<?= buildQueryParams(['role' => '']) ?>" class="filter-tab <?= !$filter_role ? 'active' : '' ?>">Tất cả</a>
            <a href="/admin/activity-logs<?= buildQueryParams(['role' => 'admin']) ?>" class="filter-tab <?= $filter_role === 'admin' ? 'active' : '' ?>">Admin</a>
            <a href="/admin/activity-logs<?= buildQueryParams(['role' => 'lecturer']) ?>" class="filter-tab <?= $filter_role === 'lecturer' ? 'active' : '' ?>">Giảng viên</a>
            <a href="/admin/activity-logs<?= buildQueryParams(['role' => 'student']) ?>" class="filter-tab <?= $filter_role === 'student' ? 'active' : '' ?>">Sinh viên</a>
        </div>

        <form class="filter-form" method="GET" action="/admin/activity-logs">
            <?php if ($filter_role): ?>
                <input type="hidden" name="role" value="<?= htmlspecialchars($filter_role) ?>">
            <?php endif; ?>

            <select name="action" class="filter-select" onchange="this.form.submit()">
                <option value="">— Tất cả thao tác —</option>
                <?php foreach ($action_types as $at): ?>
                    <option value="<?= htmlspecialchars($at['action']) ?>" <?= $filter_action === $at['action'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($at['action']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="date-range">
                <input type="date" name="from" class="filter-date" value="<?= htmlspecialchars($filter_from) ?>" title="Từ ngày">
                <span class="date-sep">—</span>
                <input type="date" name="to" class="filter-date" value="<?= htmlspecialchars($filter_to) ?>" title="Đến ngày">
            </div>

            <button type="submit" class="btn btn-ghost btn-sm">Áp dụng</button>
            <a href="/admin/activity-logs" class="btn btn-ghost btn-sm">Reset</a>
        </form>
    </div>

    <div class="table-wrapper">
        <table class="data-table" id="logsTable">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Người dùng</th>
                    <th>Vai trò</th>
                    <th>Hành động</th>
                    <th>Đối tượng</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                <p>Không có bản ghi nào phù hợp.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                            $roleInfo = $roleLabels[$log['user_role']] ?? ['label' => $log['user_role'], 'color' => 'var(--text-muted)'];
                            $badgeClass = getActionBadgeClass($log['action']);
                            $timestamp = strtotime($log['created_at']);
                        ?>
                        <tr>
                            <td class="timestamp-cell">
                                <span class="timestamp-date"><?= date('d/m/Y', $timestamp) ?></span>
                                <span class="timestamp-time"><?= date('H:i:s', $timestamp) ?></span>
                            </td>
                            <td>
                                <div class="user-info">
                                    <span class="user-name"><?= htmlspecialchars($log['full_name']) ?></span>
                                    <span class="user-username">@<?= htmlspecialchars($log['username']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge" style="color: <?= $roleInfo['color'] ?>; background: <?= str_replace(')', ', .15)', $roleInfo['color']) ?>">
                                    <?= $roleInfo['label'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="action-badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td class="text-muted">
                                <?= $log['entity'] ? htmlspecialchars($log['entity']) : '—' ?>
                            </td>
                            <td class="ip-cell"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($logs)): ?>
        <!-- Pagination -->
        <div class="pagination-bar">
            <div class="pagination-info">
                Trang <?= $pagination['page'] ?> / <?= $pagination['pages'] ?>
                (<?= number_format($pagination['total']) ?> bản ghi)
            </div>
            <div class="pagination-controls">
                <?php if ($pagination['has_prev']): ?>
                    <a href="/admin/activity-logs<?= buildQueryParams(['page' => $pagination['page'] - 1]) ?>" class="btn btn-ghost btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
                        Trước
                    </a>
                <?php else: ?>
                    <button class="btn btn-ghost btn-sm" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
                        Trước
                    </button>
                <?php endif; ?>

                <?php if ($pagination['has_next']): ?>
                    <a href="/admin/activity-logs<?= buildQueryParams(['page' => $pagination['page'] + 1]) ?>" class="btn btn-ghost btn-sm">
                        Sau
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                <?php else: ?>
                    <button class="btn btn-ghost btn-sm" disabled>
                        Sau
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
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

/* Stats Bar */
.stats-bar {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 16px 20px;
    background: var(--surface-1);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-md);
    margin-bottom: 20px;
}
.stat-item { text-align: center; }
.stat-value {
    display: block;
    font-family: 'Lexend Deca', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
}
.stat-value--accent { color: var(--accent); }
.stat-label {
    display: block;
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.stat-divider {
    width: 1px;
    height: 40px;
    background: var(--surface-2);
}

/* Filter Bar */
.filter-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.filter-tabs {
    display: flex;
    gap: 4px;
    background: var(--surface-0);
    border: 1px solid var(--surface-2);
    border-radius: 8px;
    padding: 3px;
}
.filter-tab {
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
    text-decoration: none;
    transition: all var(--transition);
    white-space: nowrap;
}
.filter-tab:hover { color: var(--text-primary); }
.filter-tab.active { background: var(--accent); color: white; }

.filter-form {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.filter-select {
    background: var(--surface-0);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    padding: 8px 12px;
    font-size: 12px;
    outline: none;
    cursor: pointer;
    transition: border-color var(--transition);
    min-width: 160px;
}
.filter-select:focus { border-color: var(--accent); }

.date-range {
    display: flex;
    align-items: center;
    gap: 6px;
}
.filter-date {
    background: var(--surface-0);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    padding: 8px 10px;
    font-size: 12px;
    outline: none;
    transition: border-color var(--transition);
}
.filter-date:focus { border-color: var(--accent); }
.date-sep { color: var(--text-muted); font-size: 12px; }

/* Table */
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
    padding: 10px 14px;
    border-bottom: 1px solid rgba(51,65,85,.3);
    vertical-align: middle;
    font-size: 13px;
}
.data-table tbody tr:hover td { background: rgba(51,65,85,.2); }
.data-table tbody tr:last-child td { border-bottom: none; }

.timestamp-cell {
    font-family: 'Lexend Deca', monospace;
    font-size: 12px;
    white-space: nowrap;
}
.timestamp-date { color: var(--text-primary); display: block; }
.timestamp-time { color: var(--text-muted); font-size: 11px; display: block; }

.user-info { display: flex; flex-direction: column; gap: 1px; }
.user-name { font-weight: 500; color: var(--text-primary); font-size: 13px; }
.user-username { font-size: 11px; color: var(--text-muted); }

.role-badge {
    display: inline-flex;
    align-items: center;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 10px;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.action-badge {
    display: inline-flex;
    align-items: center;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
    white-space: nowrap;
}
.badge--emerald { background: rgba(16,185,129,.15); color: #6ee7b7; }
.badge--sky     { background: rgba(14,165,233,.15); color: #7dd3fc; }
.badge--amber   { background: rgba(245,158,11,.15); color: #fcd34d; }
.badge--rose    { background: rgba(244,63,94,.15);  color: #fda4af; }
.badge--gray    { background: rgba(100,116,139,.15); color: #94a3b8; }

.ip-cell {
    font-family: 'Lexend Deca', monospace;
    font-size: 11px;
    color: var(--text-muted);
    white-space: nowrap;
}

.text-muted { color: var(--text-muted); }

/* Pagination */
.pagination-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    border-top: 1px solid rgba(51,65,85,.4);
}
.pagination-info { font-size: 12px; color: var(--text-muted); }
.pagination-controls { display: flex; gap: 8px; }

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 18px;
    border-radius: var(--radius-sm);
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all var(--transition);
}
.btn-sm { font-size: 12px; padding: 6px 12px; }
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { background: var(--accent-hover); }
.btn-ghost { background: none; color: var(--text-secondary); border: 1px solid var(--surface-2); }
.btn-ghost:hover { border-color: var(--surface-3); color: var(--text-primary); }
.btn-ghost:disabled { opacity: .4; cursor: not-allowed; }

/* Empty State */
.empty-state {
    text-align: center;
    padding: 48px 20px;
    color: var(--text-muted);
}
.empty-state svg { margin-bottom: 12px; opacity: .5; }
.empty-state p { font-size: 14px; }

@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .filter-bar { flex-direction: column; align-items: stretch; }
    .filter-tabs { flex-wrap: wrap; }
    .filter-form { width: 100%; }
    .stats-bar { flex-direction: column; gap: 12px; }
    .stat-divider { width: 100%; height: 1px; }
    .pagination-bar { flex-direction: column; gap: 10px; }
    .data-table th:nth-child(5),
    .data-table td:nth-child(5),
    .data-table th:nth-child(6),
    .data-table td:nth-child(6) { display: none; }
}
</style>
