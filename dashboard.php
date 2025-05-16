<?php
require_once 'header.php';

// Nếu chưa đăng nhập thì chuyển về login
if (!isLoggedIn()) {
    redirect('login.php');
}

// Xử lý upload avatar nếu có
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $upload_dir = 'uploads/avatars/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $file = $_FILES['avatar'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($ext, $allowed) && $file['size'] < 2*1024*1024) {
        $new_name = uniqid().'.'.$ext;
        $target = $upload_dir.$new_name;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            // Cập nhật DB
            $stmt = $conn->prepare("UPDATE students SET avatar = ? WHERE user_id = ?");
            $stmt->execute([$target, $_SESSION['user_id']]);
            // Reload lại trang để cập nhật avatar mới
            echo '<script>window.location.reload();</script>';
            exit;
        }
    }
}

// Nếu là admin
if (isAdmin()) {
    // Get total students count
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $total_students = $stmt->fetchColumn();
    ?>
    <div class="container center-content">
        <h2 class="mb-4">Admin Dashboard</h2>
        <div class="dashboard-stats" style="width:100%; justify-content:center;">
            <div class="stat-card text-center">
                <h3>Total Students</h3>
                <p style="font-size:2.5rem; font-weight:700; color:var(--primary-color); margin:0;"> <?php echo $total_students; ?> </p>
            </div>
        </div>
        <div class="dashboard-actions mt-4" style="display:flex; gap:1.5rem; justify-content:center;">
            <a href="students.php" class="btn">Manage Students</a>
            <a href="add-student.php" class="btn">Add New Student</a>
        </div>
    </div>
    <?php
} else {
    // Nếu là sinh viên
    $stmt = $conn->prepare("SELECT u.*, s.* FROM users u LEFT JOIN students s ON u.id = s.user_id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch();
    $avatar = !empty($student['avatar']) ? $student['avatar'] : 'asset/default-avatar.png';
    // Lấy lớp học
    $class_name = '';
    $stmt = $conn->prepare("SELECT cl.class_name FROM student_classes sc JOIN classes cl ON sc.class_id = cl.id WHERE sc.student_id = ? LIMIT 1");
    $stmt->execute([$student['id']]);
    $row = $stmt->fetch();
    if ($row) $class_name = $row['class_name'];
    // Tính điểm trung bình và số môn đã học
    $stmt = $conn->prepare("SELECT AVG(average_score) as avg_score, COUNT(*) as total_courses FROM grades WHERE student_id = ?");
    $stmt->execute([$student['id']]);
    $grade_info = $stmt->fetch();
    $avg_score = $grade_info && $grade_info['avg_score'] !== null ? number_format($grade_info['avg_score'],2) : '--';
    $total_courses = $grade_info ? $grade_info['total_courses'] : 0;
    ?>
    <div class="container center-content">
        <h2 class="mb-4">Student Dashboard</h2>
        <div class="student-dashboard-flex" style="display:flex; gap:2.5rem; justify-content:center; align-items:stretch; flex-wrap:wrap; max-width:1050px; margin:auto;">
            <!-- Card Avatar -->
            <div class="card student-avatar-card" style="flex:0 0 240px; max-width:240px; text-align:center; padding:2.5rem 1rem 2rem 1rem; display:flex; flex-direction:column; align-items:center; justify-content:space-between; min-height:420px; position:relative;">
                <form method="POST" enctype="multipart/form-data" id="avatarForm">
                    <div class="avatar-upload-wrap" style="position:relative; display:inline-block; cursor:pointer;">
                        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" style="width:110px; height:110px; object-fit:cover; border-radius:50%; box-shadow:0 2px 12px rgba(0,0,0,0.08); border:4px solid #fff; background:#f3f4f6; margin-bottom:1.2rem;">
                        <div class="avatar-hover" style="position:absolute; top:0; left:0; width:110px; height:110px; border-radius:50%; background:rgba(0,0,0,0.35); display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.2s;">
                            <svg width="36" height="36" fill="#fff" viewBox="0 0 24 24"><path d="M12 5c-1.1 0-2 .9-2 2h-2.17c-.41 0-.77.26-.92.64l-1.24 3.09c-.11.27-.16.56-.16.86v7.41c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2v-7.41c0-.3-.05-.59-.16-.86l-1.24-3.09a1 1 0 0 0-.92-.64h-2.17c0-1.1-.9-2-2-2zm0 2c.55 0 1 .45 1 1h-2c0-.55.45-1 1-1zm-4 3.5c.83 0 1.5.67 1.5 1.5S8.83 13.5 8 13.5 6.5 12.83 6.5 12s.67-1.5 1.5-1.5zm8 0c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5z"/></svg>
                        </div>
                        <input type="file" name="avatar" accept="image/*" style="opacity:0; position:absolute; top:0; left:0; width:110px; height:110px; cursor:pointer;" onchange="document.getElementById('avatarForm').submit();">
                    </div>
                </form>
                <div style="font-weight:700; font-size:1.15rem; margin-bottom:0.25rem; margin-top:1rem;">
                    <?php echo htmlspecialchars($student['full_name'] ?? ''); ?>
                </div>
                <div style="color:#888; font-size:0.97rem; margin-bottom:0.5rem;">MSSV: <b><?php echo htmlspecialchars($student['student_code'] ?? ''); ?></b></div>
                <div style="color:var(--primary-color); font-weight:500; margin-bottom:0.5rem;">
                    <?php echo $class_name ? 'Lớp: '.htmlspecialchars($class_name) : ''; ?>
                </div>
                <div style="margin-top:1.2rem;">
                    <span style="display:inline-block; background:#f1f5f9; color:#2563eb; border-radius:8px; padding:0.3rem 0.8rem; font-size:0.98rem; font-weight:600; margin-bottom:0.2rem;">Điểm TB: <?php echo $avg_score; ?></span><br>
                    <span style="display:inline-block; background:#f1f5f9; color:#1d4ed8; border-radius:8px; padding:0.3rem 0.8rem; font-size:0.98rem; font-weight:600;">Số môn: <?php echo $total_courses; ?></span>
                </div>
            </div>
            <!-- Card Thông tin -->
            <div class="card" style="flex:1; min-width:260px; max-width:520px; display:flex; flex-direction:column; justify-content:space-between; padding-bottom:1.5rem;">
                <div class="card-body" style="padding-bottom:0;">
                    <h3 class="mb-3" style="font-size:1.35rem; font-weight:800; color:#222; text-align:left;">Thông Tin Cá Nhân</h3>
                    <div class="profile-info-list">
                        <div><span class="profile-label">Email:</span> <span><?php echo htmlspecialchars($student['email'] ?? ''); ?></span></div>
                        <div><span class="profile-label">Ngày sinh:</span> <span><?php echo htmlspecialchars($student['dob'] ?? ''); ?></span></div>
                        <div><span class="profile-label">Giới tính:</span> <span><?php echo htmlspecialchars($student['gender'] ?? ''); ?></span></div>
                        <div><span class="profile-label">Địa chỉ:</span> <span><?php echo htmlspecialchars($student['address'] ?? ''); ?></span></div>
                        <div><span class="profile-label">Số điện thoại:</span> <span><?php echo htmlspecialchars($student['phone'] ?? ''); ?></span></div>
                    </div>
                </div>
                <div class="dashboard-actions mt-3" style="display:flex; flex-wrap:wrap; gap:1rem; justify-content:center; align-items:flex-end; width:100%;">
                    <a href="profile.php" class="btn btn-edit" style="min-width:170px; font-size:1rem; padding:0.7rem 0; white-space:nowrap;">Chỉnh sửa thông tin</a>
                    <a href="change-password.php" class="btn btn-secondary" style="min-width:170px; font-size:1rem; padding:0.7rem 0; white-space:nowrap;">Đổi mật khẩu</a>
                    <a href="grades.php" class="btn btn-success" style="min-width:170px; font-size:1rem; padding:0.7rem 0; white-space:nowrap;">Xem điểm</a>
                    <a href="notifications.php" class="btn btn-warning" style="min-width:170px; font-size:1rem; padding:0.7rem 0; white-space:nowrap;">Thông báo</a>
                </div>
            </div>
        </div>
        <style>
        .avatar-upload-wrap:hover .avatar-hover { opacity:1; }
        .avatar-upload-wrap:active .avatar-hover { opacity:1; }
        @media (max-width: 1050px) {
            .student-dashboard-flex { flex-direction: column; align-items: center; gap:1.5rem !important; }
            .student-dashboard-flex .card { max-width: 100% !important; }
            .student-avatar-card { width:100% !important; max-width:350px !important; margin-bottom:1rem; }
        }
        </style>
    </div>
    <?php
}
?>

<?php require_once 'footer.php'; ?>
