# Entity-Relationship Diagram (ERD)

> **OBE & E-Portfolio System** — 14 bảng, chuẩn 3NF, Engine InnoDB

```mermaid
erDiagram
    USERS {
        int id PK "AUTO_INCREMENT"
        varchar username UK "NOT NULL"
        varchar email UK "NOT NULL"
        varchar password "bcrypt hash"
        varchar full_name "NOT NULL"
        varchar avatar_url "NULL"
        enum role "admin|lecturer|student"
        tinyint is_active "DEFAULT 1"
        timestamp created_at
        timestamp updated_at
    }

    PROGRAMS {
        int id PK "AUTO_INCREMENT"
        varchar code UK "NOT NULL"
        varchar name "NOT NULL"
        text description "NULL"
        int admin_id FK "NOT NULL → users(id)"
        timestamp created_at
        timestamp updated_at
    }

    PLOS {
        int id PK "AUTO_INCREMENT"
        int program_id FK "NOT NULL → programs(id)"
        varchar code "NOT NULL"
        text description "NOT NULL"
        varchar category "Knowledge|Skill|Attitude"
        timestamp created_at
    }

    COURSES {
        int id PK "AUTO_INCREMENT"
        int program_id FK "NOT NULL → programs(id)"
        varchar code UK "NOT NULL"
        varchar name "NOT NULL"
        tinyint credits "DEFAULT 3"
        text description "NULL"
        timestamp created_at
        timestamp updated_at
    }

    COURSE_ASSIGNMENTS {
        int id PK "AUTO_INCREMENT"
        int course_id FK "NOT NULL → courses(id)"
        int lecturer_id FK "NOT NULL → users(id)"
        varchar semester "NOT NULL"
        tinyint is_active "DEFAULT 1"
        timestamp assigned_at
    }

    ENROLLMENTS {
        int id PK "AUTO_INCREMENT"
        int student_id FK "NOT NULL → users(id)"
        int assignment_id FK "NOT NULL → course_assignments(id)"
        timestamp enrolled_at
    }

    CLOS {
        int id PK "AUTO_INCREMENT"
        int course_id FK "NOT NULL → courses(id)"
        varchar code "NOT NULL"
        text description "NOT NULL"
        tinyint bloom_level "Bloom 1-6"
        timestamp created_at
    }

    CLO_PLO_MAPPINGS {
        int clo_id FK "NOT NULL → clos(id)"
        int plo_id FK "NOT NULL → plos(id)"
        decimal weight "0-100%"
    }

    ASSESSMENTS {
        int id PK "AUTO_INCREMENT"
        int assignment_id FK "NOT NULL → course_assignments(id)"
        varchar title "NOT NULL"
        enum type "quiz|assignment|midterm|final|project|lab"
        text description "NULL"
        decimal weight "% tổng điểm môn"
        datetime due_date "NULL"
        tinyint is_published "DEFAULT 0"
        timestamp created_at
        timestamp updated_at
    }

    RUBRICS {
        int id PK "AUTO_INCREMENT"
        int assessment_id FK "NOT NULL → assessments(id)"
        int clo_id FK "NOT NULL → clos(id)"
        varchar criteria_name "NOT NULL"
        decimal max_score "DEFAULT 10.00"
        text description "NULL"
        tinyint order_index "DEFAULT 0"
        timestamp created_at
    }

    STUDENT_SCORES {
        int id PK "AUTO_INCREMENT"
        int student_id FK "NOT NULL → users(id)"
        int rubric_id FK "NOT NULL → rubrics(id)"
        decimal score "NOT NULL"
        text feedback "NULL"
        timestamp graded_at
    }

    CLO_ATTAINMENTS {
        int student_id FK "NOT NULL → users(id)"
        int clo_id FK "NOT NULL → clos(id)"
        decimal achieved_percentage "DEFAULT 0.00"
        timestamp calculated_at
    }

    PLO_ATTAINMENTS {
        int student_id FK "NOT NULL → users(id)"
        int plo_id FK "NOT NULL → plos(id)"
        decimal achieved_percentage "DEFAULT 0.00"
        timestamp calculated_at
    }

    ACTIVITY_LOGS {
        bigint id PK "AUTO_INCREMENT"
        int user_id FK "NULL → users(id)"
        varchar action "NOT NULL"
        varchar entity "NULL"
        int entity_id "NULL"
        json detail "NULL"
        varchar ip_address "NULL"
        timestamp created_at
    }

    %% ─── Relationships ───────────────────────────────────────────
    USERS          ||--o{ PROGRAMS          : "quản lý (admin)"
    USERS          ||--o{ COURSE_ASSIGNMENTS: "phân công giảng dạy"
    USERS          ||--o{ ENROLLMENTS       : "đăng ký môn"
    USERS          ||--o{ STUDENT_SCORES    : "được chấm điểm"
    USERS          ||--o{ CLO_ATTAINMENTS   : "mức đạt CLO"
    USERS          ||--o{ PLO_ATTAINMENTS   : "mức đạt PLO"
    USERS          ||--o{ ACTIVITY_LOGS     : "ghi nhận hành động"

    PROGRAMS       ||--o{ PLOS              : "định nghĩa"
    PROGRAMS       ||--o{ COURSES           : "chứa"

    COURSES        ||--o{ COURSE_ASSIGNMENTS: "được phân công"

    COURSE_ASSIGNMENTS ||--o{ ENROLLMENTS : "sinh viên đăng ký"
    COURSE_ASSIGNMENTS ||--o{ ASSESSMENTS : "có bài đánh giá"

    CLOS           ||--o{ CLO_PLO_MAPPINGS  : "ánh xạ tới"
    PLOS           ||--o{ CLO_PLO_MAPPINGS  : "được ánh xạ từ"

    ASSESSMENTS    ||--o{ RUBRICS           : "có tiêu chí chấm"

    RUBRICS        ||--o{ STUDENT_SCORES    : "sinh viên đạt"

    CLOS           ||--o{ CLO_ATTAINMENTS   : "sinh viên đạt"
    CLOS           ||--o{ RUBRICS           : "được đánh giá qua"

    PLOS           ||--o{ PLO_ATTAINMENTS   : "sinh viên đạt"
```

