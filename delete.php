<?php
require_once 'header.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('login.php');
}

$user_id = $_GET['id'] ?? '';

if (empty($user_id) || !is_numeric($user_id)) {
    redirect('students.php');
}

try {
    $conn->beginTransaction();
    
    // Delete student details
    $stmt = $conn->prepare("DELETE FROM students WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([$user_id]);
    
    $conn->commit();
    $_SESSION['success'] = 'Student deleted successfully';
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = 'Failed to delete student';
}

redirect('students.php');
?>