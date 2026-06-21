# 🎓 OBE & E-PORTFOLIO SYSTEM
### iSchool 2026 — Đồ án Chuyên ngành PHP

> **Hệ thống Quản lý Chuẩn đầu ra (OBE) và E-Portfolio** — xây dựng trên PHP thuần, kiến trúc MVC tự triển khai, PDO Singleton, AJAX Fetch API, Chart.js.

---

## 📁 Cấu trúc thư mục

```
obe_portfolio/
├── app/
│   ├── Controllers/
│   │   ├── AdminController.php      ← Quản lý program, PLO, course, users
│   │   ├── AuthController.php       ← Login, logout, session
│   │   ├── LecturerController.php   ← CLO, Assessment, Rubric
│   │   ├── ScoreController.php      ← Chấm điểm, API AJAX
│   │   └── StudentController.php    ← E-Portfolio, dashboard
│   ├── Models/
│   │   ├── AssessmentModel.php
│   │   └── ScoreModel.php           ← Thuật toán CLO→PLO attainment
│   └── Views/
│       ├── admin/
│       ├── auth/
│       ├── errors/
│       ├── layouts/                  ← main.php, auth.php
│       ├── lecturer/
│       └── student/
├── config/
│   └── database.php                 ← DB credentials
├── core/
│   ├── BaseController.php           ← Helper: view, redirect, json, CSRF
│   ├── BaseModel.php                ← Generic CRUD
│   ├── Database.php                 ← PDO Singleton
│   └── Router.php                   ← URL routing engine
├── database/
│   └── schema.sql                   ← CSDL 3NF + seed data
└── public/                          ← Web root (trỏ Apache/Nginx vào đây)
    ├── index.php                    ← Front Controller
    ├── .htaccess
    ├── css/app.css
    └── js/
        ├── app.js                   ← Toast, sidebar
        └── grade_sync.js            ← AJAX live grading engine
```

---

## ⚙️ Cài đặt

### Yêu cầu
- PHP 8.1+
- MySQL 8.0+ / MariaDB 10.6+
- Apache 2.4+ với `mod_rewrite`

### Bước 1: Cài đặt CSDL
```bash
mysql -u root -p < database/schema.sql
```

### Bước 2: Cấu hình database
Mở `config/database.php`, cập nhật thông tin kết nối:
```php
return [
    'host'     => 'localhost',
    'dbname'   => 'obe_portfolio',
    'username' => 'root',
    'password' => 'your_password',
];
```

### Bước 3: Cấu hình Apache VirtualHost
```apache
<VirtualHost *:80>
    ServerName obe.local
    DocumentRoot /path/to/obe_portfolio/public
    
    <Directory /path/to/obe_portfolio/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Bước 4: Thêm host (Windows/Mac)
```
127.0.0.1   obe.local
```

### Bước 5: Chạy
Truy cập: `http://obe.local`

---

## 🔑 Tài khoản Demo

| Vai trò | Username | Password |
|---------|----------|----------|
| Admin (Trưởng khoa) | `admin01` | `password` |
| Giảng viên | `lecturer01` | `password` |
| Sinh viên | `student01` | `password` |
| Sinh viên | `student02` | `password` |

---

## 🏗️ Kiến trúc & Các điểm nổi bật

### 1. MVC Pattern tự xây dựng
- **Router**: Pattern matching với named params (`:id`), middleware support
- **BaseController**: View rendering với layout wrapping, JSON API, CSRF protection
- **BaseModel**: Generic CRUD qua PDO Prepared Statements

### 2. PDO Singleton Pattern
```php
$db = Database::getInstance(); // Chỉ tạo 1 kết nối duy nhất
$db->fetchAll($sql, $params);  // Shorthand methods
```

### 3. Thuật toán CLO→PLO Attainment (Core Business Logic)
```
student_score / rubric.max_score × 100 = CLO_achieved%
CLO_achieved% × mapping.weight          = PLO_contribution
Σ(PLO_contributions) / Σ(weights)       = PLO_achieved%
```
Sử dụng **Database Transaction** để đảm bảo tính nhất quán khi cập nhật attainment.

### 4. AJAX Live Grading (grade_sync.js)
- **Debounce 600ms**: Không gửi request liên tục khi nhập
- **Visual feedback**: 3 trạng thái (saving → saved → idle)
- **Keyboard navigation**: Arrow keys, Enter để di chuyển giữa ô
- **Batch save**: Ctrl+S lưu tất cả
- **Client-side validation**: Kiểm tra range trước khi gửi

### 5. Security
- CSRF Token trên mọi form và API call
- Password hash với bcrypt (cost=12)
- PDO Prepared Statements (chống SQL Injection)
- Session regeneration sau login
- Input sanitization với `htmlspecialchars()`
- Rate-limit chống brute force (random delay)
- Security Headers: X-Frame-Options, X-XSS-Protection, X-Content-Type-Options

---

## 📊 Database Schema (3NF)

### Các bảng chính
| Bảng | Mô tả |
|------|-------|
| `users` | Người dùng (admin/lecturer/student) |
| `programs` | Chương trình đào tạo |
| `plos` | Program Learning Outcomes |
| `courses` | Môn học |
| `course_assignments` | Phân công giảng viên |
| `enrollments` | Sinh viên đăng ký môn |
| `clos` | Course Learning Outcomes |
| **`clo_plo_mappings`** | **Ma trận ánh xạ CLO→PLO (linh hồn OBE)** |
| `assessments` | Bài kiểm tra |
| `rubrics` | Tiêu chí chấm điểm |
| `student_scores` | Điểm sinh viên |
| `clo_attainments` | Mức đạt CLO (computed) |
| `plo_attainments` | Mức đạt PLO (computed, dùng cho radar chart) |
| `activity_logs` | Audit trail |

---
