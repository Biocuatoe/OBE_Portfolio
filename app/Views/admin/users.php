<?php /* app/Views/admin/users.php */ ?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">

<?php
$roleLabels = [
    'admin'    => ['label' => 'Quản trị viên', 'color' => 'var(--rose)'],
    'lecturer' => ['label' => 'Giảng viên', 'color' => 'var(--sky)'],
    'student'  => ['label' => 'Sinh viên', 'color' => 'var(--emerald)'],
];
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h2 class="page-heading">Quản lý người dùng</h2>
        <p class="page-sub">Thêm, sửa, kích hoạt/vô hiệu hóa tài khoản</p>
    </div>
    <button class="btn btn-primary" id="btnCreateUser">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Thêm người dùng
    </button>
</div>

<!-- Users Table Card -->
<div class="section-card">
    <div class="section-header">
        <div class="section-title-group">
            <h3 class="section-title">Danh sách người dùng</h3>
            <span class="section-badge"><?= count($users) ?> người dùng</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-tabs" id="roleTabs">
            <a href="/admin/users" class="filter-tab <?= !$filter_role ? 'active' : '' ?>">Tất cả</a>
            <a href="/admin/users?role=admin" class="filter-tab <?= $filter_role === 'admin' ? 'active' : '' ?>">Quản trị</a>
            <a href="/admin/users?role=lecturer" class="filter-tab <?= $filter_role === 'lecturer' ? 'active' : '' ?>">Giảng viên</a>
            <a href="/admin/users?role=student" class="filter-tab <?= $filter_role === 'student' ? 'active' : '' ?>">Sinh viên</a>
        </div>
        <input type="text" id="userSearch" class="search-input" placeholder="Tìm kiếm username, email, họ tên...">
    </div>

    <div class="table-wrapper">
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Họ tên</th>
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
                                <p>Chưa có người dùng nào.</p>
                                <button class="btn btn-primary btn-sm mt-8" id="btnCreateUserEmpty">Thêm người dùng đầu tiên</button>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <?php
                            $roleInfo = $roleLabels[$u['role']] ?? ['label' => $u['role'], 'color' => 'var(--text-muted)'];
                            $isActive = (int)($u['is_active'] ?? 1) === 1;
                            $avatarLetter = strtoupper(mb_substr($u['username'], 0, 1));
                        ?>
                        <tr class="user-row"
                            data-id="<?= $u['id'] ?>"
                            data-username="<?= htmlspecialchars($u['username']) ?>"
                            data-email="<?= htmlspecialchars($u['email']) ?>"
                            data-full-name="<?= htmlspecialchars($u['full_name']) ?>"
                            data-role="<?= $u['role'] ?>"
                            data-is-active="<?= $u['is_active'] ?>">
                            <td>
                                <div class="user-cell">
                                    <div class="avatar avatar--<?= $u['role'] ?>"><?= $avatarLetter ?></div>
                                    <span class="username-text"><?= htmlspecialchars($u['username']) ?></span>
                                </div>
                            </td>
                            <td class="text-muted text-sm"><?= htmlspecialchars($u['email']) ?></td>
                            <td class="cell-primary"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td>
                                <span class="role-badge" style="color: <?= $roleInfo['color'] ?>; background: <?= str_replace(')', ', .2)', $roleInfo['color']) ?>">
                                    <?= $roleInfo['label'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="status-cell">
                                    <span class="status-dot <?= $isActive ? 'status-dot--active' : 'status-dot--inactive' ?>"></span>
                                    <span class="status-text <?= $isActive ? 'status-text--active' : 'status-text--inactive' ?>">
                                        <?= $isActive ? 'Đang hoạt động' : 'Bị khoá' ?>
                                    </span>
                                </div>
                            </td>
                            <td class="text-muted text-sm">
                                <?= date('d/m/Y', strtotime($u['created_at'] ?? 'now')) ?>
                            </td>
                            <td class="text-right">
                                <div class="row-actions">
                                    <button class="action-btn action-btn--edit" title="Sửa" data-id="<?= $u['id'] ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button class="action-btn <?= $isActive ? 'action-btn--warning' : 'action-btn--success' ?> toggle-btn"
                                            title="<?= $isActive ? 'Khoá tài khoản' : 'Kích hoạt tài khoản' ?>"
                                            data-id="<?= $u['id'] ?>"
                                            data-action="<?= $isActive ? 'deactivate' : 'activate' ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                                    </button>
                                    <?php if ($u['role'] === 'student'): ?>
                                        <a href="/student/<?= $u['id'] ?>/portfolio" class="action-btn action-btn--blue" title="Xem portfolio">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($users)): ?>
        <div class="pagination-info">
            Hiển thị <?= count($users) ?> bản ghi
        </div>
    <?php endif; ?>
