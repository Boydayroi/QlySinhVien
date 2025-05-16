dựadựa<?php
require_once 'header.php';

// Xử lý thêm khóa học
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);
    $credits = intval($_POST['credits']);
    $description = trim($_POST['description']);
    
    if ($course_code && $course_name) {
        $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, credits, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$course_code, $course_name, $credits, $description]);
        header('Location: courses.php?success=1'); exit;
    }
}

// Xử lý sửa khóa học
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_course'])) {
    $id = intval($_POST['course_id']);
    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);
    $credits = intval($_POST['credits']);
    $description = trim($_POST['description']);
    
    if ($course_code && $course_name) {
        $stmt = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ?, credits = ?, description = ? WHERE id = ?");
        $stmt->execute([$course_code, $course_name, $credits, $description, $id]);
        header('Location: courses.php?success=2'); exit;
    }
}

// Xử lý xóa khóa học
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Kiểm tra xem khóa học có được phân công cho giáo viên không
    $stmt = $conn->prepare("SELECT COUNT(*) FROM teacher_courses WHERE course_id = ?");
    $stmt->execute([$id]);
    $teacher_count = $stmt->fetchColumn();
    
    if ($teacher_count > 0) {
        header('Location: courses.php?error=1'); exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: courses.php?success=3'); exit;
}

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
$params = [];

if ($search) {
    $where = "WHERE course_code LIKE ? OR course_name LIKE ? OR description LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Lấy danh sách khóa học với số lượng giáo viên
$query = "SELECT c.*, COUNT(tc.teacher_id) as teacher_count 
          FROM courses c 
          LEFT JOIN teacher_courses tc ON c.id = tc.course_id 
          $where 
          GROUP BY c.id 
          ORDER BY c.id DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Lấy thông tin khóa học để sửa
$edit_course = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_course = $stmt->fetch();
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý khóa học</h2>
        <button class="btn btn-primary" onclick="showAddForm()">
            <i class="fas fa-plus"></i> Thêm khóa học mới
        </button>
    </div>

    <!-- Form tìm kiếm -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo mã, tên hoặc mô tả khóa học..." value="<?php echo htmlspecialchars($search); ?>">
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
                case 1: echo "Thêm khóa học thành công!"; break;
                case 2: echo "Cập nhật khóa học thành công!"; break;
                case 3: echo "Xóa khóa học thành công!"; break;
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Không thể xóa khóa học này vì đang được phân công cho giáo viên!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form thêm/sửa khóa học -->
    <div class="card mb-4" id="courseForm" style="display: <?php echo $edit_course ? 'block' : 'none'; ?>">
        <div class="card-body">
            <h3 class="card-title mb-4"><?php echo $edit_course ? 'Sửa khóa học' : 'Thêm khóa học mới'; ?></h3>
            <form method="POST" action="" autocomplete="off">
                <?php if ($edit_course): ?>
                    <input type="hidden" name="course_id" value="<?php echo $edit_course['id']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="course_code" class="form-label">Mã khóa học</label>
                        <input type="text" id="course_code" name="course_code" class="form-control" required
                               value="<?php echo $edit_course ? htmlspecialchars($edit_course['course_code']) : ''; ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="course_name" class="form-label">Tên khóa học</label>
                        <input type="text" id="course_name" name="course_name" class="form-control" required
                               value="<?php echo $edit_course ? htmlspecialchars($edit_course['course_name']) : ''; ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="credits" class="form-label">Số tín chỉ</label>
                        <input type="number" id="credits" name="credits" class="form-control" required min="1" max="10"
                               value="<?php echo $edit_course ? $edit_course['credits'] : ''; ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea id="description" name="description" class="form-control" rows="1"><?php echo $edit_course ? htmlspecialchars($edit_course['description']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" name="<?php echo $edit_course ? 'edit_course' : 'add_course'; ?>" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $edit_course ? 'Cập nhật' : 'Thêm khóa học'; ?>
                    </button>
                    <?php if ($edit_course): ?>
                        <a href="courses.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách khóa học -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Mã khóa học</th>
                            <th>Tên khóa học</th>
                            <th class="text-center">Tín chỉ</th>
                            <th>Mô tả</th>
                            <th class="text-center">Số GV</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($courses)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Không tìm thấy khóa học nào</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                            <tr>
                                <td class="text-center align-middle"><?php echo $course['id']; ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td class="text-center align-middle"><?php echo $course['credits']; ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($course['description']); ?></td>
                                <td class="text-center align-middle">
                                    <span class="badge bg-primary"><?php echo $course['teacher_count']; ?></span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="btn-group">
                                        <a href="?edit=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $course['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Bạn có chắc muốn xóa khóa học này?')">
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
    document.getElementById('courseForm').style.display = 'block';
    document.getElementById('course_code').focus();
}
</script>

<?php require_once 'footer.php'; ?> 