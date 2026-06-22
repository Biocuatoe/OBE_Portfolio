<?php /* app/Views/auth/login.php */ ?>
<?php $pageTitle = 'Đăng nhập'; ?>

<style>
/* ── Login Page Reset ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── Login Page Layout ── */
.login-page {
  display: flex;
  min-height: 100vh;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* ── Left Panel (60%) ── */
.login-left {
  flex: 0 0 60%;
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

/* Decorative circles */
.login-left::before {
  content: '';
  position: absolute;
  width: 500px;
  height: 500px;
  border-radius: 50%;
  border: 1px solid rgba(79, 70, 229, 0.06);
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.login-left::after {
  content: '';
  position: absolute;
  width: 700px;
  height: 700px;
  border-radius: 50%;
  border: 1px solid rgba(79, 70, 229, 0.04);
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.login-left-content {
  position: relative;
  z-index: 1;
  text-align: center;
  max-width: 440px;
  padding: 40px;
}

.login-left-icon {
  width: 64px;
  height: 64px;
  background: rgba(79, 70, 229, 0.1);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
  color: #4f46e5;
}

.login-left-icon svg {
  width: 32px;
  height: 32px;
}

.login-left h1 {
  font-size: 28px;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.2;
  margin-bottom: 12px;
  letter-spacing: -0.5px;
}

.login-left p {
  font-size: 15px;
  color: #64748b;
  line-height: 1.6;
}

.login-left-quote {
  margin-top: 40px;
  padding-top: 32px;
  border-top: 1px solid rgba(79, 70, 229, 0.1);
  font-size: 14px;
  color: #94a3b8;
  font-style: italic;
}

.login-left-quote cite {
  display: block;
  margin-top: 8px;
  font-style: normal;
  font-size: 13px;
  color: #475569;
  font-weight: 500;
}

/* ── Right Panel (40%) ── */
.login-right {
  flex: 0 0 40%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  padding: 40px;
}

.login-panel {
  width: 100%;
  max-width: 380px;
}

/* ── Panel Header ── */
.login-panel-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 32px;
}

.login-panel-logo {
  width: 44px;
  height: 44px;
  background: #0f172a;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
}

.login-panel-logo svg {
  width: 24px;
  height: 24px;
}

.login-panel-brand-text {
  display: flex;
  flex-direction: column;
}

.login-panel-brand-name {
  font-weight: 700;
  font-size: 18px;
  color: #0f172a;
  letter-spacing: -0.3px;
}

.login-panel-brand-desc {
  font-size: 12px;
  color: #94a3b8;
}

/* ── Form Heading ── */
.login-panel-heading {
  font-size: 22px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 4px;
  letter-spacing: -0.3px;
}

.login-panel-subheading {
  font-size: 14px;
  color: #64748b;
  margin-bottom: 28px;
}

/* ── Form Fields ── */
.login-form-group {
  margin-bottom: 18px;
}

.login-form-label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #475569;
  margin-bottom: 6px;
}

.login-form-input-wrapper {
  position: relative;
}

.login-form-input-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: #94a3b8;
  pointer-events: none;
}

.login-form-input {
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  padding: 10px 12px 10px 38px;
  font-family: inherit;
  font-size: 14px;
  color: #0f172a;
  background: #ffffff;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.login-form-input::placeholder {
  color: #94a3b8;
}

.login-form-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.login-form-input.has-toggle {
  padding-right: 42px;
}

.input-toggle-pass {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.input-toggle-pass:hover {
  color: #64748b;
}

.input-toggle-pass svg {
  width: 18px;
  height: 18px;
}

/* ── Error Message ── */
.login-error-msg {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
  border-radius: 6px;
  padding: 12px;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 20px;
}

.login-error-msg svg {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

/* ── Submit Button ── */
.login-submit-btn {
  width: 100%;
  padding: 12px;
  background: #0f172a;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  font-family: inherit;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 24px;
}

.login-submit-btn:hover {
  background: #1e293b;
}

.login-submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.login-submit-btn svg {
  width: 16px;
  height: 16px;
}

/* ── Forgot Password Link ── */
.login-forgot-link {
  display: block;
  text-align: right;
  font-size: 13px;
  color: #4f46e5;
  text-decoration: none;
  margin-top: -8px;
  margin-bottom: 20px;
}

.login-forgot-link:hover {
  text-decoration: underline;
}

/* ── Responsive: Mobile ── */
@media (max-width: 768px) {
  .login-page {
    flex-direction: column;
  }

  .login-left {
    flex: none;
    min-height: 120px;
    padding: 24px;
  }

  .login-left::before,
  .login-left::after {
    display: none;
  }

  .login-left-content {
    padding: 0;
  }

  .login-left-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 16px;
  }

  .login-left-icon svg {
    width: 24px;
    height: 24px;
  }

  .login-left h1 {
    font-size: 20px;
    margin-bottom: 8px;
  }

  .login-left p {
    font-size: 13px;
  }

  .login-left-quote {
    display: none;
  }

  .login-right {
    flex: 1;
    padding: 24px;
    align-items: flex-start;
  }

  .login-panel {
    max-width: 100%;
  }

  .login-panel-brand {
    margin-bottom: 24px;
  }

  .login-panel-heading {
    font-size: 20px;
  }
}
</style>

<div class="login-page">
  <!-- Left Panel: Branding -->
  <div class="login-left">
    <div class="login-left-content">
      <div class="login-left-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
          <path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
      </div>
      <h1>Nền tảng E-Portfolio</h1>
      <p>Quản lý và đánh giá chương trình đào tạo theo Outcome-Based Education</p>
      <div class="login-left-quote">
        "Giáo dục không chỉ là làm đầy cái bình, mà là thắp sáng ngọn lửa trong mỗi người học."
        <cite>— W.B. Yeats</cite>
      </div>
    </div>
  </div>

  <!-- Right Panel: Login Form -->
  <div class="login-right">
    <div class="login-panel">
      <!-- Brand Header -->
      <div class="login-panel-brand">
        <div class="login-panel-logo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
          </svg>
        </div>
        <div class="login-panel-brand-text">
          <span class="login-panel-brand-name">OBE Portfolio</span>
          <span class="login-panel-brand-desc">Quản lý E-Portfolio</span>
        </div>
      </div>

      <!-- Form Heading -->
      <h2 class="login-panel-heading">Chào mừng trở lại</h2>
      <p class="login-panel-subheading">Đăng nhập để truy cập hệ thống</p>

      <!-- Error Message -->
      <?php if (!empty($error)): ?>
      <div class="login-error-msg" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <!-- Login Form -->
      <form method="POST" action="/login" id="loginForm" novalidate>
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

        <!-- Username Field -->
        <div class="login-form-group">
          <label class="login-form-label" for="username">Tên đăng nhập / Email</label>
          <div class="login-form-input-wrapper">
            <svg class="login-form-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input
              type="text"
              id="username"
              name="username"
              class="login-form-input"
              placeholder="admin01 hoặc email@..."
              value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
              autocomplete="username"
              required
            >
          </div>
        </div>

        <!-- Password Field -->
        <div class="login-form-group">
          <label class="login-form-label" for="password">Mật khẩu</label>
          <div class="login-form-input-wrapper">
            <svg class="login-form-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input
              type="password"
              id="password"
              name="password"
              class="login-form-input has-toggle"
              placeholder="••••••••"
              autocomplete="current-password"
              required
            >
            <button type="button" class="input-toggle-pass" aria-label="Hiện mật khẩu" id="togglePass">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Forgot Password -->
        <a href="#" class="login-forgot-link">Quên mật khẩu?</a>

        <!-- Submit Button -->
        <button type="submit" class="login-submit-btn" id="loginBtn">
          <span class="btn-text">Đăng nhập</span>
          <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14"/>
            <path d="M12 5l7 7-7 7"/>
          </svg>
        </button>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('togglePass').addEventListener('click', function() {
  const input = document.getElementById('password');
  const isPassword = input.type === 'password';
  input.type = isPassword ? 'text' : 'password';

  // Update icon
  const svg = this.querySelector('svg');
  if (isPassword) {
    svg.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
  } else {
    svg.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  }
});

document.getElementById('loginForm').addEventListener('submit', function() {
  const btn = document.getElementById('loginBtn');
  btn.disabled = true;
  btn.querySelector('.btn-text').textContent = 'Đang xử lý...';
});
</script>
