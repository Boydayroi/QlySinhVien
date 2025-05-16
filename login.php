<?php
require_once 'header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    // Verify reCAPTCHA
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $recaptcha_response
    ];

    $recaptcha_options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($recaptcha_data)
        ]
    ];

    $recaptcha_context = stream_context_create($recaptcha_options);
    $recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
    $recaptcha_result = json_decode($recaptcha_result);

    if (!$recaptcha_result->success) {
        $error = 'Please complete the reCAPTCHA verification';
    } else {
        // Verify user credentials
        $stmt = $conn->prepare("SELECT id, username, password, role, is_verified FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            redirect('dashboard.php');
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>

<div class="container center-content" style="min-height:70vh;">
    <div class="form-container fade-in" style="width:100%; max-width:400px;">
        <h2 class="mb-3 text-center">Đăng nhập</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="" autocomplete="off">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="g-recaptcha mb-3" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
            <button type="submit" class="btn btn-block mt-2">Đăng nhập</button>
        </form>
        <div class="links mt-3 text-center">
            <a href="forgot-password.php">Quên mật khẩu?</a>
            <a href="register.php">Đăng ký</a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>