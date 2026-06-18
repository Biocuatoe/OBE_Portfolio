-- ================================================================
-- OBE & E-PORTFOLIO SYSTEM - DATABASE SCHEMA
-- Version: 2.0 | Standard: 3NF | Engine: InnoDB/MyPHPAdmin
-- ================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `obe_portfolio`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `obe_portfolio`;

-- ----------------------------------------------------------------
-- 1. USERS & AUTHENTICATION
-- ----------------------------------------------------------------
CREATE TABLE `users` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(60)  NOT NULL UNIQUE,
    `email`      VARCHAR(120) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,           -- bcrypt hash
    `full_name`  VARCHAR(120) NOT NULL,
    `avatar_url` VARCHAR(255) DEFAULT NULL,
    `role`       ENUM('admin','lecturer','student') NOT NULL DEFAULT 'student',
    `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 2. PROGRAMS (Chương trình đào tạo)
-- ----------------------------------------------------------------
CREATE TABLE `programs` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`        VARCHAR(20)  NOT NULL UNIQUE,   -- VD: CNTT2024
    `name`        VARCHAR(255) NOT NULL,
    `description` TEXT         DEFAULT NULL,
    `admin_id`    INT UNSIGNED NOT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 3. PLOs (Program Learning Outcomes - Chuẩn đầu ra chương trình)
-- ----------------------------------------------------------------
CREATE TABLE `plos` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `program_id`  INT UNSIGNED NOT NULL,
    `code`        VARCHAR(10)  NOT NULL,           -- VD: PLO1
    `description` TEXT         NOT NULL,
    `category`    VARCHAR(60)  DEFAULT NULL,       -- VD: Knowledge / Skill / Attitude
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_program_plo_code` (`program_id`, `code`),
    FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 4. COURSES (Môn học)
-- ----------------------------------------------------------------
CREATE TABLE `courses` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `program_id`  INT UNSIGNED NOT NULL,
    `code`        VARCHAR(20)  NOT NULL UNIQUE,   -- VD: IT3150
    `name`        VARCHAR(255) NOT NULL,
    `credits`     TINYINT UNSIGNED NOT NULL DEFAULT 3,
    `description` TEXT         DEFAULT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 5. COURSE ASSIGNMENTS (Phân công giảng viên)
-- ----------------------------------------------------------------
CREATE TABLE `course_assignments` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id`   INT UNSIGNED NOT NULL,
    `lecturer_id` INT UNSIGNED NOT NULL,
    `semester`    VARCHAR(20)  NOT NULL,           -- VD: 2024-1
    `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
    `assigned_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_assignment` (`course_id`, `lecturer_id`, `semester`),
    FOREIGN KEY (`course_id`)   REFERENCES `courses`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`lecturer_id`) REFERENCES `users`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 6. STUDENT ENROLLMENTS (Sinh viên đăng ký môn)
-- ----------------------------------------------------------------
CREATE TABLE `enrollments` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id`    INT UNSIGNED NOT NULL,
    `assignment_id` INT UNSIGNED NOT NULL,
    `enrolled_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_enrollment` (`student_id`, `assignment_id`),
    FOREIGN KEY (`student_id`)    REFERENCES `users`(`id`)               ON DELETE CASCADE,
    FOREIGN KEY (`assignment_id`) REFERENCES `course_assignments`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 7. CLOs (Course Learning Outcomes - Chuẩn đầu ra môn học)
