# User Stories — OBE & E-Portfolio System

> **OBE & E-Portfolio System** — INS3064 Multimedia Design & Web Development
>
> Tất cả User Stories được viết theo chuẩn Agile: **"As a [role], I want to [action], so that [reason]."**

---

## Mục lục

1. [Admin Stories](#1-admin-stories)
2. [Lecturer Stories](#2-lecturer-stories)
3. [Student Stories](#3-student-stories)

---

## 1. Admin Stories

---

### US-01: Quản lý Chương trình đào tạo (Program)

**As a** Administrator,
**I want to** create, view, edit, and delete training programs (Program),
**so that** I can organize the curriculum structure and assign Program Learning Outcomes (PLOs) to each program.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Must Have (MoSCoW) |
| **Acceptance Criteria** | Admin có thể tạo Program với mã DUY NHẤT (2-20 ký tự, chữ hoa/số/gạch ngang). Admin không thể xóa Program đang có Môn học. Bảng Programs hiển thị số Môn, số PLO, số GV, số SV. |
| **Validations** | Mã Program không trùng. Tên bắt buộc nhập. Credits 1-10. |
| **Epic** | EP-01: System Administration |

---

### US-02: Quản lý Chuẩn đầu ra chương trình (PLO)

**As a** Administrator,
**I want to** add, edit, and remove PLOs (Program Learning Outcomes) within each program,
**so that** I can define what competencies students must achieve upon graduation.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Must Have |
| **Acceptance Criteria** | Mỗi PLO có mã DUY NHẤT trong Program. PLO có Category (Knowledge / Skill / Attitude). Xóa PLO chặn nếu đang được CLO ánh xạ. |
| **Validations** | Mã PLO không trùng trong cùng Program. Mô tả bắt buộc. |
| **Epic** | EP-01: System Administration |

---

### US-03: Quản lý Môn học (Course) và Phân công Giảng viên

**As a** Administrator,
**I want to** create courses, assign lecturers to courses for specific semesters,
**so that** the right lecturers are responsible for the right courses in each academic term.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Must Have |
| **Acceptance Criteria** | Mỗi phân công (Lecturer + Course + Semester) là DUY NHẤT. Không xóa Môn học đang có sinh viên đăng ký. |
| **Validations** | Mã môn học DUY NHẤT toàn hệ thống (định dạng: 2-6 chữ + 2-4 số + chữ tùy chọn). Credits 1-10. |
| **Epic** | EP-01: System Administration |

---

### US-04: Quản lý Người dùng (User Management)

**As a** Administrator,
**I want to** create user accounts, assign roles (admin/lecturer/student), and deactivate accounts,
**so that** only authorized users can access the system and each user has appropriate permissions.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Must Have |
| **Acceptance Criteria** | Admin có thể toggle active/inactive tài khoản (không xóa). Không thể tự khóa tài khoản mình đang dùng. |
| **Validations** | Username và Email DUY NHẤT. Password tối thiểu 8 ký tự. |
| **Epic** | EP-01: System Administration |

---

### US-05: Xem Báo cáo PLO Attainment

**As a** Administrator,
**I want to** view a report showing PLO attainment statistics per program,
**so that** I can evaluate how well the program is achieving its learning outcomes and identify students who need support.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Must Have |
| **Acceptance Criteria** | Báo cáo hiển thị: bảng PLO với % trung bình, số SV đạt/không đạt, tỷ lệ đạt. Top 10 sinh viên xuất sắc. Chọn Program bằng dropdown. |
| **Thresholds** | Ngưỡng đạt chuẩn: >= 70% |
| **Epic** | EP-01: System Administration |

---

### US-06: Giám sát Hoạt động hệ thống (Activity Logs)

**As a** Administrator,
**I want to** view a detailed log of all actions in the system,
**so that** I can audit user behavior and investigate issues when they occur.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Should Have |
| **Acceptance Criteria** | Logs hiển thị: user, action, entity, timestamp, IP address. Lọc theo: vai trò, loại hành động, ngày, user cụ thể. |
| **Epic** | EP-01: System Administration |

---

## 2. Lecturer Stories

---

### US-07: Tạo và quản lý CLO (Course Learning Outcomes)

**As a** Lecturer,
**I want to** create CLOs (Course Learning Outcomes) with Bloom's Taxonomy levels,
**so that** I can define specific competencies students will develop in my course.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Must Have |
| **Acceptance Criteria** | Mỗi CLO có mã DUY NHẤT trong Môn học. Chọn Bloom Level 1-6. Xóa CLO bị chặn nếu đang được Rubric sử dụng. |
| **Bloom Levels** | 1: Remember, 2: Understand, 3: Apply, 4: Analyze, 5: Evaluate, 6: Create |
| **Epic** | EP-02: Teaching Management |

---

### US-08: Thiết lập Ma trận CLO→PLO

**As a** Lecturer,
**I want to** define the contribution weight of each CLO toward each PLO,
**so that** the system can correctly calculate how each rubric score affects PLO attainment.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Must Have |
| **Acceptance Criteria** | Nhập trọng số 0-100% cho mỗi cặp CLO-PLO. Weight = 0 xóa ánh xạ. Cảnh báo nếu tổng weight theo dòng hoặc cột vượt 100%. Auto-save khi rời ô. Export CSV. |
| **Validation** | Weight từ 0 đến 100. Tổng theo CLO nên = 100%. |
| **Epic** | EP-02: Teaching Management |

---

### US-09: Quản lý Assessment và Rubric

**As a** Lecturer,
**I want to** create assessments (quiz, assignment, midterm, final, project, lab) with rubric criteria linked to CLOs,
**so that** I can evaluate students against specific learning outcomes.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Must Have |
| **Acceptance Criteria** | Mỗi Rubric gắn với MỘT CLO và MỘT Assessment. Có thể thay đổi thứ tự rubric. Xóa Rubric bị chặn nếu đã có điểm sinh viên. Toggle is_published để công bố cho SV. |
| **Assessment Types** | quiz, assignment, midterm, final, project, lab |
| **Epic** | EP-02: Teaching Management |

---

### US-10: Live Grading (Chấm điểm thời gian thực)

**As a** Lecturer,
**I want to** grade student submissions using a spreadsheet-style interface with auto-save,
**so that** I can quickly enter scores and provide feedback without page reloads.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Must Have |
| **Acceptance Criteria** | Bảng học sinh x rubric. Debounce 600ms trước khi lưu. Trạng thái realtime (saving -> saved -> idle). Keyboard navigation (Tab, Arrow keys, Enter). Ctrl+S lưu tất cả pending. Feedback textarea expandable. Progress bar tổng. |
| **Auto-calculation** | Hệ thống tự động tính CLO Attainment và PLO Attainment sau mỗi lần lưu điểm. |
| **Epic** | EP-02: Teaching Management |

---

### US-11: Xem Thống kê và Phân bố điểm

**As a** Lecturer,
**I want to** view real-time grading statistics including score distribution and completion rate,
**so that** I can monitor my grading progress and identify students who need intervention.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Should Have |
| **Acceptance Criteria** | Bar chart phân bố điểm (A/B/C/D/F). Bảng criteria: avg, min, max, số SV đã chấm. Progress bar tỷ lệ hoàn thành. |
| **Epic** | EP-02: Teaching Management |

---

## 3. Student Stories

---

### US-12: Đăng nhập và xem E-Portfolio Dashboard

**As a** Student,
**I want to** log in with my credentials and view my E-Portfolio dashboard,
**so that** I can see my overall learning achievement at a glance.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Must Have |
| **Acceptance Criteria** | Redirect đúng theo role. Dashboard hiển thị: % năng lực tổng thể, số PLO đạt chuẩn (>=70%), số môn đang học, số bài đã chấm. |
| **Epic** | EP-03: Student Portal |

---

### US-13: Xem Biểu đồ Radar PLO

**As a** Student,
**I want to** view a radar chart showing my PLO attainment levels compared to the 70% threshold,
**so that** I can understand my strengths and areas for improvement across all program outcomes.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Must Have |
| **Acceptance Criteria** | Radar chart với mỗi axis = 1 PLO. Đường màu tím = mức đạt thực tế. Đường vàng = ngưỡng 70%. Progress bars kèm màu theo ngưỡng (xanh >=70%, vàng 50-69%, đỏ <50%). |
| **Epic** | EP-03: Student Portal |

---

### US-14: Xem Chi tiết CLO theo từng Môn học

**As a** Student,
**I want to** see my CLO attainment breakdown by course,
**so that** I can understand which course learning outcomes I have achieved and which ones I need to work on.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Must Have |
| **Acceptance Criteria** | Danh sách môn học đã đăng ký với % tiến độ chấm điểm. Mỗi môn hiển thị CLO cards với % đạt và Bloom level badge. Màu sắc theo ngưỡng. |
| **Epic** | EP-03: Student Portal |

---

### US-15: Xuất Portfolio ra PDF

**As a** Student,
**I want to** export my E-Portfolio as a PDF document,
**so that** I can have an official record of my learning achievements for my portfolio or job applications.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Should Have |
| **Acceptance Criteria** | PDF chứa: thông tin sinh viên, bảng PLO attainment với progress bars, trạng thái đạt/chưa đạt, ngày xuất. |
| **Epic** | EP-03: Student Portal |

---

### US-16: Xem Lịch sử Điểm (Grade Timeline)

**As a** Student,
**I want to** view a chronological list of all my graded assessments,
**so that** I can track my progress over time and see which assessments contributed to each CLO.

| Attribute | Detail |
|-----------|--------|
| **Priority** | Could Have |
| **Acceptance Criteria** | Danh sách điểm sắp xếp theo thời gian (mới nhất/ cũ nhất / điểm cao nhất). Màu sắc theo grade band. Filter theo môn học, loại bài kiểm tra. |
| **Epic** | EP-03: Student Portal |

---

## Phụ lục: Epic Mapping

| Epic ID | Tên Epic | User Stories |
|---------|---------|-------------|
| EP-01 | System Administration | US-01, US-02, US-03, US-04, US-05, US-06 |
| EP-02 | Teaching Management | US-07, US-08, US-09, US-10, US-11 |
| EP-03 | Student Portal | US-12, US-13, US-14, US-15, US-16 |

---

## Phụ lục: MoSCoW Classification

| Priority | User Stories |
|----------|-------------|
| **Must Have** | US-01, US-02, US-03, US-04, US-05, US-07, US-08, US-09, US-10, US-12, US-13, US-14 |
| **Should Have** | US-06, US-11, US-15 |
| **Could Have** | US-16 |
| **Won't Have** | (none at this stage) |

---

## Phụ lục: Acceptance Test Summary

| US | Key Test Scenario |
|----|-----------------|
| US-01 | Tạo program -> hiển thị trong bảng. Thử xóa program có môn -> bị chặn. |
| US-02 | Tạo PLO trùng mã -> bị chặn. Xóa PLO đang map -> bị chặn. |
| US-03 | Phân công trùng (cùng GV + môn + HK) -> bị chặn. |
| US-05 | Chọn program -> báo cáo hiển thị đúng PLO + top 10 SV. |
| US-10 | Nhập điểm -> tự lưu sau 600ms -> attainment tự động cập nhật. |
| US-13 | Radar chart hiển thị đúng PLO + ngưỡng 70%. |
| US-15 | Export PDF -> tải file với đúng thông tin sinh viên. |
