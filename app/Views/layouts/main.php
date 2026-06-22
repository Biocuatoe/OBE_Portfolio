<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token ?? '') ?>">
    <title><?= htmlspecialchars($pageTitle ?? 'iSchool OBE System') ?></title>

    <!-- Google Fonts: Lexend Deca (UI) + Be Vietnam Pro (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@300;400;500;600;700;800&family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="layout-body">

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="logo-icon">
                    <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="36" height="36" rx="10" fill="url(#logoGrad)"/>
                        <path d="M10 26 L18 10 L26 26" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13 21 H23" stroke="white" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                        <defs>
                            <linearGradient id="logoGrad" x1="0" y1="0" x2="36" y2="36">
                                <stop offset="0%" stop-color="#6366f1"/>
                                <stop offset="100%" stop-color="#0ea5e9"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="logo-name">iSchool</span>
                    <span class="logo-tag">OBE System</span>
                </div>
            </div>
        </div>

        <!-- User Profile Section -->
        <div class="sidebar-user">
            <div class="user-avatar">
                <?= strtoupper(mb_substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></div>
                <span class="user-role-badge role-<?= $_SESSION['user_role'] ?? '' ?>">
                    <?php
                    $roleLabels = ['admin' => 'Quản trị viên', 'lecturer' => 'Giảng viên', 'student' => 'Sinh viên'];
                    echo $roleLabels[$_SESSION['user_role'] ?? ''] ?? 'Unknown';
                    ?>
                </span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <?php if (($_SESSION['user_role'] ?? '') === 'student'): ?>
            <div class="nav-section-label">Menu chính</div>
            <a href="/student/dashboard" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span>E-Portfolio</span>
            </a>
            <a href="/student/courses" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], 'courses') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
                <span>Môn học</span>
            </a>
            <a href="/student/portfolio/export" class="nav-item">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span>Xuất PDF</span>
            </a>
            <?php endif; ?>

            <?php if (($_SESSION['user_role'] ?? '') === 'lecturer'): ?>
            <div class="nav-section-label">Giảng dạy</div>
            <a href="/lecturer/dashboard" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                <span>Tổng quan</span>
            </a>
            <a href="/lecturer/grading" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], 'grading') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                <span>Chấm điểm</span>
            </a>
            <?php endif; ?>

            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
            <div class="nav-section-label">Quản trị</div>
            <a href="/admin/dashboard" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                <span>Tổng quan</span>
            </a>
            <a href="/admin/programs" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], 'programs') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                </svg>
                <span>Chương trình đào tạo</span>
            </a>
            <a href="/admin/courses" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], 'courses') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
                <span>Môn học</span>
            </a>
            <a href="/admin/users" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], 'users') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>Người dùng</span>
            </a>
            <a href="/admin/activity-log" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'], 'activity-log') ? 'active' : '' ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                <span>Nhật ký hoạt động</span>
            </a>
            <?php endif; ?>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <a href="/logout" class="nav-item nav-logout">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>Đăng xuất</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="20" height="20">
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>
            <div class="topbar-actions">
                <span class="topbar-semester">HK 2024-1</span>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            <?= $content ?>
        </div>
    </main>

    <!-- Toast Notification -->
    <div id="toast-container" aria-live="polite"></div>

    <script src="/js/app.js"></script>
    <?php if (isset($extraJs)): ?>
        <?php foreach ((array)$extraJs as $js): ?>
        <script src="<?= htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
