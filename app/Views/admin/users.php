<?php /* app/Views/admin/users.php */ ?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-heading">Quản lý người dùng</h2>
        <p class="page-sub">Quản lý tài khoản, phân quyền và trạng thái người dùng</p>
    </div>
    <button class="btn btn-primary" id="btnCreateUser">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Thêm người dùng
    </button>
</div>

<!-- Role Filter Pills -->
<div class="filter-bar">
    <div class="filter-pills">
        <?php
        $roles = [
            ''         => ['label' => 'Tất cả', 'key' => 'all'],
            'admin'    => ['label' => 'Admin', 'key' => 'admin'],
            'lecturer' => ['label' => 'Giảng viên', 'key' => 'lecturer'],
            'student'  => ['label' => 'Sinh viên', 'key' => 'student'],
        ];
        foreach ($roles as $rKey => $rInfo):
            $isActive = $filter_role === $rKey;
            $count    = isset($role_counts[$rKey]) ? $role_counts[$rKey] : 0;
            $href     = $rKey
                ? '/admin/users?role=' . urlencode($rKey) . ($search_query ? '&search=' . urlencode($search_query) : '')
                : '/admin/users' . ($search_query ? '?search=' . urlencode($search_query) : '');
        ?>
            <a href="<?= $href ?>" class="filter-pill <?= $isActive ? 'filter-pill--active' : '' ?>">
                <?= htmlspecialchars($rInfo['label']) ?>
                <span class="filter-pill-count"><?= $count ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <form method="GET" action="/admin/users" class="search-form" id="searchForm">
        <?php if ($filter_role): ?>
            <input type="hidden" name="role" value="<?= htmlspecialchars($filter_role) ?>">
        <?php endif; ?>
        <div class="search-wrapper">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" class="search-input" placeholder="Tìm kiếm người dùng..." value="<?= htmlspecialchars($search_query) ?>" id="searchInput">
            <?php if ($search_query): ?>
                <button type="button" class="search-clear" id="searchClear" title="Xóa tìm kiếm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Users Table Card -->
