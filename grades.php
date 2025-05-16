<?php
require_once 'header.php';

// Xử lý thêm/sửa điểm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_grade']) || isset($_POST['edit_grade']))) {
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']);
    $midterm_score = floatval($_POST['midterm_score']);
    $final_score = floatval($_POST['final_score']);
    $attendance_score = floatval($_POST['attendance_score']);
    $comment = trim($_POST['comment']);
    
    // Tính điểm trung bình
    $average_score = ($midterm_score * 0.3) + ($final_score * 0.6) + ($attendance_score * 0.1);
    
    if (isset($_POST['add_grade'])) {
        $stmt = $conn->prepare("INSERT INTO grades (student_id, course_id, midterm_score, final_score, attendance_score, average_score, comment) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $course_id, $midterm_score, $final_score, $attendance_score, $average_score, $comment]);
        header('Location: grades.php?success=1'); exit;
    } else {
        $grade_id = intval($_POST['grade_id']);
        $stmt = $conn->prepare("UPDATE grades SET student_id = ?, course_id = ?, midterm_score = ?, final_score = ?, attendance_score = ?, average_score = ?, comment = ? WHERE id = ?");
        $stmt->execute([$student_id, $course_id, $midterm_score, $final_score, $attendance_score, $average_score, $comment, $grade_id]);
        header('Location: grades.php?success=2'); exit;
    }
}

// Xử lý xóa điểm
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM grades WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: grades.php?success=3'); exit;
}

// Xử lý tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

$where = [];
$params = [];

if ($search) {
    $where[] = "(s.student_code LIKE ? OR s.full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($class_id) {
    $where[] = "sc.class_id = ?";
    $params[] = $class_id;
}

if ($course_id) {
    $where[] = "g.course_id = ?";
    $params[] = $course_id;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Lấy danh sách điểm
$query = "SELECT g.*, s.student_code, s.full_name as student_name, 
                 c.course_code, c.course_name, cl.class_name
          FROM grades g
          JOIN students s ON g.student_id = s.id
          JOIN courses c ON g.course_id = c.id
          LEFT JOIN student_classes sc ON s.id = sc.student_id
          LEFT JOIN classes cl ON sc.class_id = cl.id
          $where_clause
          ORDER BY g.id DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$grades = $stmt->fetchAll();

// Lấy danh sách lớp học cho filter
$stmt = $conn->query("SELECT * FROM classes ORDER BY class_name");
$classes = $stmt->fetchAll();

// Lấy danh sách khóa học cho filter
$stmt = $conn->query("SELECT * FROM courses ORDER BY course_name");
$courses = $stmt->fetchAll();

// Lấy thông tin điểm để sửa
$edit_grade = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM grades WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_grade = $stmt->fetch();
}