-- ----------------------------------------------------------------
CREATE TABLE `clos` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id`   INT UNSIGNED NOT NULL,
    `code`        VARCHAR(10)  NOT NULL,           -- VD: CLO1
    `description` TEXT         NOT NULL,
    `bloom_level` TINYINT UNSIGNED DEFAULT NULL,   -- Bloom's Taxonomy 1-6
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_course_clo_code` (`course_id`, `code`),
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 8. CLO-PLO MAPPING MATRIX (Ma trận ánh xạ - CORE LOGIC)
-- ----------------------------------------------------------------
CREATE TABLE `clo_plo_mappings` (
    `clo_id`  INT UNSIGNED    NOT NULL,
    `plo_id`  INT UNSIGNED    NOT NULL,
    `weight`  DECIMAL(5,2)   NOT NULL DEFAULT 100.00,  -- % đóng góp (0-100)
    PRIMARY KEY (`clo_id`, `plo_id`),
    CONSTRAINT `chk_weight` CHECK (`weight` >= 0 AND `weight` <= 100),
    FOREIGN KEY (`clo_id`) REFERENCES `clos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plo_id`) REFERENCES `plos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 9. ASSESSMENTS (Bài kiểm tra / Đánh giá)
-- ----------------------------------------------------------------
CREATE TABLE `assessments` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `assignment_id` INT UNSIGNED NOT NULL,
    `title`         VARCHAR(255) NOT NULL,
    `type`          ENUM('quiz','assignment','midterm','final','project','lab') NOT NULL,
    `description`   TEXT         DEFAULT NULL,
    `weight`        DECIMAL(5,2) NOT NULL DEFAULT 0.00, -- % trong tổng điểm môn
    `due_date`      DATETIME     DEFAULT NULL,
    `is_published`  TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`assignment_id`) REFERENCES `course_assignments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 10. RUBRICS (Tiêu chí chấm điểm)
-- ----------------------------------------------------------------
CREATE TABLE `rubrics` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `assessment_id`  INT UNSIGNED NOT NULL,
    `clo_id`         INT UNSIGNED NOT NULL,
    `criteria_name`  VARCHAR(255) NOT NULL,
    `max_score`      DECIMAL(6,2) NOT NULL DEFAULT 10.00,
    `description`    TEXT         DEFAULT NULL,
    `order_index`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`assessment_id`) REFERENCES `assessments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`clo_id`)        REFERENCES `clos`(`id`)        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 11. STUDENT SCORES (Điểm sinh viên theo từng tiêu chí)
-- ----------------------------------------------------------------
CREATE TABLE `student_scores` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id` INT UNSIGNED NOT NULL,
    `rubric_id`  INT UNSIGNED NOT NULL,
    `score`      DECIMAL(6,2) NOT NULL,
    `feedback`   TEXT         DEFAULT NULL,
    `graded_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_student_rubric` (`student_id`, `rubric_id`),
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)    ON DELETE CASCADE,
    FOREIGN KEY (`rubric_id`)  REFERENCES `rubrics`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 12. CLO ATTAINMENT (Mức độ đạt CLO - được tính tự động)
