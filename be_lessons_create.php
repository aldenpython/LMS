<?php


require_once 'db_connection.php';
require_once 'be_auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (is_admin() || is_instructor())) {
    $course_id = intval($_POST['course_id']);
    $title = trim($_POST['lesson_title']);
    $content = trim($_POST['lesson_content']);
    $video_url = trim($_POST['video_url']);
    $order_number = intval($_POST['order_number']);

    $stmt = $conn->prepare("INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $course_id, $title, $content, $video_url, $order_number);

    if ($stmt->execute()) {
        set_message("Lesson added successfully!", "success");
    } else {
        set_message("Failed to add lesson.", "danger");
    }
    $stmt->close();

    header("Location: lessons.php?course_id=$course_id");
    exit;
} else {
    set_message("Unauthorized or invalid request.", "danger");
    header("Location: lessons.php");
    exit;
}
?>