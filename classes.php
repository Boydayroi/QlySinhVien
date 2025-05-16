<?php
require_once 'header.php';

// Xử lý thêm lớp
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    $class_name = trim($_POST['class_name']);
    $description = trim($_POST['description']);
    if ($class_name) {
        $stmt = $conn->prepare("INSERT INTO classes (class_name, description) VALUES (?, ?)");
        $stmt->execute([$class_name, $description]);
        header('Location: classes.php?success=1'); exit;
    }
}

// Xử lý sửa lớp
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_class'])) {
    $id = intval($_POST['class_id']);
    $class_name = trim($_POST['class_name']);
    $description = trim($_POST['description']);
    if ($class_name) {
        $stmt = $conn->prepare("UPDATE classes SET class_name = ?, description = ? WHERE id = ?");
        $stmt->execute([$class_name, $description, $id]);
        header('Location: classes.php?success=2'); exit;
    }
}

// Xử lý xóa lớp
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Kiểm tra xem lớp có sinh viên không
    $stmt = $conn->prepare("SELECT COUNT(*) FROM student_classes WHERE class_id = ?");
    $stmt->execute([$id]);
    $student_count = $stmt->fetchColumn();
    
    if ($student_count > 0) {
        header('Location: classes.php?error=1'); exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: classes.php?success=3'); exit;
}

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
$params = [];

if ($search) {
    $where = "WHERE class_name LIKE ? OR description LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Lấy danh sách lớp với số lượng sinh viên
$query = "SELECT c.*, COUNT(sc.student_id) as student_count 
          FROM classes c 
          LEFT JOIN student_classes sc ON c.id = sc.class_id 
          $where 
          GROUP BY c.id 
          ORDER BY c.id DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$classes = $stmt->fetchAll();

// Lấy thông tin lớp để sửa
$edit_class = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_class = $stmt->fetch();
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý lớp học</h2>
        <button class="btn btn-primary" onclick="showAddForm()">
            <i class="fas fa-plus"></i> Thêm lớp mới
        </button>
    </div>

    <!-- Form tìm kiếm -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên lớp hoặc mô tả..." value="<?php echo htmlspecialchars($search); ?>">
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
                case 1: echo "Thêm lớp học thành công!"; break;
                case 2: echo "Cập nhật lớp học thành công!"; break;
                case 3: echo "Xóa lớp học thành công!"; break;
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Không thể xóa lớp học này vì đang có sinh viên trong lớp!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form thêm/sửa lớp -->
    <div class="card mb-4" id="classForm" style="display: <?php echo $edit_class ? 'block' : 'none'; ?>">
        <div class="card-body">
            <h3 class="card-title mb-4"><?php echo $edit_class ? 'Sửa lớp học' : 'Thêm lớp mới'; ?></h3>
            <form method="POST" action="" autocomplete="off">
                <?php if ($edit_class): ?>
                    <input type="hidden" name="class_id" value="<?php echo $edit_class['id']; ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="class_name" class="form-label">Tên lớp</label>
                    <input type="text" id="class_name" name="class_name" class="form-control" required
                           value="<?php echo $edit_class ? htmlspecialchars($edit_class['class_name']) : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea id="description" name="description" class="form-control" rows="3"><?php echo $edit_class ? htmlspecialchars($edit_class['description']) : ''; ?></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" name="<?php echo $edit_class ? 'edit_class' : 'add_class'; ?>" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $edit_class ? 'Cập nhật' : 'Thêm lớp'; ?>
                    </button>
                    <?php if ($edit_class): ?>
                        <a href="classes.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách lớp -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Tên lớp</th>
                            <th>Mô tả</th>
                            <th class="text-center">Số sinh viên</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($classes)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Không tìm thấy lớp học nào</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($classes as $class): ?>
                            <tr>
                                <td class="text-center align-middle"><?php echo $class['id']; ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($class['class_name']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($class['description']); ?></td>
                                <td class="text-center align-middle">
                                    <span class="badge bg-primary"><?php echo $class['student_count']; ?></span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="btn-group">
                                        <a href="?edit=<?php echo $class['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $class['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Bạn có chắc muốn xóa lớp này?')">
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
    document.getElementById('classForm').style.display = 'block';
    document.getElementById('class_name').focus();
}
</script>

<?php require_once 'footer.php'; ?> 