-- ----------------------------------------------------------------
CREATE TABLE `clo_attainments` (
    `student_id`          INT UNSIGNED  NOT NULL,
    `clo_id`              INT UNSIGNED  NOT NULL,
    `achieved_percentage` DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    `calculated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`student_id`, `clo_id`),
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`clo_id`)     REFERENCES `clos`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 13. PLO ATTAINMENT REPORTS (Báo cáo đạt chuẩn PLO - E-Portfolio core)
-- ----------------------------------------------------------------
CREATE TABLE `plo_attainments` (
    `student_id`          INT UNSIGNED  NOT NULL,
    `plo_id`              INT UNSIGNED  NOT NULL,
    `achieved_percentage` DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    `calculated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`student_id`, `plo_id`),
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plo_id`)     REFERENCES `plos`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- 14. ACTIVITY LOG (Audit Trail)
-- ----------------------------------------------------------------
CREATE TABLE `activity_logs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED    DEFAULT NULL,
    `action`     VARCHAR(60)     NOT NULL,
    `entity`     VARCHAR(60)     DEFAULT NULL,
    `entity_id`  INT UNSIGNED    DEFAULT NULL,
    `detail`     JSON            DEFAULT NULL,
    `ip_address` VARCHAR(45)     DEFAULT NULL,
    `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_action` (`user_id`, `action`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- SEED DATA (Demo)
-- ================================================================
INSERT INTO `users` (`username`,`email`,`password`,`full_name`,`role`) VALUES
('admin01',   'admin@ischool.edu.vn',    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn An',    'admin'),
('lecturer01','gv01@ischool.edu.vn',     '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị Bình',    'lecturer'),
('student01', 'sv01@student.ischool.vn', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Minh Cường',    'student'),
('student02', 'sv02@student.ischool.vn', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Thùy Dương',  'student');
-- Password for all: "password"

INSERT INTO `programs` (`code`,`name`,`description`,`admin_id`) VALUES
('CNTT2024','Công nghệ Thông tin K2024','Chương trình đào tạo kỹ sư CNTT chuẩn CDIO',1);

INSERT INTO `plos` (`program_id`,`code`,`description`,`category`) VALUES
(1,'PLO1','Vận dụng kiến thức toán học, khoa học cơ bản vào lĩnh vực CNTT','Knowledge'),
(1,'PLO2','Thiết kế và lập trình ứng dụng phần mềm đáp ứng yêu cầu thực tế','Skill'),
(1,'PLO3','Làm việc nhóm hiệu quả, giao tiếp chuyên nghiệp','Attitude'),
(1,'PLO4','Tư duy phản biện và giải quyết vấn đề sáng tạo','Skill'),
(1,'PLO5','Sử dụng công cụ và công nghệ hiện đại trong phát triển phần mềm','Skill');

INSERT INTO `courses` (`program_id`,`code`,`name`,`credits`) VALUES
(1,'IT3150','Lập trình Web Nâng cao',3),
(1,'IT3200','Cơ sở dữ liệu',3);

INSERT INTO `course_assignments` (`course_id`,`lecturer_id`,`semester`) VALUES
(1,2,'2024-1'),(2,2,'2024-1');

INSERT INTO `enrollments` (`student_id`,`assignment_id`) VALUES
(3,1),(4,1),(3,2),(4,2);

INSERT INTO `clos` (`course_id`,`code`,`description`,`bloom_level`) VALUES
(1,'CLO1','Xây dựng ứng dụng web sử dụng mô hình MVC',4),
(1,'CLO2','Áp dụng kỹ thuật AJAX để tạo giao diện tương tác',5),
(1,'CLO3','Thiết kế cơ sở dữ liệu quan hệ đạt chuẩn 3NF',5),
(2,'CLO1','Viết câu truy vấn SQL phức tạp',3),
(2,'CLO2','Tối ưu hóa hiệu năng truy vấn cơ sở dữ liệu',4);

INSERT INTO `clo_plo_mappings` (`clo_id`,`plo_id`,`weight`) VALUES
(1,2,70),(1,5,30),(2,2,50),(2,4,50),(3,1,40),(3,2,60),(4,1,60),(4,4,40),(5,2,50),(5,5,50);

INSERT INTO `assessments` (`assignment_id`,`title`,`type`,`weight`,`is_published`) VALUES
(1,'Bài tập lớn - Xây dựng Website MVC','project',60.00,1),
(1,'Bài kiểm tra giữa kỳ','midterm',40.00,1);

INSERT INTO `rubrics` (`assessment_id`,`clo_id`,`criteria_name`,`max_score`,`order_index`) VALUES
(1,1,'Kiến trúc MVC đúng chuẩn, phân tầng rõ ràng',30.00,1),
(1,2,'Tính năng AJAX hoạt động mượt mà, không reload',20.00,2),
(1,3,'Cơ sở dữ liệu thiết kế chuẩn, có foreign key',20.00,3),
(1,1,'Giao diện người dùng đẹp, responsive',30.00,4),
(2,1,'Câu hỏi lý thuyết MVC',50.00,1),
(2,2,'Bài tập AJAX thực hành',50.00,2);
