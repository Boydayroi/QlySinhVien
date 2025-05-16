<?php
require_once 'header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        // Kiểm tra username/email đã tồn tại
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Tên đăng nhập hoặc email đã tồn tại!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hash]);
            redirect('login.php');
        }
    }
}
?>
<div class="container center-content" style="min-height:70vh;">
    <div class="form-container fade-in" style="width:100%; max-width:400px;">
        <h2 class="mb-3 text-center">Đăng ký</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="" autocomplete="off">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-block mt-2">Đăng ký</button>
        </form>
        <div class="links mt-3 text-center">
            <a href="login.php">Đã có tài khoản? Đăng nhập</a>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>