<div class="section-card">
    <div class="table-info-bar">
        <?php
        $total = $total_pages * 20;
        $from  = (($current_page - 1) * 20) + 1;
        $to    = min($current_page * 20, count($users) > 0 ? (($current_page - 1) * 20) + count($users) : 0);
        ?>
        Hiển thị <?= $from ?>-<?= $to ?> trên <?= $total ?> kết quả
    </div>

    <div class="table-wrapper">
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>Người dùng</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <p>Không tìm thấy người dùng nào.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <?php
                            $roleClass = match($u['role']) {
                                'admin'    => 'badge--indigo',
                                'lecturer' => 'badge--sky',
                                'student'  => 'badge--emerald',
                                default    => 'badge--gray',
                            };
                            $roleLabel = match($u['role']) {
                                'admin'    => 'Admin',
                                'lecturer' => 'Giảng viên',
                                'student'  => 'Sinh viên',
                                default    => ucfirst($u['role']),
                            };
                            $initial   = mb_strtoupper(mb_substr($u['full_name'] ?? $u['username'], 0, 1));
                            $isActive  = (bool)$u['is_active'];
                        ?>
                        <tr class="user-row"
                            data-username="<?= htmlspecialchars($u['username']) ?>"
                            data-email="<?= htmlspecialchars($u['email']) ?>"
                            data-fullname="<?= htmlspecialchars($u['full_name']) ?>"
                            data-role="<?= htmlspecialchars($u['role']) ?>"
                            data-active="<?= $u['is_active'] ?>"
                            data-id="<?= $u['id'] ?>">

                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar user-avatar--<?= $u['role'] ?>"><?= $initial ?></div>
                                    <div>
                                        <div class="cell-primary"><?= htmlspecialchars($u['full_name']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="mono-text"><?= htmlspecialchars($u['username']) ?></span>
                            </td>
                            <td>
                                <span class="cell-sub"><?= htmlspecialchars($u['email']) ?></span>
                            </td>
                            <td>
                                <span class="role-badge <?= $roleClass ?>"><?= $roleLabel ?></span>
                            </td>
                            <td>
                                <?php if ($isActive): ?>
                                    <span class="status-pill status-pill--active">Hoạt động</span>
                                <?php else: ?>
                                    <span class="status-pill status-pill--inactive">Bị khoá</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-muted text-sm">
                                <?= date('d/m/Y', strtotime($u['created_at'] ?? 'now')) ?>
                            </td>
                            <td class="text-right">
                                <div class="row-actions">
                                    <button class="action-btn action-btn--edit" title="Sửa" data-id="<?= $u['id'] ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <?php if ($isActive): ?>
                                        <button class="action-btn action-btn--warning" title="Khoá tài khoản" data-id="<?= $u['id'] ?>" data-fullname="<?= htmlspecialchars($u['full_name']) ?>" data-action="deactivate">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        </button>
                                    <?php else: ?>
                                        <button class="action-btn action-btn--success" title="Kích hoạt tài khoản" data-id="<?= $u['id'] ?>" data-fullname="<?= htmlspecialchars($u['full_name']) ?>" data-action="activate">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
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
            $baseUrl = '/admin/users?page=';
            $qsParts = [];
            if ($filter_role) $qsParts[] = 'role=' . urlencode($filter_role);
            if ($search_query) $qsParts[] = 'search=' . urlencode($search_query);
            $qs       = $qsParts ? '&' . implode('&', $qsParts) : '';
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

<!-- ── Create / Edit User Modal ─────────────────────────────────── -->
<div class="modal-overlay" id="userModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Thêm người dùng</h3>
            <button class="modal-close" id="modalClose" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form id="userForm" novalidate>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="user-username">Username <span class="required">*</span></label>
                        <input type="text" id="user-username" name="username" class="form-input" placeholder="VD: nguyenvana" maxlength="50" autocomplete="username">
                        <span class="field-error" id="err-username"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="user-email">Email <span class="required">*</span></label>
                        <input type="email" id="user-email" name="email" class="form-input" placeholder="VD: nguyenvana@tdtu.edu.vn" autocomplete="email">
                        <span class="field-error" id="err-email"></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="user-fullname">Họ tên <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" id="user-fullname" name="full_name" class="form-input form-input--icon" placeholder="VD: Nguyễn Văn A" maxlength="100">
                        </div>
                        <span class="field-error" id="err-full_name"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="user-role">Vai trò <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <select id="user-role" name="role" class="form-input form-input--icon">
                                <option value="student">Sinh viên</option>
                                <option value="lecturer">Giảng viên</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <span class="field-error" id="err-role"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="user-password">Mật khẩu <span class="required" id="pwRequired">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="user-password" name="password" class="form-input form-input--icon" placeholder="Ít nhất 8 ký tự" autocomplete="<?= $editMode ?? false ? 'new-password' : 'new-password' ?>">
                    </div>
                    <span class="field-error" id="err-password"></span>
                    <span class="field-hint" id="pwHint" style="display:none">Để trống nếu không đổi mật khẩu</span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" id="modalCancel">Hủy</button>
                <button type="submit" class="btn btn-primary" id="modalSubmit">
                    <span class="btn-label">Tạo mới</span>
                    <span class="btn-spinner" hidden>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="spin"><circle cx="12" cy="12" r="10" stroke-dasharray="30 70"/></svg>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Toggle Confirm Modal ─────────────────────────────────────── -->
<div class="modal-overlay" id="toggleModal" role="dialog" aria-modal="true">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h3 class="modal-title" id="toggleModalTitle">Khoá tài khoản</h3>
            <button class="modal-close" id="toggleModalClose" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon" id="toggleIcon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <p class="confirm-text" id="toggleConfirmText">Khoá tài khoản của <strong id="toggleUserName"></strong>?</p>
            <p class="confirm-sub">Người dùng sẽ không thể đăng nhập khi tài khoản bị khoá.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" id="toggleCancel">Hủy</button>
            <button class="btn btn-primary" id="toggleConfirm">
                <span class="btn-label" id="toggleBtnLabel">Khoá</span>
                <span class="btn-spinner" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="spin"><circle cx="12" cy="12" r="10" stroke-dasharray="30 70"/></svg>
                </span>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── API helper ────────────────────────────────────────────────────
    function api(url, method, body) {
        return fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ ...body, _token: CSRF }),
        }).then(r => r.json()).then(d => { if (!r.ok) throw d; return d; });
    }

    function setFieldError(id, msg) {
        const el = document.getElementById('err-' + id);
        if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
        const input = document.getElementById('user-' + id);
        if (input) input.classList.toggle('input--error', !!msg);
    }

    function clearErrors() {
        ['username','email','full_name','role','password'].forEach(id => setFieldError(id, ''));
    }

    function setLoading(btn, on) {
        if (!btn) return;
        btn.disabled = on;
        const label = btn.querySelector('.btn-label');
        if (label) label.textContent = on ? 'Đang xử lý...' : (editId ? 'Lưu thay đổi' : 'Tạo mới');
        const spinner = btn.querySelector('.btn-spinner');
        if (spinner) spinner.hidden = !on;
    }

    // ── User Modal ──────────────────────────────────────────────────
    const modal      = document.getElementById('userModal');
    const form       = document.getElementById('userForm');
    const modalTitle = document.getElementById('modalTitle');
    const submitBtn  = document.getElementById('modalSubmit');
    let editId = null;

    function openModal(id, row) {
        editId = id;
        const isEdit = !!id;
        modalTitle.textContent = isEdit ? 'Sửa người dùng' : 'Thêm người dùng';
        submitBtn.querySelector('.btn-label').textContent = isEdit ? 'Lưu thay đổi' : 'Tạo mới';

        document.getElementById('pwRequired').style.display = isEdit ? 'none' : 'inline';
        document.getElementById('pwHint').style.display = isEdit ? 'block' : 'none';
        document.getElementById('user-password').placeholder = isEdit ? 'Để trống nếu không đổi' : 'Ít nhất 8 ký tự';

        if (isEdit && row) {
            document.getElementById('user-username').value  = row.dataset.username || '';
            document.getElementById('user-email').value       = row.dataset.email || '';
            document.getElementById('user-fullname').value   = row.dataset.fullname || '';
            document.getElementById('user-role').value       = row.dataset.role || 'student';
            document.getElementById('user-password').value   = '';
        } else {
            form.reset();
        }

        clearErrors();
        modal.classList.add('open');
        document.getElementById('user-username').focus();
    }

    function closeModal() {
        modal.classList.remove('open');
        editId = null;
        form.reset();
        clearErrors();
    }

    document.getElementById('btnCreateUser')?.addEventListener('click', () => openModal(null));

    document.getElementById('modalClose')?.addEventListener('click', closeModal);
    document.getElementById('modalCancel')?.addEventListener('click', closeModal);
    modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    document.querySelectorAll('.action-btn--edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.user-row');
            openModal(+btn.dataset.id, row);
        });
    });

    form?.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();
        setLoading(submitBtn, true);

        const payload = {
            username:  document.getElementById('user-username').value.trim(),
            email:     document.getElementById('user-email').value.trim(),
            full_name: document.getElementById('user-fullname').value.trim(),
            role:      document.getElementById('user-role').value,
            password:  document.getElementById('user-password').value,
        };

        // If edit mode and password is empty, omit it
        if (editId && payload.password === '') {
            delete payload.password;
        }

        try {
            const url  = editId ? `/admin/user/${editId}/update` : '/admin/user/store';
            const data = await api(url, 'POST', payload);

            if (data.error && data.fields) {
                Object.entries(data.fields).forEach(([k, v]) => setFieldError(k, v));
                if (data.error !== 'Validation failed') window.Toast?.error(data.error);
            } else if (data.error) {
                window.Toast?.error(data.error);
            } else {
                window.Toast?.success(editId ? 'Đã cập nhật người dùng.' : 'Đã tạo người dùng mới.');
                closeModal();
                window.location.reload();
            }
        } catch(err) {
            if (err.error && err.fields) {
                Object.entries(err.fields).forEach(([k, v]) => setFieldError(k, v));
            }
            window.Toast?.error(err.error || 'Đã xảy ra lỗi. Vui lòng thử lại.');
        } finally {
            setLoading(submitBtn, false);
        }
    });

    // ── Toggle Modal ────────────────────────────────────────────────
    const toggleModal = document.getElementById('toggleModal');
    let toggleId = null;
    let toggleAction = null;

    function openToggleModal(id, fullname, action) {
        toggleId     = id;
        toggleAction = action;
        const isActivate = action === 'activate';

        document.getElementById('toggleUserName').textContent = fullname;
        document.getElementById('toggleConfirmText').innerHTML =
            (isActivate ? 'Kích hoạt' : 'Khoá') + ' tài khoản của <strong>' + fullname + '</strong>?';
        document.getElementById('toggleBtnLabel').textContent = isActivate ? 'Kích hoạt' : 'Khoá';
        document.getElementById('toggleConfirm').className =
            'btn ' + (isActivate ? 'btn-success' : 'btn-warning');

        const iconEl = document.getElementById('toggleIcon');
        if (isActivate) {
            iconEl.style.background = 'rgba(16,185,129,.12)';
            iconEl.style.color = 'var(--emerald)';
        } else {
            iconEl.style.background = 'rgba(245,158,11,.12)';
            iconEl.style.color = 'var(--amber)';
        }

        document.getElementById('toggleConfirmText').nextElementSibling.textContent =
            isActivate
                ? 'Người dùng sẽ có thể đăng nhập lại.'
                : 'Người dùng sẽ không thể đăng nhập khi tài khoản bị khoá.';

        toggleModal.classList.add('open');
    }

    function closeToggleModal() {
        toggleModal.classList.remove('open');
        toggleId = null;
        toggleAction = null;
    }

    document.querySelectorAll('.action-btn--warning, .action-btn--success').forEach(btn => {
        btn.addEventListener('click', () => {
            openToggleModal(+btn.dataset.id, btn.dataset.fullname, btn.dataset.action);
        });
    });

    document.getElementById('toggleModalClose')?.addEventListener('click', closeToggleModal);
    document.getElementById('toggleCancel')?.addEventListener('click', closeToggleModal);
    toggleModal?.addEventListener('click', e => { if (e.target === toggleModal) closeToggleModal(); });

    document.getElementById('toggleConfirm')?.addEventListener('click', async function() {
        if (!toggleId) return;
        setLoading(this, true);
        try {
            const data = await api(`/admin/user/${toggleId}/toggle`, 'POST', {});
            window.Toast?.success(toggleAction === 'activate' ? 'Đã kích hoạt tài khoản.' : 'Đã khoá tài khoản.');
            closeToggleModal();
            window.location.reload();
        } catch(err) {
            window.Toast?.error(err.error || 'Thao tác thất bại.');
        } finally {
            setLoading(this, false);
        }
    });

    // ── Search ───────────────────────────────────────────────────────
    const searchInput = document.getElementById('searchInput');
    const searchClear  = document.getElementById('searchClear');

    let searchDebounce = null;
    searchInput?.addEventListener('input', function() {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            // Quick client-side filter for immediate feedback
            const query = this.value.toLowerCase().trim();
            document.querySelectorAll('.user-row').forEach(row => {
                const text = [
                    row.dataset.username,
                    row.dataset.email,
                    row.dataset.fullname,
                ].join(' ').toLowerCase();
                row.style.display = query === '' || text.includes(query) ? '' : 'none';
            });
        }, 400);
    });

    searchClear?.addEventListener('click', function() {
        if (searchInput) searchInput.value = '';
        document.querySelectorAll('.user-row').forEach(row => row.style.display = '');
        document.getElementById('searchForm').submit();
    });

    // ── Keyboard shortcuts ───────────────────────────────────────────
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeModal(); closeToggleModal(); }
    });
})();
</script>

