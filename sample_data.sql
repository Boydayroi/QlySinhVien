-- Xóa dữ liệu cũ nếu có
DELETE FROM notifications;
DELETE FROM grades;
DELETE FROM teacher_courses;
DELETE FROM student_classes;
DELETE FROM students;
DELETE FROM teachers;
DELETE FROM courses;
DELETE FROM classes;
DELETE FROM password_resets;
DELETE FROM users;

-- Reset auto increment
ALTER TABLE notifications AUTO_INCREMENT = 1;
ALTER TABLE grades AUTO_INCREMENT = 1;
ALTER TABLE teacher_courses AUTO_INCREMENT = 1;
ALTER TABLE student_classes AUTO_INCREMENT = 1;
ALTER TABLE students AUTO_INCREMENT = 1;
ALTER TABLE teachers AUTO_INCREMENT = 1;
ALTER TABLE courses AUTO_INCREMENT = 1;
ALTER TABLE classes AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;

-- Thêm tài khoản admin và sinh viên (mật khẩu: password)
INSERT INTO users (username, password, email, role, is_verified, created_at) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'admin', 1, NOW()),
('student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student1@example.com', 'student', 1, NOW()),
('student2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student2@example.com', 'student', 1, NOW()),
('student3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student3@example.com', 'student', 1, NOW()),
('teacher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher1@example.com', 'teacher', 1, NOW()),
('teacher2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher2@example.com', 'teacher', 1, NOW());

-- Thêm sinh viên
INSERT INTO students (user_id, full_name, student_id, date_of_birth, gender, address, phone, avatar) VALUES
(2, 'Nguyễn Văn A', 'SV001', '2000-01-15', 'male', '123 Đường ABC, Quận 1, TP.HCM', '0123456789', NULL),
(3, 'Trần Thị B', 'SV002', '2000-03-20', 'female', '456 Đường XYZ, Quận 2, TP.HCM', '0987654321', NULL),
(4, 'Lê Văn C', 'SV003', '2000-05-10', 'male', '789 Đường DEF, Quận 3, TP.HCM', '0369852147', NULL);

-- Thêm giảng viên
INSERT INTO teachers (teacher_code, full_name, email, phone, specialization, degree) VALUES
('', 'Nguyễn Văn Giảng', 'giangnv@example.com', '0123456789', 'Công nghệ thông tin', NULL),
('', 'Trần Thị Dạy', 'daytt@example.com', '0987654321', 'Điện tử', NULL);

-- Thêm lớp học
INSERT INTO classes (class_name, description) VALUES
('Lớp CNTT K1', 'Lớp Công nghệ thông tin Khóa 1'),
('Lớp CNTT K2', 'Lớp Công nghệ thông tin Khóa 2'),
('Lớp Điện tử K1', 'Lớp Điện tử Khóa 1');

-- Thêm sinh viên vào lớp
INSERT INTO student_classes (student_id, class_id) VALUES
(1, 1), (2, 1), (3, 2);

-- Thêm môn học
INSERT INTO courses (course_code, course_name, credits, description) VALUES
('WEB101', 'Lập trình Web', 3, 'Học về HTML, CSS, JavaScript và PHP'),
('DB101', 'Cơ sở dữ liệu', 3, 'Học về SQL và quản lý cơ sở dữ liệu'),
('JAVA101', 'Lập trình Java', 4, 'Học về lập trình hướng đối tượng với Java'),
('NET101', 'Lập trình .NET', 4, 'Học về lập trình C# và .NET Framework');

-- Phân công giảng viên dạy môn
INSERT INTO teacher_courses (teacher_id, course_id) VALUES
(1, 1), (1, 2), (2, 3), (2, 4);

-- Thêm điểm số
INSERT INTO grades (student_id, course_id, midterm_score, final_score, attendance_score, average_score, comment) VALUES
(1, 1, 8.5, 9.0, 9.5, 8.9, 'Học tập tốt'),
(1, 2, 7.5, 8.0, 9.0, 8.0, 'Cần cải thiện'),
(2, 1, 9.0, 9.5, 9.0, 9.3, 'Xuất sắc'),
(2, 3, 8.0, 8.5, 9.0, 8.4, 'Học tập tốt'),
(3, 2, 9.5, 9.0, 9.5, 9.3, 'Xuất sắc'),
(3, 4, 8.5, 9.0, 9.0, 8.9, 'Học tập tốt');

-- Thêm thông báo
INSERT INTO notifications (title, content, created_at) VALUES
('Thông báo nghỉ học', 'Ngày mai lớp CNTT K1 được nghỉ học', NOW()),
('Thông báo thi cuối kỳ', 'Lịch thi cuối kỳ sẽ được công bố vào tuần sau', NOW()),
('Thông báo học phí', 'Hạn nộp học phí học kỳ 1: 30/06/2024', NOW()),
('Thông báo đăng ký môn học', 'Thời gian đăng ký môn học học kỳ 2: 01/01/2024 - 15/01/2024', NOW()); 