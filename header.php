<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo" style="font-size:2.1rem; font-weight:800; letter-spacing:1.5px;">Student Management</a>
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php"<?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo ' class="active"';?>>Dashboard</a>
                    <?php if (isAdmin()): ?>
                        <a href="students.php"<?php if(basename($_SERVER['PHP_SELF'])=='students.php') echo ' class="active"';?>>Students</a>
                        <a href="classes.php"<?php if(basename($_SERVER['PHP_SELF'])=='classes.php') echo ' class="active"';?>>Lớp học</a>
                        <a href="courses.php"<?php if(basename($_SERVER['PHP_SELF'])=='courses.php') echo ' class="active"';?>>Khóa học</a>
                        <a href="teachers.php"<?php if(basename($_SERVER['PHP_SELF'])=='teachers.php') echo ' class="active"';?>>Giáo viên</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php"<?php if(basename($_SERVER['PHP_SELF'])=='login.php') echo ' class="active"';?>>Login</a>
                    <a href="register.php"<?php if(basename($_SERVER['PHP_SELF'])=='register.php') echo ' class="active"';?>>Register</a>
                <?php endif; ?>
                <button id="darkModeToggle" class="btn btn-sm btn-secondary ms-3" style="vertical-align:middle;">
                    <span id="darkModeIcon">🌙</span>
                </button>
            </div>
        </div>
    </nav>
    <script>
    // Dark mode toggle
    function setDarkMode(on) {
        if (on) {
            document.documentElement.classList.add('dark-mode');
            localStorage.setItem('darkMode', '1');
            document.getElementById('darkModeIcon').textContent = '☀️';
        } else {
            document.documentElement.classList.remove('dark-mode');
            localStorage.setItem('darkMode', '0');
            document.getElementById('darkModeIcon').textContent = '🌙';
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        var darkMode = localStorage.getItem('darkMode') === '1';
        setDarkMode(darkMode);
        document.getElementById('darkModeToggle').onclick = function(e) {
            e.preventDefault();
            setDarkMode(!document.documentElement.classList.contains('dark-mode'));
        };
    });
    </script>