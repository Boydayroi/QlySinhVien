<?php
require_once 'header.php';

// Hiển thị lỗi PHP để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin sinh viên
$stmt = $conn->prepare("SELECT u.username, u.email, s.* FROM users u LEFT JOIN students s ON u.id = s.user_id WHERE u.id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch();

if (!$student) {
    echo "<div class='container'><div class='alert alert-danger'>Student not found.</div></div>";
    require_once 'footer.php';
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $address = $_POST['address'] ?? null;
    $phone = $_POST['phone'] ?? null;

    // Xử lý upload ảnh đại diện mới (nếu có)
    $avatar = $student['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatar = 'uploads/avatars/' . uniqid() . '.' . $ext;
        if (!is_dir('uploads/avatars')) {
            mkdir('uploads/avatars', 0777, true);
        }
        move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar);
    }

    // Đổi mật khẩu nếu có nhập
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
        }
    }

    if (empty($error)) {
        // Update thông tin sinh viên
        $stmt = $conn->prepare("UPDATE students SET full_name = ?, student_id = ?, date_of_birth = ?, gender = ?, address = ?, phone = ?, avatar = ? WHERE user_id = ?");
        $stmt->execute([$full_name, $student_id, $date_of_birth, $gender, $address, $phone, $avatar, $user_id]);
        $success = 'Profile updated successfully.';

        // Lấy lại dữ liệu mới nhất từ DB
        $stmt = $conn->prepare("SELECT u.username, u.email, s.* FROM users u LEFT JOIN students s ON u.id = s.user_id WHERE u.id = ?");
        $stmt->execute([$user_id]);
        $student = $stmt->fetch();
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 class="form-title">Your Profile</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div style="text-align:center;margin-bottom:1rem;">
                <img src="<?php echo $student['avatar'] ? $student['avatar'] : 'uploads/avatars/default.png'; ?>"
                     alt="Avatar"
                     style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:2px solid #3498db;">
            </div>
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="student_id">Student ID:</label>
                <input type="text" id="student_id" name="student_id" class="form-control" value="<?php echo htmlspecialchars($student['student_id']); ?>" required>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($student['date_of_birth'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" class="form-control">
                    <option value="">Select</option>
                    <option value="male" <?php if(($student['gender'] ?? '')=='male') echo 'selected'; ?>>Male</option>
                    <option value="female" <?php if(($student['gender'] ?? '')=='female') echo 'selected'; ?>>Female</option>
                    <option value="other" <?php if(($student['gender'] ?? '')=='other') echo 'selected'; ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($student['address'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="avatar">Avatar:</label>
                <input type="file" id="avatar" name="avatar" class="form-control" accept="image/*">
            </div>
            <hr>
            <div class="form-group">
                <label for="password">New Password:</label>
                <input type="password" id="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Update Profile</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
