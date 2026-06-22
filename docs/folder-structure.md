# Cấu trúc thư mục — OBE & E-Portfolio System

> Mô tả chi tiết cấu trúc thư mục của dự án OBE Portfolio. Được viết bằng tiếng Việt, nhất quán với ngôn ngữ codebase.

---

## Tổng quan

```
OBE_Portfolio/
├── app/                          # Mã nguồn ứng dụng chính
│   ├── Config/                   # Cấu hình database
│   ├── Controllers/              # Các Controller (MVC)
│   ├── Models/                  # Các Model dữ liệu
│   └── Views/                   # Giao diện HTML (templates)
│       ├── admin/               # Giao diện quản trị (Admin)
│       ├── auth/                # Trang đăng nhập / đăng xuất
│       ├── errors/              # Trang lỗi 403 / 404
│       ├── layouts/             # Layout chính (main.php, auth.php)
│       ├── lecturer/            # Giao diện giảng viên
│       └── student/             # Giao diện sinh viên
├── config/                       # Cấu hình ứng dụng
│   └── database.php             # Thông tin kết nối MySQL
├── core/                         # Framework core tự xây dựng
│   ├── BaseController.php        # Controller cha — helpers: view(), redirect(), json(), csrfToken()
│   ├── BaseModel.php             # Model cha — generic CRUD
│   ├── Database.php              # PDO Singleton — kết nối DB duy nhất
│   ├── Router.php                # URL Routing — hỗ trợ named params, middleware
│   └── AuthService.php           # Logic xác thực đăng nhập
├── database/                     # SQL schema & seed data
│   └── schema.sql                # 14 bảng 3NF + dữ liệu demo
├── docs/                         # Tài liệu dự án
│   ├── screenshots/              # Ảnh chụp màn hình
│   ├── folder-structure.md       # (file này)
│   ├── features.md               # Mô tả chi tiết tính năng
│   ├── database-schema.md        # Sơ đồ ERD chi tiết
│   ├── ERD.md                   # Entity-Relationship Diagram (mermaid)
│   └── SITEMAP.md              # Site Map đầy đủ
├── public/                       # Web root (Document Root của Apache/Nginx)
│   ├── index.php                # Front Controller — entry point duy nhất
│   ├── .htaccess               # Rewrite rules cho URL thân thiện
│   ├── reset_pass.php          # Trang reset mật khẩu
│   ├── css/
│   │   └── app.css             # Stylesheet chính
│   └── js/
│       ├── app.js              # JavaScript chung: toast, sidebar, filter
│       └── grade_sync.js       # AJAX live grading engine (debounce, keyboard nav)
├── .cursor/                     # Cursor IDE settings
│   ├── rules/
│   └── mcps/
├── .git/
├── .htaccess                   # Apache rewrite rules gốc
├── README.md                   # Tài liệu tổng quan
└── composer.json / composer.lock  # (nếu sử dụng)
```

---

## Mô tả chi tiết từng thư mục

### `app/Controllers/` — Lớp xử lý request

| File | Vai trò | Routes chính |
|------|---------|--------------|
| `AdminController.php` | Dashboard, Program, PLO, Course, Assignment, User, Activity Log, Report | `/admin/*` |
| `AuthController.php` | Đăng nhập, đăng xuất | `/auth/*` |
| `LecturerController.php` | CLO, Assessment, Rubric, Dashboard | `/lecturer/*` |
| `StudentController.php` | Dashboard (E-Portfolio), Courses | `/student/*` |
| `ScoreController.php` | Live grading, API điểm | `/lecturer/assessment/*`, `/api/score/*` |

**Đặc điểm kiến trúc:**
- Tất cả controller kế thừa `BaseController`
- `requireAuth()` kiểm tra session và vai trò ở đầu mỗi action
- `verifyCsrf()` bảo vệ mọi request POST/PUT/DELETE
- View được render bằng `view('path', $data, $layout)` — tự động bọc layout
- JSON API trả về bằng `json($data, $status)`

### `app/Models/` — Lớp truy xuất dữ liệu

| File | Mô tả |
|------|--------|
| `ScoreModel.php` | Thuật toán CLO→PLO attainment, lưu điểm, query biểu đồ |
| `AssessmentModel.php` | CRUD bài kiểm tra, rubric |

### `app/Views/` — Templates HTML

