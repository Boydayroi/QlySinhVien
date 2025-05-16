<?php
require_once 'header.php';

// Hiển thị lỗi PHP để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is admin
if (!isAdmin()) {
    redirect('login.php');
}

$error = '';
$success = '';
$user_id = $_GET['id'] ?? '';

if (empty($user_id) || !is_numeric($user_id)) {
    redirect('students.php');
}

// Get student details
$stmt = $conn->prepare("SELECT u.*, s.* FROM users u LEFT JOIN students s ON u.id = s.user_id WHERE u.id = ? AND u.role = 'student'");
$stmt->execute([$user_id]);
$student = $stmt->fetch();

if (!$student) {
    redirect('students.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $student_code = $_POST['student_id'] ?? '';
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

    // Check if username or email already exists for other users
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $user_id]);

    if ($stmt->rowCount() > 0) {
        $error = 'Username or email already exists';
    } else {
        try {
            $conn->beginTransaction();

            // Update user
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $user_id]);

            // Update student details (có avatar)
            $stmt = $conn->prepare("UPDATE students SET full_name = ?, student_id = ?, date_of_birth = ?, gender = ?, address = ?, phone = ?, avatar = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $student_code, $date_of_birth, $gender, $address, $phone, $avatar, $user_id]);

            $conn->commit();
            $success = 'Student updated successfully';

            // Refresh student data
            $stmt = $conn->prepare("SELECT u.*, s.* FROM users u LEFT JOIN students s ON u.id = s.user_id WHERE u.id = ?");
            $stmt->execute([$user_id]);
            $student = $stmt->fetch();
        } catch (Exception $e) {
            $conn->rollBack();
            $error = 'Failed to update student';
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 class="form-title">Edit Student</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="" class="edit-student-form" enctype="multipart/form-data">
            <div style="text-align:center;margin-bottom:1rem;">
                <img src="<?php echo $student['avatar'] ? $student['avatar'] : 'uploads/avatars/default.png'; ?>"
                     alt="Avatar"
                     style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:2px solid #3498db;">
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($student['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
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
            <button type="submit" class="btn btn-primary btn-block">Update Student</button>
        </form>
    </div>
</div>
<?php require_once 'footer.php'; ?>
