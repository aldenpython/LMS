<?php

require_once 'db_connection.php';
require_once 'be_auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (is_admin() || is_instructor())) {
    $title = trim($_POST['course_name']);
    $price = floatval($_POST['fees']);
    $description = ""; // Optional: add description field if you want
    $instructor_id = is_admin() ? intval($_POST['instructor_id']) : $_SESSION['user_id'];

    // Optional: add description support if your form includes it
    if (isset($_POST['description'])) {
        $description = trim($_POST['description']);
    }

    $stmt = $conn->prepare("INSERT INTO Course (title, description, price, instructor_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $title, $description, $price, $instructor_id);

    if ($stmt->execute()) {
        $new_course_id = $stmt->insert_id;
        set_message("Course created successfully! Now add lessons and assessments.", "success");
        $stmt->close();
        header("Location: lessons.php?course_id=$new_course_id");
        exit;
    } else {
        set_message("Failed to create course.", "danger");
    }
    $stmt->close();
} else {
    set_message("Unauthorized or invalid request.", "danger");
}

header("Location: courses.php");
exit;
?>