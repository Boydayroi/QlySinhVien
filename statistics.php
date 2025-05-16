<?php
require_once 'header.php';

// Tổng quan
$total_students = $conn->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_classes = $conn->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$total_teachers = $conn->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$total_courses = $conn->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$avg_score = $conn->query("SELECT AVG(average_score) FROM grades")->fetchColumn();

// Số lượng sinh viên theo lớp
$class_labels = [];
$class_student_counts = [];
$stmt = $conn->query("SELECT cl.class_name, COUNT(sc.student_id) as count FROM classes cl LEFT JOIN student_classes sc ON cl.id = sc.class_id GROUP BY cl.id ORDER BY cl.class_name");
foreach ($stmt as $row) {
    $class_labels[] = $row['class_name'];
    $class_student_counts[] = (int)$row['count'];
}

// Số lượng sinh viên theo khóa học
$course_labels = [];
$course_student_counts = [];
$stmt = $conn->query("SELECT c.course_name, COUNT(g.student_id) as count FROM courses c LEFT JOIN grades g ON c.id = g.course_id GROUP BY c.id ORDER BY c.course_name");
foreach ($stmt as $row) {
    $course_labels[] = $row['course_name'];
    $course_student_counts[] = (int)$row['count'];
}

// Điểm trung bình theo lớp
$class_avg_labels = [];
$class_avg_scores = [];
$stmt = $conn->query("SELECT cl.class_name, AVG(g.average_score) as avg_score FROM classes cl LEFT JOIN student_classes sc ON cl.id = sc.class_id LEFT JOIN grades g ON sc.student_id = g.student_id GROUP BY cl.id ORDER BY cl.class_name");
foreach ($stmt as $row) {
    $class_avg_labels[] = $row['class_name'];
    $class_avg_scores[] = round($row['avg_score'], 2);
}

// Điểm trung bình theo môn học
$course_avg_labels = [];
$course_avg_scores = [];
$stmt = $conn->query("SELECT c.course_name, AVG(g.average_score) as avg_score FROM courses c LEFT JOIN grades g ON c.id = g.course_id GROUP BY c.id ORDER BY c.course_name");
foreach ($stmt as $row) {
    $course_avg_labels[] = $row['course_name'];
    $course_avg_scores[] = round($row['avg_score'], 2);
}

// Giáo viên theo chuyên môn
$specialization_labels = [];
$specialization_counts = [];
$stmt = $conn->query("SELECT specialization, COUNT(*) as count FROM teachers GROUP BY specialization ORDER BY count DESC");
foreach ($stmt as $row) {
    $specialization_labels[] = $row['specialization'] ?: 'Khác';
    $specialization_counts[] = (int)$row['count'];
}

// Top sinh viên xuất sắc
$top_students = $conn->query("SELECT s.student_code, s.full_name, AVG(g.average_score) as avg_score FROM students s JOIN grades g ON s.id = g.student_id GROUP BY s.id ORDER BY avg_score DESC LIMIT 5")->fetchAll();
// Lớp có điểm TB cao nhất/thấp nhất
$top_classes = $conn->query("SELECT cl.class_name, AVG(g.average_score) as avg_score FROM classes cl LEFT JOIN student_classes sc ON cl.id = sc.class_id LEFT JOIN grades g ON sc.student_id = g.student_id GROUP BY cl.id HAVING avg_score IS NOT NULL ORDER BY avg_score DESC LIMIT 3")->fetchAll();
$low_classes = $conn->query("SELECT cl.class_name, AVG(g.average_score) as avg_score FROM classes cl LEFT JOIN student_classes sc ON cl.id = sc.class_id LEFT JOIN grades g ON sc.student_id = g.student_id GROUP BY cl.id HAVING avg_score IS NOT NULL ORDER BY avg_score ASC LIMIT 3")->fetchAll();
?>

