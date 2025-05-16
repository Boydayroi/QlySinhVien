<?php
require_once 'header.php';

$error = '';
$success = '';
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

if (empty($email) || empty($token)) {
    redirect('login.php');
}

// Bỏ kiểm tra thời gian hết hạn token (chỉ kiểm tra email và token)
$stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ?");
$stmt->execute([$email, $token]);
$reset = $stmt->fetch();

if (!$reset) {
    $error = 'Invalid or expired reset token.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if ($password !== $confirm_password) {
            $error = 'Mật khẩu xác nhận không khớp!';
        } else {
            // Update password cho user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $email]);
            
            // Xóa token sau khi dùng
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);
            
            $success = 'Đặt lại mật khẩu thành công!';
        }
    }
}
?>

<style>
.reset-password-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 20px;
}

.reset-password-form {
    background: white;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 500px;
}

.reset-password-form h2 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 30px;
    font-weight: 600;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #34495e;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.btn-primary {
    background: #3498db;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    width: 100%;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background: #2980b9;
}

.error {
    background: #fee2e2;
    color: #dc2626;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    border: 1px solid #fecaca;
}

.success {
    background: #dcfce7;
    color: #16a34a;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    border: 1px solid #bbf7d0;
}

.links {
    text-align: center;
    margin-top: 20px;
}

.links a {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.links a:hover {
    color: #2980b9;
    text-decoration: underline;
}

.password-requirements {
    font-size: 14px;
    color: #666;
    margin-top: 5px;
}

.password-strength {
    height: 5px;
    margin-top: 8px;
    border-radius: 3px;
    background: #e0e0e0;
}

.password-strength-bar {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
}
</style>

<div class="container center-content" style="min-height:70vh;">
    <div class="form-container fade-in" style="width:100%; max-width:400px;">
        <h2 class="mb-3 text-center">Đặt lại mật khẩu</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="" autocomplete="off">
            <div class="form-group">
                <label for="password">Mật khẩu mới</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-block mt-2">Đặt lại mật khẩu</button>
        </form>
        <div class="links mt-3 text-center">
            <a href="login.php">Quay lại đăng nhập</a>
        </div>
    </div>
</div>

<script>
document.getElementById('password').addEventListener('input', function(e) {
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