</div>

<!-- ── Create / Edit User Modal ─────────────────────────────────────────── -->
<div class="modal-overlay" id="userModal" role="dialog" aria-modal="true">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Thêm người dùng</h3>
            <button class="modal-close" id="modalClose" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form id="userForm" novalidate>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="user-username">Username <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input type="text" id="user-username" name="username" class="form-input" placeholder="VD: nguyenvana" maxlength="50">
                    </div>
                    <span class="field-error" id="err-username"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="user-email">Email <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <input type="email" id="user-email" name="email" class="form-input" placeholder="VD: nguyenvana@example.edu.vn" maxlength="100">
                    </div>
                    <span class="field-error" id="err-email"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="user-full-name">Họ tên <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input type="text" id="user-full-name" name="full_name" class="form-input" placeholder="VD: Nguyễn Văn A" maxlength="100">
                    </div>
                    <span class="field-error" id="err-full_name"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="user-role">Vai trò <span class="required">*</span></label>
                    <select id="user-role" name="role" class="form-select">
                        <option value="student">Sinh viên</option>
                        <option value="lecturer">Giảng viên</option>
                        <option value="admin">Quản trị viên</option>
                    </select>
                    <span class="field-error" id="err-role"></span>
                </div>

                <div class="form-group" id="passwordGroup">
                    <label class="form-label" for="user-password">Mật khẩu <span class="required" id="passwordRequired">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="user-password" name="password" class="form-input" placeholder="Tối thiểu 8 ký tự" maxlength="100">
                    </div>
                    <span class="field-error" id="err-password"></span>
                </div>

                <div class="form-group" id="confirmPasswordGroup" style="display:none">
                    <label class="form-label" for="user-confirm-password">Xác nhận mật khẩu <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="user-confirm-password" name="confirm_password" class="form-input" placeholder="Nhập lại mật khẩu" maxlength="100">
                    </div>
                    <span class="field-error" id="err-confirm_password"></span>
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

