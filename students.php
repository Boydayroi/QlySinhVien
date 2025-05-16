<?php
require_once 'header.php';

// Xử lý tìm kiếm
$search_field = $_GET['search_field'] ?? '';
$search_value = trim($_GET['search_value'] ?? '');

$where = "u.role = 'student'";
$params = [];
if ($search_field && $search_value) {
    if ($search_field === 'id') {
        $where .= " AND u.id = ?";
        $params[] = $search_value;
    } elseif ($search_field === 'full_name') {
        $where .= " AND s.full_name LIKE ?";
        $params[] = "%$search_value%";
    } elseif ($search_field === 'email') {
        $where .= " AND u.email LIKE ?";
        $params[] = "%$search_value%";
    } elseif ($search_field === 'student_id') {
        $where .= " AND s.student_id LIKE ?";
        $params[] = "%$search_value%";
    } elseif ($search_field === 'phone') {
        $where .= " AND s.phone LIKE ?";
        $params[] = "%$search_value%";
    }
}

// Lấy danh sách sinh viên
$sql = "SELECT u.id AS user_id, u.username, u.email, u.role, s.full_name, s.student_id, s.date_of_birth, s.gender, s.address, s.phone 
        FROM users u 
        LEFT JOIN students s ON u.id = s.user_id 
        WHERE $where";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>

<div class="container">
    <h2 class="text-center mb-4">Manage Students</h2>
    <form method="GET" action="" style="margin-bottom:2rem; display:flex; gap:1rem; justify-content:center; align-items:center; flex-wrap:wrap;">
        <select name="search_field" class="form-control" style="max-width:180px;">
            <option value="">Tìm theo...</option>
            <option value="id" <?php if($search_field=='id') echo 'selected'; ?>>ID</option>
            <option value="full_name" <?php if($search_field=='full_name') echo 'selected'; ?>>Tên</option>
            <option value="email" <?php if($search_field=='email') echo 'selected'; ?>>Email</option>
            <option value="student_id" <?php if($search_field=='student_id') echo 'selected'; ?>>Mã sinh viên</option>
            <option value="phone" <?php if($search_field=='phone') echo 'selected'; ?>>Số điện thoại</option>
        </select>
        <input type="text" name="search_value" class="form-control" style="max-width:260px;" placeholder="Nhập nội dung tìm kiếm..." value="<?php echo htmlspecialchars($search_value); ?>">
        <button type="submit" class="btn">Tìm kiếm</button>
        <?php if($search_field && $search_value): ?>
            <a href="students.php" class="btn btn-delete" style="background:#e5e7eb;color:#222;">Xóa lọc</a>
        <?php endif; ?>
    </form>
    <div class="table-responsive">
    <table class="students-table table" style="min-width:1100px;">
        <thead>
            <tr>
                <th class="text-center">ID</th>
                <th class="text-center">Username</th>
                <th class="text-center" style="max-width:220px;">Email</th>
                <th class="text-center" style="max-width:180px;">Full Name</th>
                <th class="text-center">Student ID</th>
                <th class="text-center">Date of Birth</th>
                <th class="text-center">Gender</th>
                <th class="text-center" style="max-width:120px;">Address</th>
                <th class="text-center">Phone</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td class="text-center align-middle"><?php echo htmlspecialchars($student['user_id']); ?></td>
                    <td class="text-center align-middle"><?php echo htmlspecialchars($student['username']); ?></td>
                    <td class="align-middle" style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"> <?php echo htmlspecialchars($student['email']); ?> </td>
                    <td class="align-middle" style="max-width:180px; white-space:normal; word-break:break-word; overflow:hidden; text-overflow:ellipsis;"> <?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?> </td>
                    <td class="text-center align-middle"><?php echo htmlspecialchars($student['student_id'] ?? 'N/A'); ?></td>
                    <td class="text-center align-middle"><?php echo htmlspecialchars($student['date_of_birth'] ?? ''); ?></td>
                    <td class="text-center align-middle"><?php echo htmlspecialchars($student['gender'] ?? ''); ?></td>
                    <td class="align-middle" style="max-width:120px; white-space:pre-line; word-break:break-word;"> <?php echo htmlspecialchars($student['address'] ?? ''); ?> </td>
                    <td class="text-center align-middle"><?php echo htmlspecialchars($student['phone'] ?? ''); ?></td>
                    <td class="text-center align-middle">
                        <div class="action-buttons" style="display:flex; gap:0.5rem; justify-content:center; flex-wrap:wrap;">
                            <a href="edit-student.php?id=<?php echo $student['user_id']; ?>" class="btn btn-edit">Edit</a>
                            <a href="delete.php?id=<?php echo $student['user_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <style>
    .students-table td, .students-table th {
        vertical-align: middle;
    }
    .students-table td {
        line-height: 1.5;
        padding-top: 1.2rem;
        padding-bottom: 1.2rem;
    }
    .students-table td:nth-child(4) {
        white-space: normal !important;
        word-break: break-word !important;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px;
    }
    </style>
</div>

<?php require_once 'footer.php'; ?>