<style>
/* ── Shared Admin Styles ─────────────────────────────────────────── */
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
    padding: 12px 14px;
    border-bottom: 1px solid rgba(51,65,85,.4);
    vertical-align: middle;
}
.data-table tbody tr:hover td { background: rgba(51,65,85,.25); }
.data-table tbody tr:last-child td { border-bottom: none; }

/* User cell */
.user-cell { display: flex; align-items: center; gap: 10px; }
.user-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 13px;
    flex-shrink: 0;
}
.user-avatar--admin    { background: var(--accent-soft); color: var(--accent); }
.user-avatar--lecturer { background: rgba(14,165,233,.12); color: var(--sky); }
.user-avatar--student  { background: rgba(16,185,129,.12); color: var(--emerald); }
.user-avatar--default  { background: var(--surface-2); color: var(--text-secondary); }

.cell-primary { font-weight: 500; font-size: 13px; color: var(--text-primary); }
.cell-sub { font-size: 11px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; max-width: 200px; white-space: nowrap; }
.mono-text { font-family: 'Lexend Deca', monospace; font-size: 12px; color: var(--text-secondary); }

/* Role badges */
.role-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    font-family: 'Lexend Deca', sans-serif;
}
.badge--indigo  { background: var(--accent-soft); color: var(--accent); }
.badge--sky     { background: rgba(14,165,233,.12); color: var(--sky); }
.badge--emerald { background: rgba(16,185,129,.12); color: var(--emerald); }
.badge--gray    { background: var(--surface-2); color: var(--text-muted); }