<!-- ── Toggle Confirm Modal ───────────────────────────────────────────── -->
<div class="modal-overlay" id="toggleModal" role="dialog" aria-modal="true">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h3 class="modal-title" id="toggleModalTitle">Xác nhận</h3>
            <button class="modal-close" id="toggleModalClose" aria-label="Đóng">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="confirm-icon" id="toggleIcon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
            </div>
            <p class="confirm-text" id="toggleMessage">Khoá tài khoản này?</p>
            <p class="confirm-sub">Người dùng sẽ không thể đăng nhập cho đến khi được kích hoạt lại.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" id="toggleCancel">Hủy</button>
            <button class="btn btn-warning" id="toggleConfirm">
                <span class="btn-label">Xác nhận</span>
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

    // ── Helpers ──────────────────────────────────────────────────────────
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
        ['username', 'email', 'full_name', 'password', 'confirm_password'].forEach(id => setFieldError(id, ''));
    }

    function setLoading(btn, on) {
        btn.disabled = on;
        btn.querySelector('.btn-spinner').hidden = !on;
    }

    // ── Client-side search ─────────────────────────────────────────────────
    document.getElementById('userSearch')?.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.user-row').forEach(row => {
            const text = [
                row.dataset.username,
                row.dataset.email,
                row.dataset.fullName
            ].join(' ').toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    });

    // ── User Modal logic ───────────────────────────────────────────────────
    const userModal = document.getElementById('userModal');
    const userForm  = document.getElementById('userForm');
    const modalTitle = document.getElementById('modalTitle');
    const submitBtn  = document.getElementById('modalSubmit');
    const passwordGroup = document.getElementById('passwordGroup');
    const confirmPasswordGroup = document.getElementById('confirmPasswordGroup');
    const passwordRequired = document.getElementById('passwordRequired');
    let editId = null;

    function openUserModal(id, row) {
        editId = id;
        userForm.reset();
        clearErrors();

        if (id && row) {
            modalTitle.textContent = 'Sửa người dùng';
            submitBtn.querySelector('.btn-label').textContent = 'Lưu thay đổi';

            document.getElementById('user-username').value = row.dataset.username || '';
            document.getElementById('user-username').disabled = true;
            document.getElementById('user-email').value = row.dataset.email || '';
            document.getElementById('user-full-name').value = row.dataset.fullName || '';
            document.getElementById('user-role').value = row.dataset.role || 'student';

            // For editing: password is optional
            passwordGroup.querySelector('.form-label').innerHTML = 'Mật khẩu <span class="field-hint">(để trống nếu không đổi)</span>';
            passwordRequired.style.display = 'none';
            confirmPasswordGroup.style.display = 'none';
            document.getElementById('user-password').placeholder = 'Để trống nếu không đổi';
        } else {
            modalTitle.textContent = 'Thêm người dùng';
            submitBtn.querySelector('.btn-label').textContent = 'Tạo mới';

            document.getElementById('user-username').disabled = false;
            passwordGroup.querySelector('.form-label').innerHTML = 'Mật khẩu <span class="required">*</span>';
            passwordRequired.style.display = 'inline';
            confirmPasswordGroup.style.display = '';
            document.getElementById('user-password').placeholder = 'Tối thiểu 8 ký tự';
        }

        userModal.classList.add('open');
        if (!document.getElementById('user-username').disabled) {
            document.getElementById('user-username').focus();
        }
    }

    function closeUserModal() {
        userModal.classList.remove('open');
        editId = null;
    }

    document.getElementById('btnCreateUser')?.addEventListener('click', () => openUserModal(null));
    document.getElementById('btnCreateUserEmpty')?.addEventListener('click', () => openUserModal(null));
    document.getElementById('modalClose')?.addEventListener('click', closeUserModal);
    document.getElementById('modalCancel')?.addEventListener('click', closeUserModal);
    userModal?.addEventListener('click', e => { if (e.target === userModal) closeUserModal(); });

    document.querySelectorAll('.action-btn--edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.user-row');
            openUserModal(+btn.dataset.id, row);
        });
    });

    userForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();
        setLoading(submitBtn, true);

        const password = document.getElementById('user-password').value;
        const confirmPassword = document.getElementById('user-confirm-password').value;

        // Client-side validation
        const username = document.getElementById('user-username').value.trim();
        const email = document.getElementById('user-email').value.trim();
        const fullName = document.getElementById('user-full-name').value.trim();
        const role = document.getElementById('user-role').value;

        let hasError = false;

        if (!username) {
            setFieldError('username', 'Username không được để trống.');
            hasError = true;
        }
        if (!email) {
            setFieldError('email', 'Email không được để trống.');
            hasError = true;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            setFieldError('email', 'Email không hợp lệ.');
            hasError = true;
        }
        if (!fullName) {
            setFieldError('full_name', 'Họ tên không được để trống.');
            hasError = true;
        }

        if (!editId) {
            // Creating new user: password required
            if (!password) {
                setFieldError('password', 'Mật khẩu không được để trống.');
                hasError = true;
            } else if (password.length < 8) {
                setFieldError('password', 'Mật khẩu phải có ít nhất 8 ký tự.');
                hasError = true;
            }
            if (!confirmPassword) {
                setFieldError('confirm_password', 'Vui lòng xác nhận mật khẩu.');
                hasError = true;
            } else if (password !== confirmPassword) {
                setFieldError('confirm_password', 'Mật khẩu xác nhận không khớp.');
                hasError = true;
            }
        } else if (password && password.length < 8) {
            setFieldError('password', 'Mật khẩu phải có ít nhất 8 ký tự.');
            hasError = true;
        }

        if (hasError) {
            setLoading(submitBtn, false);
            return;
        }

        const payload = {
            username: username,
            email: email,
            full_name: fullName,
            role: role,
        };

        if (password) {
            payload.password = password;
        }

        try {
            const url = editId ? `/admin/user/${editId}/update` : '/admin/user/store';
            const data = await api(url, 'POST', payload);

            if (data.error && data.fields) {
                Object.entries(data.fields).forEach(([k, v]) => setFieldError(k, v));
                if (data.error !== 'Validation failed') window.Toast?.error(data.error);
            } else if (data.error) {
                window.Toast?.error(data.error);
            } else {
                window.Toast?.success(editId ? 'Đã cập nhật người dùng.' : 'Đã tạo người dùng mới.');
                closeUserModal();
                window.location.reload();
            }
        } catch(err) {
            if (err.error && err.fields) {
                Object.entries(err.fields).forEach(([k, v]) => setFieldError(k, v));
            } else {
                window.Toast?.error(err.error || 'Đã xảy ra lỗi.');
            }
        } finally {
            setLoading(submitBtn, false);
        }
    });

    // ── Toggle Active/Inactive ──────────────────────────────────────────────
    const toggleModal = document.getElementById('toggleModal');
    const toggleConfirmBtn = document.getElementById('toggleConfirm');
    let toggleId = null;
    let toggleAction = null;

    function openToggleModal(id, action) {
        toggleId = id;
        toggleAction = action;

        const isActivate = action === 'activate';
        document.getElementById('toggleModalTitle').textContent = isActivate ? 'Kích hoạt tài khoản' : 'Khoá tài khoản';
        document.getElementById('toggleMessage').textContent = (isActivate ? 'Kích hoạt' : 'Khoá') + ' tài khoản này?';
        document.getElementById('toggleModal').querySelector('.confirm-sub').textContent =
            isActivate ? 'Người dùng sẽ có thể đăng nhập trở lại.' : 'Người dùng sẽ không thể đăng nhập cho đến khi được kích hoạt lại.';
        document.getElementById('toggleConfirm').className = isActivate ? 'btn btn-success' : 'btn btn-warning';
        document.getElementById('toggleIcon').style.background = isActivate ? 'rgba(16,185,129,.12)' : 'rgba(245,158,11,.12)';
        document.getElementById('toggleIcon').style.color = isActivate ? 'var(--emerald)' : 'var(--amber)';

        toggleModal.classList.add('open');
    }

    function closeToggleModal() {
        toggleModal.classList.remove('open');
        toggleId = null;
        toggleAction = null;
    }

    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            openToggleModal(+btn.dataset.id, btn.dataset.action);
        });
    });

    document.getElementById('toggleModalClose')?.addEventListener('click', closeToggleModal);
    document.getElementById('toggleCancel')?.addEventListener('click', closeToggleModal);
    toggleModal?.addEventListener('click', e => { if (e.target === toggleModal) closeToggleModal(); });

    toggleConfirmBtn?.addEventListener('click', async function() {
        if (!toggleId) return;
        setLoading(this, true);

        try {
            const data = await api(`/admin/user/${toggleId}/toggle`, 'POST', { action: toggleAction });

            if (data.error) {
                window.Toast?.error(data.error);
            } else {
                window.Toast?.success(toggleAction === 'activate' ? 'Đã kích hoạt tài khoản.' : 'Đã khoá tài khoản.');
                closeToggleModal();
                window.location.reload();
            }
        } catch(err) {
            window.Toast?.error(err.error || 'Đã xảy ra lỗi.');
        } finally {
            setLoading(toggleConfirmBtn, false);
        }
    });

    // Escape key closes modals
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeUserModal(); closeToggleModal(); }
    });
})();
</script>

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
.btn-sm { font-size: 12px; padding: 6px 14px; }
.mt-8 { margin-top: 8px; }

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
.filter-tab.active {
    background: var(--accent);
    color: white;
}

