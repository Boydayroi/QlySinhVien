<?php
require_once 'header.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    redirect('login.php');
}

// Verify token
$stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ?");
$stmt->execute([$token]);

if ($stmt->rowCount() === 0) {
    $error = 'Invalid verification token.';
} else {
    // Update user verification status
    $stmt = $conn->prepare("UPDATE users SET is_verified = TRUE, verification_token = NULL WHERE verification_token = ?");
    $stmt->execute([$token]);
    
    $success = 'Email verified successfully. You can now login.';
}
?>

<div class="container">
    <div class="verify-email">
        <h2>Email Verification</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="links">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
