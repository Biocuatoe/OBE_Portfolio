<?php /* app/Views/auth/login.php */ ?>
<?php $pageTitle = 'Đăng nhập'; ?>

<form method="POST" action="/login" class="auth-form" id="loginForm" novalidate>
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <h2 class="auth-form-title">Chào mừng trở lại</h2>
    <p class="auth-form-subtitle">Đăng nhập để truy cập hệ thống</p>

    <?php if (!empty($error)): ?>
    <div class="alert alert-error" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <label class="form-label" for="username">Tên đăng nhập / Email</label>
        <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <input
                type="text"
                id="username"
                name="username"
                class="form-input"
                placeholder="admin01 hoặc email@..."
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                autocomplete="username"
                required
            >
        </div>
    </div>

    <div class="form-group">
        <label class="form-label" for="password">
            Mật khẩu
            <a href="#" class="form-label-link">Quên mật khẩu?</a>
        </label>
        <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input
                type="password"
                id="password"
                name="password"
                class="form-input"
                placeholder="••••••••"
                autocomplete="current-password"
                required
            >
            <button type="button" class="input-toggle-pass" aria-label="Hiện mật khẩu" id="togglePass">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-full" id="loginBtn">
        <span class="btn-text">Đăng nhập</span>
        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14"/><path d="M12 5l7 7-7 7"/>
        </svg>
    </button>
</form>

<script>
document.getElementById('togglePass').addEventListener('click', function() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
});

document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.querySelector('.btn-text').textContent = 'Đang xử lý...';
});
</script>