.search-input {
    background: var(--surface-0);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    padding: 8px 14px;
    font-size: 13px;
    outline: none;
    width: 260px;
    transition: border-color var(--transition);
}
.search-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
.search-input::placeholder { color: var(--text-muted); }

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
    padding: 12px 14px;
    border-bottom: 1px solid rgba(51,65,85,.4);
    vertical-align: middle;
}
.data-table tbody tr:hover td { background: rgba(51,65,85,.25); }
.data-table tbody tr:last-child td { border-bottom: none; }

.user-cell { display: flex; align-items: center; gap: 10px; }
.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Lexend Deca', sans-serif;
    font-weight: 700;
    font-size: 14px;
    color: white;
    flex-shrink: 0;
}
.avatar--admin    { background: var(--rose); }
.avatar--lecturer { background: var(--sky); }
.avatar--student  { background: var(--emerald); }

.username-text { font-weight: 500; font-size: 13px; color: var(--text-primary); }

.role-badge {
    display: inline-flex;
    align-items: center;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 12px;
    white-space: nowrap;
}

.status-cell { display: flex; align-items: center; gap: 6px; }
.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.status-dot--active { background: var(--emerald); box-shadow: 0 0 6px var(--emerald); }
.status-dot--inactive { background: var(--text-muted); }
.status-text { font-size: 12px; }
.status-text--active { color: var(--emerald); }
.status-text--inactive { color: var(--text-muted); }

.cell-primary { font-weight: 500; font-size: 13px; color: var(--text-primary); }

.pagination-info {
    padding: 12px 14px;
    font-size: 12px;
    color: var(--text-muted);
    border-top: 1px solid rgba(51,65,85,.4);
}