// Lấy danh sách sinh viên cho form thêm/sửa
$stmt = $conn->query("SELECT id, student_code, full_name FROM students ORDER BY full_name");
$students = $stmt->fetchAll();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý điểm</h2>
        <button class="btn btn-primary" onclick="showAddForm()">
            <i class="fas fa-plus"></i> Nhập điểm mới
        </button>
    </div>

    <!-- Form tìm kiếm và lọc -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm theo mã SV hoặc tên..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="class_id" class="form-select">
                        <option value="">Tất cả lớp học</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo $class_id == $class['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="course_id" class="form-select">
                        <option value="">Tất cả khóa học</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>" <?php echo $course_id == $course['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
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
                case 1: echo "Nhập điểm thành công!"; break;
                case 2: echo "Cập nhật điểm thành công!"; break;
                case 3: echo "Xóa điểm thành công!"; break;
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form thêm/sửa điểm -->
    <div class="card mb-4" id="gradeForm" style="display: <?php echo $edit_grade ? 'block' : 'none'; ?>">
        <div class="card-body">
            <h3 class="card-title mb-4"><?php echo $edit_grade ? 'Sửa điểm' : 'Nhập điểm mới'; ?></h3>
            <form method="POST" action="" autocomplete="off">
                <?php if ($edit_grade): ?>
                    <input type="hidden" name="grade_id" value="<?php echo $edit_grade['id']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="student_id" class="form-label">Sinh viên</label>
                        <select id="student_id" name="student_id" class="form-select" required>
                            <option value="">Chọn sinh viên...</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>" 
                                    <?php echo $edit_grade && $edit_grade['student_id'] == $student['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['student_code'] . ' - ' . $student['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="course_id" class="form-label">Khóa học</label>
                        <select id="course_id" name="course_id" class="form-select" required>
                            <option value="">Chọn khóa học...</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>"
                                    <?php echo $edit_grade && $edit_grade['course_id'] == $course['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="midterm_score" class="form-label">Điểm giữa kỳ (30%)</label>
                        <input type="number" id="midterm_score" name="midterm_score" class="form-control" required
                               min="0" max="10" step="0.1"
                               value="<?php echo $edit_grade ? $edit_grade['midterm_score'] : ''; ?>">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="final_score" class="form-label">Điểm cuối kỳ (60%)</label>
                        <input type="number" id="final_score" name="final_score" class="form-control" required
                               min="0" max="10" step="0.1"
                               value="<?php echo $edit_grade ? $edit_grade['final_score'] : ''; ?>">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="attendance_score" class="form-label">Điểm chuyên cần (10%)</label>
                        <input type="number" id="attendance_score" name="attendance_score" class="form-control" required
                               min="0" max="10" step="0.1"
                               value="<?php echo $edit_grade ? $edit_grade['attendance_score'] : ''; ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="comment" class="form-label">Nhận xét</label>
                    <textarea id="comment" name="comment" class="form-control" rows="2"><?php echo $edit_grade ? htmlspecialchars($edit_grade['comment']) : ''; ?></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" name="<?php echo $edit_grade ? 'edit_grade' : 'add_grade'; ?>" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $edit_grade ? 'Cập nhật' : 'Nhập điểm'; ?>
                    </button>
                    <?php if ($edit_grade): ?>
                        <a href="grades.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách điểm -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Mã SV</th>
                            <th>Họ và tên</th>
                            <th>Lớp</th>
                            <th>Mã MH</th>
                            <th>Tên môn học</th>
                            <th class="text-center">Giữa kỳ</th>
                            <th class="text-center">Cuối kỳ</th>
                            <th class="text-center">Chuyên cần</th>
                            <th class="text-center">Trung bình</th>
                            <th>Nhận xét</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($grades)): ?>
                            <tr>
                                <td colspan="12" class="text-center">Không tìm thấy điểm nào</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($grades as $grade): ?>
                            <tr>
                                <td class="text-center align-middle"><?php echo $grade['id']; ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($grade['student_code']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($grade['student_name']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($grade['class_name']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($grade['course_code']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                <td class="text-center align-middle"><?php echo number_format($grade['midterm_score'], 1); ?></td>
                                <td class="text-center align-middle"><?php echo number_format($grade['final_score'], 1); ?></td>
                                <td class="text-center align-middle"><?php echo number_format($grade['attendance_score'], 1); ?></td>
                                <td class="text-center align-middle">
                                    <span class="badge <?php 
                                        echo $grade['average_score'] >= 8.5 ? 'bg-success' : 
                                            ($grade['average_score'] >= 7 ? 'bg-primary' : 
                                            ($grade['average_score'] >= 5.5 ? 'bg-info' : 
                                            ($grade['average_score'] >= 4 ? 'bg-warning' : 'bg-danger'))); 
                                    ?>">
                                        <?php echo number_format($grade['average_score'], 1); ?>
                                    </span>
                                </td>
                                <td class="align-middle"><?php echo htmlspecialchars($grade['comment']); ?></td>
                                <td class="text-center align-middle">
                                    <div class="btn-group">
                                        <a href="?edit=<?php echo $grade['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $grade['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Bạn có chắc muốn xóa điểm này?')">
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
    document.getElementById('gradeForm').style.display = 'block';
    document.getElementById('student_id').focus();
}

// Tính điểm trung bình tự động
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', calculateAverage);
});

function calculateAverage() {
    const midterm = parseFloat(document.getElementById('midterm_score').value) || 0;
    const final = parseFloat(document.getElementById('final_score').value) || 0;
    const attendance = parseFloat(document.getElementById('attendance_score').value) || 0;
    
    const average = (midterm * 0.3) + (final * 0.6) + (attendance * 0.1);
    console.log('Điểm trung bình:', average.toFixed(1));
}
</script>

<?php require_once 'footer.php'; ?> 