<?php
require_once 'header.php';

// Nếu đã đăng nhập thì chuyển hướng về dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>

<div class="container">
    <div class="welcome-section home-content">
        <h1>Welcome to Student Management System</h1>
        <p>A comprehensive system for managing student information and academic records.</p>
        
        <div class="cta-buttons">
            <a href="login.php" class="btn">Login</a>
            <a href="register.php" class="btn">Register</a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