.row-actions { display: flex; gap: 4px; justify-content: flex-end; }
.action-btn {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    cursor: pointer;
    transition: all var(--transition);
    text-decoration: none;
}
.action-btn svg { width: 14px; height: 14px; }
.action-btn--edit:hover  { border-color: var(--accent); color: var(--accent); background: var(--accent-soft); }
.action-btn--danger:hover { border-color: var(--rose); color: var(--rose); background: rgba(244,63,94,.1); }
.action-btn--blue { color: var(--sky); }
.action-btn--blue:hover { border-color: var(--sky); background: rgba(14,165,233,.1); }
.action-btn--warning { color: var(--amber); }
.action-btn--warning:hover { border-color: var(--amber); background: rgba(245,158,11,.1); }
.action-btn--success { color: var(--emerald); }
.action-btn--success:hover { border-color: var(--emerald); background: rgba(16,185,129,.1); }

.text-center { text-align: center; }
.text-right  { text-align: right; }
.text-muted  { color: var(--text-muted); }
.text-sm     { font-size: 12px; }

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.6);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity .25s;
}
.modal-overlay.open { opacity: 1; pointer-events: auto; }

.modal {
    background: var(--surface-1);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 480px;
    box-shadow: var(--shadow-card);
    transform: translateY(12px) scale(.98);
    transition: transform .25s;
    overflow: hidden;
}
.modal-overlay.open .modal { transform: translateY(0) scale(1); }
.modal--sm .modal-body { text-align: center; }

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
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
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 4px;
    border-radius: var(--radius-sm);
    transition: all var(--transition);
}
.modal-close svg { width: 18px; height: 18px; }
.modal-close:hover { background: var(--surface-2); color: var(--text-primary); }

.modal-body { padding: 20px; }
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 20px;
    border-top: 1px solid var(--surface-2);
}

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
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { background: var(--accent-hover); }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.btn-ghost { background: none; color: var(--text-secondary); border: 1px solid var(--surface-2); }
.btn-ghost:hover { border-color: var(--surface-3); color: var(--text-primary); }
.btn-danger { background: var(--rose); color: white; }
.btn-danger:hover { background: #e11d48; }
.btn-warning { background: var(--amber); color: white; }
.btn-warning:hover { background: #d97706; }
.btn-success { background: var(--emerald); color: white; }
.btn-success:hover { background: #059669; }
.btn-danger:disabled, .btn-warning:disabled, .btn-success:disabled { opacity: .6; cursor: not-allowed; }

.btn-spinner svg { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Form elements */
.form-group { margin-bottom: 16px; }
.form-group:last-child { margin-bottom: 0; }
.form-label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 6px;
}
.required { color: var(--rose); }
.field-hint { color: var(--text-muted); font-weight: 400; font-size: 11px; }

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.input-icon {
    position: absolute;
    left: 12px;
    width: 16px;
    height: 16px;
    color: var(--text-muted);
    pointer-events: none;
}
.form-input {
    width: 100%;
    background: var(--surface-0);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    padding: 10px 12px 10px 38px;
    font-family: inherit;
    font-size: 14px;
    outline: none;
    transition: border-color var(--transition);
}
.form-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
.form-input::placeholder { color: var(--text-muted); }
.form-input:disabled { opacity: .5; cursor: not-allowed; }

.form-select {
    width: 100%;
    background: var(--surface-0);
    border: 1px solid var(--surface-2);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    padding: 10px 12px;
    font-family: inherit;
    font-size: 14px;
    outline: none;
    cursor: pointer;
    transition: border-color var(--transition);
}
.form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }

.field-error {
    display: none;
    font-size: 11px;
    color: var(--rose);
    margin-top: 4px;
}
.input--error { border-color: var(--rose) !important; }

/* Confirm modal */
.confirm-icon {
    width: 52px;
    height: 52px;
    background: rgba(245,158,11,.12);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--amber);
    margin: 0 auto 16px;
}
.confirm-icon svg { width: 24px; height: 24px; }
.confirm-text { font-size: 15px; color: var(--text-primary); margin-bottom: 6px; }
.confirm-sub { font-size: 12px; color: var(--text-muted); }

@media (max-width: 640px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .filter-bar { flex-direction: column; align-items: stretch; }
    .filter-tabs { flex-wrap: wrap; }
    .search-input { width: 100%; }
    .data-table th:nth-child(2),
    .data-table td:nth-child(2) { display: none; }
}
</style>