/* Status pills */
.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    font-family: 'Lexend Deca', sans-serif;
}
.status-pill--active   { background: rgba(16,185,129,.15); color: var(--emerald); }
.status-pill--inactive { background: rgba(100,116,139,.15); color: var(--text-muted); }

/* Row actions */
.row-actions { display: flex; gap: 4px; justify-content: flex-end; }
.action-btn {
    width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    background: none;
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    cursor: pointer;
    transition: all var(--transition);
    text-decoration: none;
}
.action-btn svg { width: 14px; height: 14px; }
.action-btn--edit:hover    { border-color: var(--accent); color: var(--accent); background: var(--accent-soft); }
.action-btn--warning:hover { border-color: var(--amber); color: var(--amber); background: rgba(245,158,11,.1); }
.action-btn--success:hover { border-color: var(--emerald); color: var(--emerald); background: rgba(16,185,129,.1); }

.text-center { text-align: center; }
.text-right  { text-align: right; }
.text-muted  { color: var(--text-muted); }
.text-sm     { font-size: 12px; }

/* ── Filter bar ──────────────────────────────────────────────────── */
.filter-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.filter-pills { display: flex; gap: 6px; flex-wrap: wrap; }
.filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    font-family: 'Lexend Deca', sans-serif;
    color: var(--text-secondary);
    background: var(--surface-1);
    border: 1px solid var(--surface-2);
    text-decoration: none;
    transition: all var(--transition);
}
.filter-pill:hover { border-color: var(--surface-3); color: var(--text-primary); }
.filter-pill--active { border-color: var(--accent); color: var(--accent); background: var(--accent-soft); }
.filter-pill-count {
    font-size: 10px;
    background: var(--surface-2);
    color: var(--text-muted);
    padding: 1px 6px;
    border-radius: 10px;
}
.filter-pill--active .filter-pill-count { background: var(--accent-soft); color: var(--accent); }

