# Hướng dẫn chụp ảnh màn hình

> Hướng dẫn chụp ảnh màn hình để hoàn thiện README và tài liệu. Thực hiện **sau khi** khởi động ứng dụng.

## Cách khởi động ứng dụng

### Cách 1: Sử dụng XAMPP (khuyến nghị)

1. Mở **XAMPP Control Panel**
2. Start **Apache** và **MySQL**
3. Truy cập `http://localhost/[tên-thư-mục-project]/public`

### Cách 2: Sử dụng PHP Built-in Server

```bash
cd C:\path\to\OBE_Portfolio\public
php -S localhost:8080
# Truy cập http://localhost:8080
```

---

## Danh sách ảnh cần chụp

### 1. Trang đăng nhập
**URL:** `http://localhost/login`  
**File:** `docs/screenshots/login.png`

1. Truy cập trang login
2. Chụp toàn bộ cửa sổ trình duyệt
3. Đảm bảo form đăng nhập hiển thị rõ

### 2. Admin Dashboard
**URL:** `http://localhost/admin`  
**File:** `docs/screenshots/admin-dashboard.png`

1. Đăng nhập với `admin01 / password`
2. Chụp Dashboard tổng quan
3. Đảm bảo hiển thị: Stats cards, biểu đồ, PLO bars, activity feed

### 3. Admin — Danh sách Chương trình đào tạo
**URL:** `http://localhost/admin/programs`  
**File:** `docs/screenshots/admin-programs.png`

1. Vào menu sidebar → Chương trình đào tạo
2. Chụp bảng danh sách với dữ liệu mẫu

### 4. Admin — Quản lý PLO
**URL:** `http://localhost/admin/program/1/plos` (thay 1 bằng ID program)  
**File:** `docs/screenshots/admin-plos.png`

1. Vào CTĐT → Bấm biểu tượng PLO
2. Chụp bảng PLO với stats

### 5. Admin — Báo cáo Attainment
**URL:** `http://localhost/admin/report/attainment/1`  
**File:** `docs/screenshots/admin-report-attainment.png`

1. Vào Báo cáo PLO từ sidebar hoặc Dashboard
2. Chụp bảng PLO và top sinh viên

### 6. Admin — Quản lý Users
**URL:** `http://localhost/admin/users`  
**File:** `docs/screenshots/admin-users.png`

1. Vào Người dùng từ sidebar
2. Chụp bảng với filter pills và table

### 7. Lecturer Dashboard
**URL:** `http://localhost/lecturer`  
**File:** `docs/screenshots/lecturer-dashboard.png`

1. Đăng nhập với `lecturer01 / password`
2. Chụp Dashboard giảng viên
3. Đảm bảo hiển thị: Assignment cards, pending grading list

### 8. Lecturer — Ma trận CLO→PLO
**URL:** `http://localhost/lecturer/assignment/1/mapping` (thay 1 bằng ID assignment)  
**File:** `docs/screenshots/lecturer-mapping.png`

1. Vào môn học → Ma trận
2. Chụp ma trận với các ô weight

### 9. Lecturer — Live Grading
**URL:** `http://localhost/lecturer/assessment/1/grade` (thay 1 bằng ID assessment)  
**File:** `docs/screenshots/lecturer-grading.png`

1. Vào bài kiểm tra → Bấm "Chấm điểm"
2. Chụp bảng grading với input scores

### 10. Student Dashboard — E-Portfolio
**URL:** `http://localhost/student`  
**File:** `docs/screenshots/student-portfolio.png`

1. Đăng nhập với `student01 / password`
2. Chụp Dashboard E-Portfolio
3. Đảm bảo hiển thị: Radar chart, PLO bars, CLO cards

---

## Cách lưu ảnh

1. Mở trình duyệt → chụp ảnh (Windows: `Win+Shift+S` hoặc `PrtScn`)
2. Mở ảnh trong Paint hoặc công cụ chỉnh sửa
3. Resize nếu quá lớn (recommend: 1280-1920px width)
4. Lưu dưới dạng PNG vào thư mục tương ứng trong `docs/screenshots/`
5. Ảnh sẽ tự động hiển thị trong README.md

---

## Responsive Screenshot (tùy chọn)

Nếu muốn tạo ấn tượng chuyên nghiệp, chụp ở các kích thước:
- **Desktop**: 1920×1080px
- **Tablet**: 768×1024px (thu nhỏ trình duyệt)
- **Mobile**: 375×667px (DevTools → Toggle device toolbar)

Gộp 3 ảnh vào 1 file để show responsive design trong portfolio.
