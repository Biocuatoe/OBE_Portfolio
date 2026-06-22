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
<div class="filter-bar">
    <form method="GET" action="/admin/activity-logs" id="filterForm" style="display:contents">
        <div class="form-group">
            <label class="form-label" for="filter-user">Người dùng</label>
            <select name="user_id" id="filter-user" class="form-control">
                <option value="">— Tất cả người dùng —</option>
                <?php foreach ($users_list as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $filter_user_id == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['full_name']) ?> (<?= $u['role'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="filter-action">Hành động</label>
            <select name="action" id="filter-action" class="form-control">
                <option value="">— Tất cả hành động —</option>
                <?php foreach ($distinct_actions as $act): ?>
                    <option value="<?= htmlspecialchars($act) ?>" <?= $filter_action === $act ? 'selected' : '' ?>>
                        <?= htmlspecialchars($act) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="filter-date-from">Từ ngày</label>
            <input type="date" name="date_from" id="filter-date-from" class="form-control" value="<?= htmlspecialchars($filter_date_from) ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="filter-date-to">Đến ngày</label>
            <input type="date" name="date_to" id="filter-date-to" class="form-control" value="<?= htmlspecialchars($filter_date_to) ?>">
        </div>

        <div style="display:flex;gap:8px;align-items:flex-end;padding-bottom:2px">
            <button type="submit" class="btn btn-primary btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Lọc
            </button>
            <a href="/admin/activity-logs" class="btn btn-secondary btn-sm">Xoá lọc</a>
        </div>
    </form>
</div>

<!-- logs Table Card -->
<div class="card">
    <div class="table-info-bar">
        <?php
        $from = (($current_page - 1) * 30) + 1;
        $to   = min($current_page * 30, count($logs) > 0 ? (($current_page - 1) * 30) + count($logs) : 0);
        $total = $total_pages * 30;
        ?>
        Hiển thị <?= $from ?>-<?= $to ?> trên <?= $total ?> kết quả
    </div>

    <div class="table-wrap">
        <table class="data-table striped" id="logsTable">
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
                        if (strpos($a, 'login') !== false) return 'badge badge-accent';
                        if (strpos($a, 'logout') !== false) return 'badge badge-gray';
                        if (preg_match('/\b(create|add|insert|enroll)\b/', $a)) return 'badge badge-emerald';
                        if (preg_match('/\b(update|edit|save|grade)\b/', $a)) return 'badge badge-sky';
                        if (preg_match('/\b(delete|remove)\b/', $a)) return 'badge badge-rose';
                        if (preg_match('/\b(activate|deactivate)\b/', $a)) return 'badge badge-amber';
                        return 'badge badge-gray';
                    }

                    function roleBadgeClass(string $role): string {
                        return match($role) {
                            'admin'    => 'badge badge-accent',
                            'lecturer' => 'badge badge-sky',
                            'student'  => 'badge badge-emerald',
                            default    => 'badge badge-gray',
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
                            <td class="text-mono text-sm" style="white-space:nowrap">
                                <?= formatLogTime($log['created_at']) ?>
                            </td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar user-avatar--<?= $log['role'] ?>"><?= $initial ?></div>
                                    <div>
                                        <div class="cell-primary"><?= htmlspecialchars($log['full_name']) ?></div>
                                        <div>
                                            <span class="<?= roleBadgeClass($log['role']) ?>"><?= roleLabel($log['role']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="<?= $colorClass ?>">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="cell-sub">
                                    <?= $log['entity'] ? htmlspecialchars($log['entity']) : '—' ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-mono text-sm text-muted" style="white-space:nowrap"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></span>
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
            <span class="pagination-info">
                Hiển thị <?= $from ?>-<?= $to ?> trên <?= $total ?> kết quả
            </span>
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
                <a href="<?= $baseUrl . ($current_page - 1) . $qs ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
            <?php else: ?>
                <span class="disabled">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
            <?php endif; ?>

            <?php
            $start = max(1, $current_page - 2);
            $end   = min($total_pages, $current_page + 2);
            if ($start > 1): ?>
                <a href="<?= $baseUrl . 1 . $qs ?>">1</a>
                <?php if ($start > 2): ?><span class="disabled">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i === $current_page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $baseUrl . $i . $qs ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?><span class="disabled">…</span><?php endif; ?>
                <a href="<?= $baseUrl . $total_pages . $qs ?>"><?= $total_pages ?></a>
            <?php endif; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="<?= $baseUrl . ($current_page + 1) . $qs ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            <?php else: ?>
                <span class="disabled">
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
    color: #0f172a;
    margin-bottom: 2px;
}
.page-sub { font-size: 13px; color: #94a3b8; }

.table-info-bar {
    padding: 10px 14px;
    font-size: 12px;
    color: #94a3b8;
    border-bottom: 1px solid rgba(226,232,240,0.8);
}

/* User cell */
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
.user-avatar--admin    { background: rgba(79,70,229,0.08); color: #4f46e5; }
.user-avatar--lecturer { background: rgba(14,165,233,0.08); color: #0ea5e9; }
.user-avatar--student  { background: rgba(16,185,129,0.08); color: #10b981; }
.user-avatar--default  { background: #e2e8f0; color: #64748b; }

.cell-primary { font-weight: 500; font-size: 13px; color: #0f172a; }
.cell-sub { font-size: 11px; color: #94a3b8; }

.text-mono { font-family: 'Lexend Deca', monospace; }
.text-muted { color: #94a3b8; }
.text-sm { font-size: 12px; }

@media (max-width: 768px) {
    .filter-bar { flex-direction: column; }
    .filter-bar form { flex-direction: column; }
    .data-table th:nth-child(4),
    .data-table td:nth-child(4) { display: none; }
}
@media (max-width: 480px) {
    .data-table th:nth-child(5),
    .data-table td:nth-child(5) { display: none; }
}
</style>
