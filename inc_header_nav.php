<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Learning Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>Online Learning Platform</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <li><a href="courses.php">Manage Courses</a></li>
                            <li><a href="assessments.php">Manage Assessments</a></li>
                            <li><a href="enrollments.php">Manage Enrollments</a></li>
                            <li><a href="lessons.php">Manage Lessons</a></li>
                            <li><a href="grades.php">Manage Grades</a></li>
                            <li><a href="payments.php">View Payment</a></li>
                            <li><a href="forum.php">Manage Discussion Forum</a></li>
                        <?php elseif ($_SESSION['role'] == 'instructor'): ?>
                            <li><a href="courses.php">My Courses</a></li>
                            <li><a href="lessons.php">Manage Lessons</a></li>
                            <li><a href="assessments.php">Assessments</a></li>
                            <li><a href="grades.php">Grades</a></li>
                            <li><a href="forum.php">Discussion Forum</a></li>
                        <?php elseif ($_SESSION['role'] == 'student'): ?>
                            <li><a href="enrollments.php">My Enrollments</a></li>
                            <li><a href="lessons.php">My Lessons</a></li>
                            <li><a href="assessments.php">My Assessments</a></li>
                            <li><a href="grades.php">My Grades</a></li>
                            <li><a href="forum.php">Discussion Forum</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </nav>
            <div class="auth-bar">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
                    <a href="be_logoff.php" class="btn btn-logout">Log Out</a>
                <?php else: ?>
                    <a href="index.php" class="btn btn-login">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="container">