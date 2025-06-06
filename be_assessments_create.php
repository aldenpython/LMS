<?php


require_once 'db_connection.php';
require_once 'be_auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (is_admin() || is_instructor())) {
    $course_id = intval($_POST['course_id']);
    $assessment_type = trim($_POST['assessment_type']);

    $stmt = $conn->prepare("INSERT INTO Assessment (course_id, type) VALUES (?, ?)");
    $stmt->bind_param("is", $course_id, $assessment_type);

    if ($stmt->execute()) {
        set_message("Assessment added successfully!", "success");
    } else {
        set_message("Failed to add assessment.", "danger");
    }
    $stmt->close();

    header("Location: assessments.php?course_id=$course_id");
    exit;
} else {
    set_message("Unauthorized or invalid request.", "danger");
    header("Location: assessments.php");
    exit;
}
?>