<div class="container">
    <h2 class="mb-4">Thống kê & Báo cáo</h2>
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-2 fw-bold text-primary"><?php echo $total_students; ?></div>
                    <div>Sinh viên</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-2 fw-bold text-success"><?php echo $total_classes; ?></div>
                    <div>Lớp học</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-2 fw-bold text-warning"><?php echo $total_teachers; ?></div>
                    <div>Giáo viên</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-2 fw-bold text-info"><?php echo $total_courses; ?></div>
                    <div>Khóa học</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="fs-2 fw-bold text-danger"><?php echo number_format($avg_score, 2); ?></div>
                    <div>Điểm TB toàn trường</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header fw-bold">Số lượng sinh viên theo lớp</div>
                <div class="card-body"><canvas id="chartClassStudent"></canvas></div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header fw-bold">Số lượng sinh viên theo khóa học</div>
                <div class="card-body"><canvas id="chartCourseStudent"></canvas></div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header fw-bold">Điểm trung bình theo lớp</div>
                <div class="card-body"><canvas id="chartClassAvg"></canvas></div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header fw-bold">Điểm trung bình theo môn học</div>
                <div class="card-body"><canvas id="chartCourseAvg"></canvas></div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header fw-bold">Giáo viên theo chuyên môn</div>
                <div class="card-body"><canvas id="chartSpecialization"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header fw-bold">Top 5 sinh viên xuất sắc</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>Mã SV</th><th>Họ tên</th><th>Điểm TB</th></tr></thead>
                        <tbody>
                        <?php foreach ($top_students as $s): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($s['student_code']); ?></td>
                                <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                                <td><?php echo number_format($s['avg_score'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-header fw-bold">Lớp điểm TB cao nhất</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>Lớp</th><th>Điểm TB</th></tr></thead>
                        <tbody>
                        <?php foreach ($top_classes as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['class_name']); ?></td>
                                <td><?php echo number_format($c['avg_score'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-header fw-bold">Lớp điểm TB thấp nhất</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>Lớp</th><th>Điểm TB</th></tr></thead>
                        <tbody>
                        <?php foreach ($low_classes as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['class_name']); ?></td>
                                <td><?php echo number_format($c['avg_score'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const classLabels = <?php echo json_encode($class_labels); ?>;
const classStudentCounts = <?php echo json_encode($class_student_counts); ?>;
const courseLabels = <?php echo json_encode($course_labels); ?>;
const courseStudentCounts = <?php echo json_encode($course_student_counts); ?>;
const classAvgLabels = <?php echo json_encode($class_avg_labels); ?>;
const classAvgScores = <?php echo json_encode($class_avg_scores); ?>;
const courseAvgLabels = <?php echo json_encode($course_avg_labels); ?>;
const courseAvgScores = <?php echo json_encode($course_avg_scores); ?>;
const specializationLabels = <?php echo json_encode($specialization_labels); ?>;
const specializationCounts = <?php echo json_encode($specialization_counts); ?>;

new Chart(document.getElementById('chartClassStudent'), {
    type: 'bar',
    data: {labels: classLabels, datasets: [{label: 'Số SV', data: classStudentCounts, backgroundColor: '#0d6efd'}]},
    options: {responsive: true, plugins: {legend: {display: false}}}
});
new Chart(document.getElementById('chartCourseStudent'), {
    type: 'bar',
    data: {labels: courseLabels, datasets: [{label: 'Số SV', data: courseStudentCounts, backgroundColor: '#20c997'}]},
    options: {responsive: true, plugins: {legend: {display: false}}}
});
new Chart(document.getElementById('chartClassAvg'), {
    type: 'line',
    data: {labels: classAvgLabels, datasets: [{label: 'Điểm TB', data: classAvgScores, borderColor: '#fd7e14', backgroundColor: 'rgba(253,126,20,0.2)', tension: 0.3}]},
    options: {responsive: true}
});
new Chart(document.getElementById('chartCourseAvg'), {
    type: 'line',
    data: {labels: courseAvgLabels, datasets: [{label: 'Điểm TB', data: courseAvgScores, borderColor: '#6610f2', backgroundColor: 'rgba(102,16,242,0.2)', tension: 0.3}]},
    options: {responsive: true}
});
new Chart(document.getElementById('chartSpecialization'), {
    type: 'doughnut',
    data: {labels: specializationLabels, datasets: [{label: 'Số GV', data: specializationCounts, backgroundColor: ['#0d6efd','#20c997','#fd7e14','#6610f2','#ffc107','#dc3545','#6c757d']}]},
    options: {responsive: true}
});
</script>

<?php require_once 'footer.php'; ?> 