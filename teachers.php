<?php
require_once 'header.php';

// Xử lý thêm giáo viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
    $teacher_code = trim($_POST['teacher_code']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    $degree = trim($_POST['degree']);
    
    if ($teacher_code && $full_name && $email) {
        $stmt = $conn->prepare("INSERT INTO teachers (teacher_code, full_name, email, phone, specialization, degree) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$teacher_code, $full_name, $email, $phone, $specialization, $degree]);
        header('Location: teachers.php?success=1'); exit;
    }
}

// Xử lý sửa giáo viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_teacher'])) {
    $id = intval($_POST['teacher_id']);
    $teacher_code = trim($_POST['teacher_code']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    $degree = trim($_POST['degree']);
    
    if ($teacher_code && $full_name && $email) {
        $stmt = $conn->prepare("UPDATE teachers SET teacher_code = ?, full_name = ?, email = ?, phone = ?, specialization = ?, degree = ? WHERE id = ?");
        $stmt->execute([$teacher_code, $full_name, $email, $phone, $specialization, $degree, $id]);
        header('Location: teachers.php?success=2'); exit;
    }
}

// Xử lý xóa giáo viên
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Kiểm tra xem giáo viên có được phân công khóa học không
    $stmt = $conn->prepare("SELECT COUNT(*) FROM teacher_courses WHERE teacher_id = ?");
    $stmt->execute([$id]);
    $course_count = $stmt->fetchColumn();
    
    if ($course_count > 0) {
        header('Location: teachers.php?error=1'); exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: teachers.php?success=3'); exit;
}

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
$params = [];

if ($search) {
    $where = "WHERE teacher_code LIKE ? OR full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR specialization LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"];
}

// Lấy danh sách giáo viên với số lượng khóa học
$query = "SELECT t.*, COUNT(tc.course_id) as course_count 
          FROM teachers t 
          LEFT JOIN teacher_courses tc ON t.id = tc.teacher_id 
          $where 
          GROUP BY t.id 
          ORDER BY t.id DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$teachers = $stmt->fetchAll();

// Lấy thông tin giáo viên để sửa
$edit_teacher = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_teacher = $stmt->fetch();
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý giáo viên</h2>
        <button class="btn btn-primary" onclick="showAddForm()">
            <i class="fas fa-plus"></i> Thêm giáo viên mới
        </button>
    </div>

    <!-- Form tìm kiếm -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo mã, tên, email, SĐT hoặc chuyên môn..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Thông báo -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            switch ($_GET['success']) {
                case 1: echo "Thêm giáo viên thành công!"; break;
                case 2: echo "Cập nhật thông tin giáo viên thành công!"; break;
                case 3: echo "Xóa giáo viên thành công!"; break;
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Không thể xóa giáo viên này vì đang được phân công giảng dạy!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form thêm/sửa giáo viên -->
    <div class="card mb-4" id="teacherForm" style="display: <?php echo $edit_teacher ? 'block' : 'none'; ?>">
        <div class="card-body">
            <h3 class="card-title mb-4"><?php echo $edit_teacher ? 'Sửa thông tin giáo viên' : 'Thêm giáo viên mới'; ?></h3>
            <form method="POST" action="" autocomplete="off">
                <?php if ($edit_teacher): ?>
                    <input type="hidden" name="teacher_id" value="<?php echo $edit_teacher['id']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="teacher_code" class="form-label">Mã giáo viên</label>
                        <input type="text" id="teacher_code" name="teacher_code" class="form-control" required
                               value="<?php echo $edit_teacher ? htmlspecialchars($edit_teacher['teacher_code']) : ''; ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label">Họ và tên</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required
                               value="<?php echo $edit_teacher ? htmlspecialchars($edit_teacher['full_name']) : ''; ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required
                               value="<?php echo $edit_teacher ? htmlspecialchars($edit_teacher['email']) : ''; ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               value="<?php echo $edit_teacher ? htmlspecialchars($edit_teacher['phone']) : ''; ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="specialization" class="form-label">Chuyên môn</label>
                        <input type="text" id="specialization" name="specialization" class="form-control"
                               value="<?php echo $edit_teacher ? htmlspecialchars($edit_teacher['specialization']) : ''; ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="degree" class="form-label">Học vị</label>
                        <select id="degree" name="degree" class="form-select">
                            <option value="">Chọn học vị...</option>
                            <option value="ThS" <?php echo $edit_teacher && $edit_teacher['degree'] == 'ThS' ? 'selected' : ''; ?>>Thạc sĩ</option>
                            <option value="TS" <?php echo $edit_teacher && $edit_teacher['degree'] == 'TS' ? 'selected' : ''; ?>>Tiến sĩ</option>
                            <option value="PGS.TS" <?php echo $edit_teacher && $edit_teacher['degree'] == 'PGS.TS' ? 'selected' : ''; ?>>Phó Giáo sư, Tiến sĩ</option>
                            <option value="GS.TS" <?php echo $edit_teacher && $edit_teacher['degree'] == 'GS.TS' ? 'selected' : ''; ?>>Giáo sư, Tiến sĩ</option>
                        </select>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" name="<?php echo $edit_teacher ? 'edit_teacher' : 'add_teacher'; ?>" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $edit_teacher ? 'Cập nhật' : 'Thêm giáo viên'; ?>
                    </button>
                    <?php if ($edit_teacher): ?>
                        <a href="teachers.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách giáo viên -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Mã GV</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Chuyên môn</th>
                            <th>Học vị</th>
                            <th class="text-center">Số KH</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($teachers)): ?>
                            <tr>
                                <td colspan="9" class="text-center">Không tìm thấy giáo viên nào</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($teachers as $teacher): ?>
                            <tr>
                                <td class="text-center align-middle"><?php echo $teacher['id']; ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($teacher['teacher_code']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($teacher['email']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($teacher['phone']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($teacher['specialization']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($teacher['degree']); ?></td>
                                <td class="text-center align-middle">
                                    <span class="badge bg-primary"><?php echo $teacher['course_count']; ?></span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="btn-group">
                                        <a href="?edit=<?php echo $teacher['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $teacher['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Bạn có chắc muốn xóa giáo viên này?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function showAddForm() {
    document.getElementById('teacherForm').style.display = 'block';
    document.getElementById('teacher_code').focus();
}
</script>

<?php require_once 'footer.php'; ?> 