/* Search */
.search-form { display: flex; }
.search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.search-icon {
    position: absolute;
    left: 10px;
    width: 14px; height: 14px;
    color: var(--text-muted);
    pointer-events: none;
}
.search-input {
    background: var(--surface-1);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm);
    padding: 7px 32px 7px 32px;
    font-family: 'Lexend Deca', sans-serif;
    font-size: 13px;
    color: var(--text-primary);
    width: 240px;
    transition: border-color var(--transition);
}
.search-input::placeholder { color: var(--text-muted); }
.search-input:focus { outline: none; border-color: var(--accent); }
.search-clear {
    position: absolute; right: 8px;
    background: none; border: none;
    color: var(--text-muted); cursor: pointer;
    padding: 2px;
    display: flex; align-items: center;
    transition: color var(--transition);
}
.search-clear svg { width: 12px; height: 12px; }
.search-clear:hover { color: var(--text-primary); }

/* Table info bar */
.table-info-bar {
    padding: 10px 14px;
    font-size: 12px;
    color: var(--text-muted);
    border-bottom: 1px solid rgba(51,65,85,.4);
}

/* Pagination */
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

/* Empty state */
.empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 48px 20px; gap: 12px;
    color: var(--text-muted);
}
.empty-state svg { opacity: .4; }
.empty-state p { font-size: 14px; }

