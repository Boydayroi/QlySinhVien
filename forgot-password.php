<?php
require_once 'header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
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
        // Kiểm tra email tồn tại
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Tạo token reset
            $reset_token = bin2hex(random_bytes(32));
            $created_at = date('Y-m-d H:i:s');

            // Xóa token cũ (nếu có)
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            // Lưu token mới vào bảng password_resets
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $reset_token, $created_at]);

            // Gửi email reset
            $reset_link = SITE_URL . "/reset-password.php?email=" . urlencode($email) . "&token=" . $reset_token;
            $email_message = "Please click the following link to reset your password: <a href='$reset_link'>$reset_link</a>";
            
            if (sendEmail($email, 'Reset Your Password', $email_message)) {
                $success = 'Password reset instructions have been sent to your email.';
            } else {
                $error = 'Failed to send reset email.';
            }
        } else {
            $error = 'Email not found.';
        }
    }
}
?>

<div class="container center-content" style="min-height:70vh;">
    <div class="form-container fade-in" style="width:100%; max-width:400px;">
        <h2 class="mb-3 text-center">Quên mật khẩu</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="" autocomplete="off">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
            <button type="submit" class="btn btn-block mt-2">Gửi yêu cầu</button>
        </form>
        <div class="links mt-3 text-center">
            <a href="login.php">Quay lại đăng nhập</a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
