<?php
require_once 'header.php';

// Xử lý thêm thông báo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notification'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $type = trim($_POST['type']);
    $target_type = trim($_POST['target_type']);
    $target_id = intval($_POST['target_id']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    if ($title && $content) {
        $stmt = $conn->prepare("INSERT INTO notifications (title, content, type, target_type, target_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $content, $type, $target_type, $target_id, $start_date, $end_date, $status]);
        header('Location: notifications.php?success=1'); exit;
    }
}

// Xử lý sửa thông báo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_notification'])) {
    $id = intval($_POST['notification_id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $type = trim($_POST['type']);
    $target_type = trim($_POST['target_type']);
    $target_id = intval($_POST['target_id']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    if ($title && $content) {
        $stmt = $conn->prepare("UPDATE notifications SET title = ?, content = ?, type = ?, target_type = ?, target_id = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $content, $type, $target_type, $target_id, $start_date, $end_date, $status, $id]);
        header('Location: notifications.php?success=2'); exit;
    }
}

// Xử lý xóa thông báo
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: notifications.php?success=3'); exit;
}

// Xử lý tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$status = isset($_GET['status']) ? intval($_GET['status']) : -1;

$where = [];
$params = [];

if ($search) {
    $where[] = "(title LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($type) {
    $where[] = "type = ?";
    $params[] = $type;
}

if ($status !== -1) {
    $where[] = "status = ?";
    $params[] = $status;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Lấy danh sách thông báo
$query = "SELECT n.*, 
                 CASE 
                    WHEN n.target_type = 'class' THEN (SELECT class_name FROM classes WHERE id = n.target_id)
                    WHEN n.target_type = 'course' THEN (SELECT course_name FROM courses WHERE id = n.target_id)
                    ELSE 'Tất cả'
                 END as target_name
          FROM notifications n
          $where_clause
          ORDER BY n.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$notifications = $stmt->fetchAll();

// Lấy thông tin thông báo để sửa
$edit_notification = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_notification = $stmt->fetch();
}

// Lấy danh sách lớp học cho target
$stmt = $conn->query("SELECT id, class_name FROM classes ORDER BY class_name");
$classes = $stmt->fetchAll();

// Lấy danh sách khóa học cho target
$stmt = $conn->query("SELECT id, course_name FROM courses ORDER BY course_name");
$courses = $stmt->fetchAll();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý thông báo</h2>
        <button class="btn btn-primary" onclick="showAddForm()">
            <i class="fas fa-plus"></i> Thêm thông báo mới
        </button>
    </div>

    <!-- Form tìm kiếm và lọc -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm theo tiêu đề hoặc nội dung..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">Tất cả loại thông báo</option>
                        <option value="info" <?php echo $type == 'info' ? 'selected' : ''; ?>>Thông tin</option>
                        <option value="warning" <?php echo $type == 'warning' ? 'selected' : ''; ?>>Cảnh báo</option>
                        <option value="success" <?php echo $type == 'success' ? 'selected' : ''; ?>>Thành công</option>
                        <option value="danger" <?php echo $type == 'danger' ? 'selected' : ''; ?>>Khẩn cấp</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="-1">Tất cả trạng thái</option>
                        <option value="1" <?php echo $status === 1 ? 'selected' : ''; ?>>Đang hiển thị</option>
                        <option value="0" <?php echo $status === 0 ? 'selected' : ''; ?>>Đã ẩn</option>
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
                case 1: echo "Thêm thông báo thành công!"; break;
                case 2: echo "Cập nhật thông báo thành công!"; break;
                case 3: echo "Xóa thông báo thành công!"; break;
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form thêm/sửa thông báo -->
    <div class="card mb-4" id="notificationForm" style="display: <?php echo $edit_notification ? 'block' : 'none'; ?>">
        <div class="card-body">
            <h3 class="card-title mb-4"><?php echo $edit_notification ? 'Sửa thông báo' : 'Thêm thông báo mới'; ?></h3>
            <form method="POST" action="" autocomplete="off">
                <?php if ($edit_notification): ?>
                    <input type="hidden" name="notification_id" value="<?php echo $edit_notification['id']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="title" class="form-label">Tiêu đề</label>
                        <input type="text" id="title" name="title" class="form-control" required
                               value="<?php echo $edit_notification ? htmlspecialchars($edit_notification['title']) : ''; ?>">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="type" class="form-label">Loại thông báo</label>
                        <select id="type" name="type" class="form-select" required>
                            <option value="info" <?php echo $edit_notification && $edit_notification['type'] == 'info' ? 'selected' : ''; ?>>Thông tin</option>
                            <option value="warning" <?php echo $edit_notification && $edit_notification['type'] == 'warning' ? 'selected' : ''; ?>>Cảnh báo</option>
                            <option value="success" <?php echo $edit_notification && $edit_notification['type'] == 'success' ? 'selected' : ''; ?>>Thành công</option>
                            <option value="danger" <?php echo $edit_notification && $edit_notification['type'] == 'danger' ? 'selected' : ''; ?>>Khẩn cấp</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label">Nội dung</label>
                    <textarea id="content" name="content" class="form-control" rows="4" required><?php echo $edit_notification ? htmlspecialchars($edit_notification['content']) : ''; ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="target_type" class="form-label">Đối tượng nhận</label>
                        <select id="target_type" name="target_type" class="form-select" required onchange="updateTargetOptions()">
                            <option value="all" <?php echo $edit_notification && $edit_notification['target_type'] == 'all' ? 'selected' : ''; ?>>Tất cả</option>
                            <option value="class" <?php echo $edit_notification && $edit_notification['target_type'] == 'class' ? 'selected' : ''; ?>>Lớp học</option>
                            <option value="course" <?php echo $edit_notification && $edit_notification['target_type'] == 'course' ? 'selected' : ''; ?>>Khóa học</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="target_id" class="form-label">Chọn đối tượng</label>
                        <select id="target_id" name="target_id" class="form-select">
                            <option value="0">-- Chọn --</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Trạng thái</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="status" name="status" 
                                   <?php echo $edit_notification && $edit_notification['status'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="status">Hiển thị</label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Ngày bắt đầu</label>
                        <input type="datetime-local" id="start_date" name="start_date" class="form-control" required
                               value="<?php echo $edit_notification ? date('Y-m-d\TH:i', strtotime($edit_notification['start_date'])) : ''; ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">Ngày kết thúc</label>
                        <input type="datetime-local" id="end_date" name="end_date" class="form-control" required
                               value="<?php echo $edit_notification ? date('Y-m-d\TH:i', strtotime($edit_notification['end_date'])) : ''; ?>">
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" name="<?php echo $edit_notification ? 'edit_notification' : 'add_notification'; ?>" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $edit_notification ? 'Cập nhật' : 'Thêm thông báo'; ?>
                    </button>
                    <?php if ($edit_notification): ?>
                        <a href="notifications.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách thông báo -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Tiêu đề</th>
                            <th>Loại</th>
                            <th>Đối tượng</th>
                            <th>Thời gian</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($notifications)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Không tìm thấy thông báo nào</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                            <tr>
                                <td class="text-center align-middle"><?php echo $notification['id']; ?></td>
                                <td class="align-middle">
                                    <div class="fw-bold"><?php echo htmlspecialchars($notification['title']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars(substr($notification['content'], 0, 100)) . '...'; ?></small>
                                </td>
                                <td class="align-middle">
                                    <span class="badge bg-<?php echo $notification['type']; ?>">
                                        <?php
                                        switch ($notification['type']) {
                                            case 'info': echo 'Thông tin'; break;
                                            case 'warning': echo 'Cảnh báo'; break;
                                            case 'success': echo 'Thành công'; break;
                                            case 'danger': echo 'Khẩn cấp'; break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="align-middle"><?php echo htmlspecialchars($notification['target_name']); ?></td>
                                <td class="align-middle">
                                    <div>Từ: <?php echo date('d/m/Y H:i', strtotime($notification['start_date'])); ?></div>
                                    <div>Đến: <?php echo date('d/m/Y H:i', strtotime($notification['end_date'])); ?></div>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge <?php echo $notification['status'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $notification['status'] ? 'Đang hiển thị' : 'Đã ẩn'; ?>
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="btn-group">
                                        <a href="?edit=<?php echo $notification['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $notification['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Bạn có chắc muốn xóa thông báo này?')">
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
// Dữ liệu cho select box
const classes = <?php echo json_encode($classes); ?>;
const courses = <?php echo json_encode($courses); ?>;

function showAddForm() {
    document.getElementById('notificationForm').style.display = 'block';
    document.getElementById('title').focus();
}

function updateTargetOptions() {
    const targetType = document.getElementById('target_type').value;
    const targetSelect = document.getElementById('target_id');
    targetSelect.innerHTML = '<option value="0">-- Chọn --</option>';
    
    if (targetType === 'class') {
        classes.forEach(cls => {
            const option = document.createElement('option');
            option.value = cls.id;
            option.textContent = cls.class_name;
            targetSelect.appendChild(option);
        });
    } else if (targetType === 'course') {
        courses.forEach(course => {
            const option = document.createElement('option');
            option.value = course.id;
            option.textContent = course.course_name;
            targetSelect.appendChild(option);
        });
    }
    
    // Nếu đang edit, set lại giá trị đã chọn
    <?php if ($edit_notification): ?>
    if (targetType === '<?php echo $edit_notification['target_type']; ?>') {
        targetSelect.value = '<?php echo $edit_notification['target_id']; ?>';
    }
    <?php endif; ?>
}

// Khởi tạo target options khi trang load
document.addEventListener('DOMContentLoaded', function() {
    updateTargetOptions();
});
</script>

<?php require_once 'footer.php'; ?> 