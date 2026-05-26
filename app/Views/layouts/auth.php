<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập — iSchool OBE System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@300;400;500;600;700;800&family=Be+Vietnam+Pro:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="auth-body">
    <div class="auth-bg">
        <div class="auth-bg-orb orb-1"></div>
        <div class="auth-bg-orb orb-2"></div>
        <div class="auth-bg-orb orb-3"></div>
        <div class="auth-grid-overlay"></div>
    </div>

    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Brand -->
            <div class="auth-brand">
                <div class="auth-logo">
                    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" width="40" height="40">
                        <rect width="40" height="40" rx="12" fill="url(#authLogoGrad)"/>
                        <path d="M11 29 L20 11 L29 29" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14.5 23.5 H25.5" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                        <defs>
                            <linearGradient id="authLogoGrad" x1="0" y1="0" x2="40" y2="40">
                                <stop offset="0%" stop-color="#6366f1"/>
                                <stop offset="100%" stop-color="#0ea5e9"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="auth-brand-text">
                    <h1 class="auth-brand-name">iSchool OBE</h1>
                    <p class="auth-brand-desc">Hệ thống quản lý chuẩn đầu ra & E-Portfolio</p>
                </div>
            </div>

            <?= $content ?>
        </div>

        <!-- Demo credentials -->
        <div class="auth-demo-hint">
            <span>Demo:</span>
            <button class="demo-btn" data-user="admin01" data-pass="password">👨‍💼 Admin</button>
            <button class="demo-btn" data-user="lecturer01" data-pass="password">👨‍🏫 Giảng viên</button>
            <button class="demo-btn" data-user="student01" data-pass="password">🎓 Sinh viên</button>
        </div>
    </div>

    <script>
    // Demo login fill
    document.querySelectorAll('.demo-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('username').value = btn.dataset.user;
            document.getElementById('password').value = btn.dataset.pass;
            document.getElementById('username').focus();
        });
    });
    </script>
</body>
</html>
