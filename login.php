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

<div class="container">
    <div class="login-form">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="links">
            <a href="forgot-password.php">Forgot Password?</a>
            <a href="register.php">Register</a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>