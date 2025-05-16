<?php
require_once 'header.php';

// Nếu chưa đăng nhập thì chuyển về login
if (!isLoggedIn()) {
    redirect('login.php');
}

// Nếu là admin
if (isAdmin()) {
    // Get total students count
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $total_students = $stmt->fetchColumn();
    ?>
    <div class="container">
        <h2>Admin Dashboard</h2>
        <div class="dashboard-stats">
            <div class="stat-box">
                <h3>Total Students</h3>
                <p><?php echo $total_students; ?></p>
            </div>
        </div>
        <div class="dashboard-actions">
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
    ?>
    <div class="container">
        <h2>Student Dashboard</h2>
        <p>Welcome, <strong><?php echo htmlspecialchars($student['username']); ?>!</strong></p>
        <h3>Your Information</h3>
        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($student['full_name']); ?></p>
        <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($student['date_of_birth']); ?></p>
        <p><strong>Gender:</strong> <?php echo htmlspecialchars($student['gender']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($student['address']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone']); ?></p>
        <div class="dashboard-actions" style="margin-top:2rem;">
            <a href="profile.php" class="btn btn-edit">Edit Profile</a>
        </div>
    </div>
    <?php
}
?>

<?php require_once 'footer.php'; ?>
