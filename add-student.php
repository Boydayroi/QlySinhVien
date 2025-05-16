<?php
require_once 'header.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $address = $_POST['address'] ?? null;
    $phone = $_POST['phone'] ?? null;
    
    // Xử lý upload ảnh đại diện
    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatar = 'uploads/avatars/' . uniqid() . '.' . $ext;
        if (!is_dir('uploads/avatars')) {
            mkdir('uploads/avatars', 0777, true);
        }
        move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar);
    }
    
    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        $error = 'Username or email already exists';
    } else {
        try {
            $conn->beginTransaction();
            
            // Insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, is_verified) VALUES (?, ?, ?, 'student', TRUE)");
            $stmt->execute([$username, $email, $hashed_password]);
            
            $user_id = $conn->lastInsertId();
            
            // Insert student details (thêm avatar)
            $stmt = $conn->prepare("INSERT INTO students (user_id, full_name, student_id, date_of_birth, gender, address, phone, avatar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $full_name, $student_id, $date_of_birth, $gender, $address, $phone, $avatar]);
            
            $conn->commit();
            $success = 'Student added successfully';
        } catch (Exception $e) {
            $conn->rollBack();
            $error = 'Failed to add student: ' . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 class="form-title">Add New Student</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="add-student-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="student_id">Student ID:</label>
                <input type="text" id="student_id" name="student_id" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" class="form-control">
                    <option value="">Select</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="avatar">Avatar:</label>
                <input type="file" id="avatar" name="avatar" class="form-control" accept="image/*">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Add Student</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>