/* Section card */
.section-card {
    background: var(--surface-1);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-md);
    overflow: hidden;
}

/* Modal */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.6);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none;
    transition: opacity .25s;
}
.modal-overlay.open { opacity: 1; pointer-events: auto; }

.modal {
    background: var(--surface-1);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-lg);
    width: 100%; max-width: 520px;
    box-shadow: var(--shadow-card);
    transform: translateY(12px) scale(.98);
    transition: transform .25s;
    overflow: hidden;
}
.modal-overlay.open .modal { transform: translateY(0) scale(1); }
.modal--sm { max-width: 400px; }
.modal--sm .modal-body { text-align: center; }

.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 20px 16px;
    border-bottom: 1px solid var(--surface-2);
}
.modal-title {
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 16px;
    color: var(--text-primary);
}
.modal-close {
    background: none; border: none;
    color: var(--text-muted); cursor: pointer;
    padding: 4px; border-radius: var(--radius-sm);
    transition: all var(--transition);
}
.modal-close svg { width: 18px; height: 18px; }
.modal-close:hover { background: var(--surface-2); color: var(--text-primary); }

.modal-body { padding: 20px; }
.modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 14px 20px;
    border-top: 1px solid var(--surface-2);
}

/* Buttons */
.btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 9px 18px;
    border-radius: var(--radius-sm);
    font-family: 'Lexend Deca', sans-serif; font-weight: 600; font-size: 13px;
    cursor: pointer; border: none; text-decoration: none;
    transition: all var(--transition);
}
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { background: var(--accent-hover); }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.btn-ghost { background: none; color: var(--text-secondary); border: 1px solid var(--surface-2); }
.btn-ghost:hover { border-color: var(--surface-3); color: var(--text-primary); }
.btn-warning { background: var(--amber); color: white; }
.btn-warning:hover { background: #d97706; }
.btn-warning:disabled { opacity: .6; cursor: not-allowed; }
.btn-success { background: var(--emerald); color: white; }
.btn-success:hover { background: #059669; }
.btn-success:disabled { opacity: .6; cursor: not-allowed; }

.btn-spinner svg { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Form */
.form-group { margin-bottom: 16px; }
.form-group:last-child { margin-bottom: 0; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-label {
    display: block;
    font-size: 13px; font-weight: 500;
    color: var(--text-secondary); margin-bottom: 6px;
}
.required { color: var(--rose); }
.form-input {
    width: 100%;
    background: var(--surface-0);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm);
    padding: 9px 12px;
    font-family: 'Lexend Deca', sans-serif;
    font-size: 13px;
    color: var(--text-primary);
    transition: border-color var(--transition);
    box-sizing: border-box;
}
.form-input::placeholder { color: var(--text-muted); }
.form-input:focus { outline: none; border-color: var(--accent); }
.form-input--icon { padding-left: 36px; }
select.form-input { cursor: pointer; appearance: none; }
select.form-input option { background: var(--surface-1); color: var(--text-primary); }

.input-wrapper { position: relative; }
.input-icon {
    position: absolute; left: 10px; top: 50%;
    transform: translateY(-50%);
    width: 15px; height: 15px;
    color: var(--text-muted);
    pointer-events: none;
}

.field-error {
    display: none;
    font-size: 11px; color: var(--rose);
    margin-top: 4px;
}
.field-hint { font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block; }
.input--error { border-color: var(--rose) !important; }

.confirm-icon {
    width: 52px; height: 52px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
}
.confirm-icon svg { width: 24px; height: 24px; }
.confirm-text { font-size: 15px; color: var(--text-primary); margin-bottom: 6px; }
.confirm-sub { font-size: 12px; color: var(--text-muted); }

@media (max-width: 640px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .filter-bar { flex-direction: column; align-items: stretch; }
    .filter-pills { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 4px; }
    .form-row { grid-template-columns: 1fr; }
    .search-input { width: 100%; }
    .data-table th:nth-child(2),
    .data-table td:nth-child(2),
    .data-table th:nth-child(3),
    .data-table td:nth-child(3) { display: none; }
}
</style>
