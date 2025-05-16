<?php
require_once 'header.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('login.php');
}

// Lấy danh sách sinh viên, đặt bí danh cho cột id của users
$stmt = $conn->query("SELECT u.id AS user_id, u.username, u.email, u.role, s.full_name, s.student_id, s.date_of_birth, s.gender, s.address, s.phone 
                      FROM users u 
                      LEFT JOIN students s ON u.id = s.user_id 
                      WHERE u.role = 'student'");
$students = $stmt->fetchAll();
?>

<div class="container">
    <h2>Manage Students</h2>
    
    <table class="students-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Full Name</th>
                <th>Student ID</th>
                <th>Date of Birth</th>
                <th>Gender</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($student['username']); ?></td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                    <td><?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($student['student_id'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($student['date_of_birth'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['gender'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['address'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['phone'] ?? ''); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="edit-student.php?id=<?php echo $student['user_id']; ?>" class="btn btn-edit">Edit</a>
                            <a href="delete.php?id=<?php echo $student['user_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>