| Thư mục | Nội dung |
|---------|----------|
| `admin/` | Dashboard, Programs, PLOs, Courses, Activity Logs, Report Attainment, Mapping Matrix |
| `auth/` | Login page |
| `layouts/` | `main.php` (layout chính có sidebar), `auth.php` (layout đơn giản không sidebar) |
| `lecturer/` | Dashboard, CLOs, Assessments, Rubrics, Grading (live) |
| `student/` | Dashboard (E-Portfolio với Radar Chart), Courses, Portfolio PDF export |
| `errors/` | 403 Forbidden, 404 Not Found |

### `core/` — Framework tự xây dựng

| File | Mô tả |
|------|--------|
| `Database.php` | **PDO Singleton** — đảm bảo 1 connection duy nhất. Helpers: `fetchAll()`, `fetchOne()`, `query()`, `beginTransaction()`, `logActivity()` |
| `Router.php` | **URL Routing** — pattern matching với `:param`. Hỗ trợ GET/POST, named routes. Xử lý 404 tự động. |
| `BaseController.php` | View rendering, redirect, JSON response, CSRF token, auth check |
| `BaseModel.php` | Generic CRUD cho model con |
| `AuthService.php` | Logic xác thực (login attempt, redirect path theo role) |

### `public/` — Web Root

| File/Thư mục | Mô tả |
|--------------|--------|
| `index.php` | **Front Controller** — load toàn bộ app từ đây. Đăng ký routes, khởi tạo session, output security headers. |
| `.htaccess` | Rewrite tất cả request → `index.php` (trừ file tồn tại) |
| `css/app.css` | ~2500 dòng CSS custom — CSS variables, layout system, component styles |
| `js/app.js` | Toast notification, sidebar toggle, activity filter |
| `js/grade_sync.js` | **Live Grading Engine** — debounce 600ms, keyboard navigation, batch save Ctrl+S |

### `database/` — Cơ sở dữ liệu

| File | Mô tả |
|------|--------|
| `schema.sql` | 14 bảng MySQL (3NF, InnoDB), indexes, FK constraints, seed data demo |

### `docs/` — Tài liệu

| File | Mô tả |
|------|--------|
| `ERD.md` | Sơ đồ ERD (Mermaid) với 14 bảng, cardinalities |
| `SITEMAP.md` | Toàn bộ routes + site map diagram |
| `folder-structure.md` | File này |
| `features.md` | Hướng dẫn chi tiết từng workflow |
| `database-schema.md` | Mô tả chi tiết từng bảng và ràng buộc |
| `screenshots/` | Thư mục chứa ảnh chụp màn hình |

---

## Luồng request (Request Lifecycle)

```
Trình duyệt gửi request
        ↓
public/index.php (Front Controller)
    • Load core classes (Database, Router, Controllers)
    • Session start
    • Security headers
        ↓
Router::dispatch()
    • Parse URL → match route pattern
    • Kiểm tra method (GET/POST)
    • Gọi Controller action
        ↓
BaseController::requireAuth()
    • Kiểm tra session['user_id'] tồn tại
    • Kiểm tra session['user_role'] ∈ allowed roles
    • Redirect / 403 nếu không hợp lệ
        ↓
Controller Action
    • Gọi Database helper → query MySQL
    • Xử lý business logic (ScoreModel::saveScore)
    • Gọi $this->view('path', $data) HOẶC $this->json($data)
        ↓
View (PHP Template)
    • ob_start() → render template → ob_get_clean()
    • Wrap với layout (main.php hoặc auth.php)
    • Output HTML → trình duyệt
```

---

## Mapping giữa URL và Controller Action

| URL Pattern | Controller | Action | Mô tả |
|------------|-----------|--------|--------|
| `/admin` | AdminController | `dashboard()` | Dashboard tổng quan |
| `/admin/programs` | AdminController | `programs()` | Danh sách CTĐT |
| `/admin/program/:id/plos` | AdminController | `plos()` | PLO của CTĐT |
| `/admin/courses` | AdminController | `courses()` | Danh sách môn học |
| `/admin/users` | AdminController | `users()` | Quản lý người dùng |
| `/admin/activity-logs` | AdminController | `activityLogs()` | Audit trail |
| `/admin/report/attainment/:id` | AdminController | `reportAttainment()` | Báo cáo PLO |
| `/lecturer` | LecturerController | `dashboard()` | Dashboard GV |
| `/lecturer/assignment/:id/clos` | LecturerController | `clos()` | Quản lý CLO |
| `/lecturer/assessment/:id/rubrics` | LecturerController | `rubrics()` | Quản lý Rubric |
| `/lecturer/assessment/:id/grade` | ScoreController | `gradingSheet()` | Live Grading |
| `/student` | StudentController | `dashboard()` | E-Portfolio |
| `/api/score/save` | ScoreController | `apiSave()` | AJAX lưu điểm |
