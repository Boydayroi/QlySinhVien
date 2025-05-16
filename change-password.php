<?php
require_once 'header.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Lấy thông tin user hiện tại
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!password_verify($current_password, $user['password'])) {
        $error = 'Mật khẩu hiện tại không đúng!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Mật khẩu mới và xác nhận không khớp!';
    } else {
        // Update password mới
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        
        $success = 'Đổi mật khẩu thành công!';
    }
}
?>

<div class="container center-content" style="min-height:70vh;">
    <div class="form-container fade-in" style="width:100%; max-width:400px;">
        <h2 class="mb-3 text-center">Đổi mật khẩu</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="" autocomplete="off">
            <div class="form-group">
                <label for="current_password">Mật khẩu hiện tại</label>
                <input type="password" id="current_password" name="current_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="new_password">Mật khẩu mới</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required>
                <div class="password-requirements">
                    Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường và số
                </div>
                <div class="password-strength">
                    <div id="strengthBar" class="password-strength-bar" style="width:0%"></div>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-block mt-2">Đổi mật khẩu</button>
        </form>
        <div class="links mt-3 text-center">
            <a href="dashboard.php">Quay lại Dashboard</a>
        </div>
    </div>
</div>

<script>
document.getElementById('new_password').addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthBar = document.getElementById('strengthBar');
    let strength = 0;
    
    if (password.length >= 8) strength += 25;
    if (password.match(/[A-Z]/)) strength += 25;
    if (password.match(/[a-z]/)) strength += 25;
    if (password.match(/[0-9]/)) strength += 25;
    
    strengthBar.style.width = strength + '%';
    
    if (strength <= 25) {
        strengthBar.style.backgroundColor = '#ef4444';
    } else if (strength <= 50) {
        strengthBar.style.backgroundColor = '#f59e0b';
    } else if (strength <= 75) {
        strengthBar.style.backgroundColor = '#10b981';
    } else {
        strengthBar.style.backgroundColor = '#059669';
    }
});
</script>

<?php require_once 'footer.php'; ?> 