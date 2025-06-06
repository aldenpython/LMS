<?php

require_once 'db_connection.php';
require_once 'be_auth.php';
require_login();

if (is_student() && isset($_POST['lesson_id'])) {
    $student_id = $_SESSION['user_id'];
    $lesson_id = intval($_POST['lesson_id']);


    // Prevent duplicate entries
    $stmt = $conn->prepare("SELECT 1 FROM LessonCompletion WHERE student_id = ? AND lesson_id = ?");
    $stmt->bind_param("ii", $student_id, $lesson_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        $stmt->close();
        $insert = $conn->prepare("INSERT INTO LessonCompletion (student_id, lesson_id) VALUES (?, ?)");
        $insert->bind_param("ii", $student_id, $lesson_id);
        $insert->execute();
        $insert->close();
    } else {
        $stmt->close();
    }
}
header("Location: lessons.php?course_id=" . intval($_POST['course_id']));
exit;
?>