---

## Liên kết & Cardinality

| # | Bảng 1 | Bảng 2 | Kiểu | Mô tả |
|---|--------|--------|------|-------|
| 1 | `users` | `programs` | 1:N | Admin quản lý chương trình đào tạo |
| 2 | `users` | `course_assignments` | 1:N | Giảng viên được phân công giảng dạy |
| 3 | `users` | `enrollments` | 1:N | Sinh viên đăng ký học phần |
| 4 | `users` | `student_scores` | 1:N | Sinh viên được chấm điểm theo rubric |
| 5 | `users` | `clo_attainments` | 1:N | Sinh viên có mức đạt CLO |
| 6 | `users` | `plo_attainments` | 1:N | Sinh viên có mức đạt PLO |
| 7 | `users` | `activity_logs` | 1:N | Mọi hành động được ghi log |
| 8 | `programs` | `plos` | 1:N | Chương trình có nhiều PLO |
| 9 | `programs` | `courses` | 1:N | Chương trình chứa nhiều môn học |
| 10 | `courses` | `course_assignments` | 1:N | Môn học được phân công giảng viên |
| 11 | `course_assignments` | `enrollments` | 1:N | Phân công có nhiều sinh viên đăng ký |
| 12 | `course_assignments` | `assessments` | 1:N | Phân công có nhiều bài đánh giá |
| 13 | `clos` | `clo_plo_mappings` | 1:N | CLO tham gia nhiều ánh xạ PLO |
| 14 | `plos` | `clo_plo_mappings` | 1:N | PLO được ánh xạ từ nhiều CLO |
| 15 | `assessments` | `rubrics` | 1:N | Bài đánh giá có nhiều tiêu chí |
| 16 | `clos` | `rubrics` | 1:N | CLO được đánh giá qua rubric |
| 17 | `rubrics` | `student_scores` | 1:N | Mỗi sinh viên có 1 điểm / rubric |
| 18 | `clos` | `clo_attainments` | 1:N | CLO có mức đạt cho mỗi sinh viên |
| 19 | `plos` | `plo_attainments` | 1:N | PLO có mức đạt cho mỗi sinh viên |

---

## Ràng buộc đặc biệt

| Ràng buộc | Bảng | Chi tiết |
|-----------|------|----------|
| UK | `plos` | `(program_id, code)` — không trùng mã PLO trong cùng chương trình |
| UK | `clos` | `(course_id, code)` — không trùng mã CLO trong cùng môn học |
| UK | `course_assignments` | `(course_id, lecturer_id, semester)` — mỗi GV chỉ 1 phân công/môn/học kỳ |
| UK | `enrollments` | `(student_id, assignment_id)` — sinh viên không đăng ký trùng |
| UK | `student_scores` | `(student_id, rubric_id)` — mỗi SV 1 điểm duy nhất / rubric |
| PK kép | `clo_plo_mappings` | `(clo_id, plo_id)` — mỗi cặp CLO-PLO chỉ 1 dòng |
| PK kép | `clo_attainments` | `(student_id, clo_id)` |
| PK kép | `plo_attainments` | `(student_id, plo_id)` |
| CHECK | `clo_plo_mappings` | `weight BETWEEN 0 AND 100` |
| CASCADE | hầu hết FK | Xóa cha → xóa con để giữ tính nhất quán |
| RESTRICT | `programs.admin_id` | Không xóa admin đang quản lý chương trình |
| RESTRICT | `rubrics.clo_id` | Không xóa CLO đang được rubric tham chiếu |
| SET NULL | `activity_logs.user_id` | Giữ lại log khi user bị xóa |
