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
    $student_code = $_POST['student_code'] ?? '';
    $dob = $_POST['dob'] ?? null;
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
    $change_password_ok = true;
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $error = 'Mật khẩu xác nhận không khớp!';
            $change_password_ok = false;
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            $success = 'Đổi mật khẩu thành công!';
        }
    }

    // Chỉ update thông tin cá nhân nếu không có lỗi đổi mật khẩu
    if ($change_password_ok) {
        $stmt = $conn->prepare("UPDATE students SET full_name = ?, student_code = ?, dob = ?, gender = ?, address = ?, phone = ?, avatar = ? WHERE user_id = ?");
        $stmt->execute([$full_name, $student_code, $dob, $gender, $address, $phone, $avatar, $user_id]);
        if (empty($success)) $success = 'Cập nhật thông tin thành công!';
        // Lấy lại dữ liệu mới nhất từ DB
        $stmt = $conn->prepare("SELECT u.username, u.email, s.* FROM users u LEFT JOIN students s ON u.id = s.user_id WHERE u.id = ?");
        $stmt->execute([$user_id]);
        $student = $stmt->fetch();
    }
}
?>
<div class="container center-content" style="min-height:70vh;">
    <div class="form-container fade-in" style="max-width:500px;">
        <h2 class="mb-3 text-center">Thông tin cá nhân</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data" autocomplete="off">
            <div style="text-align:center;margin-bottom:1.2rem;">
                <div class="avatar-upload-wrap" style="display:inline-block;">
                    <img src="<?php echo $student['avatar'] ? $student['avatar'] : 'asset/default-avatar.png'; ?>" alt="Avatar" />
                    <input type="file" id="avatar" name="avatar" accept="image/*" style="margin-top:0.5rem;">
                </div>
            </div>
            <div class="form-group">
                <label for="full_name">Họ và tên</label>
                <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($student['full_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="student_code">Mã sinh viên</label>
                <input type="text" id="student_code" name="student_code" class="form-control" value="<?php echo htmlspecialchars($student['student_code'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="dob">Ngày sinh</label>
                <input type="date" id="dob" name="dob" class="form-control" value="<?php echo htmlspecialchars($student['dob'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="gender">Giới tính</label>
                <select id="gender" name="gender" class="form-control">
                    <option value="">Chọn</option>
                    <option value="male" <?php if(($student['gender'] ?? '')=='male') echo 'selected'; ?>>Nam</option>
                    <option value="female" <?php if(($student['gender'] ?? '')=='female') echo 'selected'; ?>>Nữ</option>
                    <option value="other" <?php if(($student['gender'] ?? '')=='other') echo 'selected'; ?>>Khác</option>
                </select>
            </div>
            <div class="form-group">
                <label for="address">Địa chỉ</label>
                <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($student['address'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
            </div>
            <hr>
            <div class="form-group">
                <label for="password">Mật khẩu mới</label>
                <input type="password" id="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control">
            </div>
            <button type="submit" class="btn btn-block mt-2">Cập nhật</button>
        </form>
    </div>
</div>
<?php require_once 'footer.php'; ?>
