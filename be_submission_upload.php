<?php

require_once 'db_connection.php';
require_once 'be_auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && is_student()) {
    $assessment_id = intval($_POST['assessment_id']);
    $student_id = $_SESSION['user_id'];

    // Handle file upload
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir);
        $filename = uniqid() . "_" . basename($_FILES['assignment_file']['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target_path)) {
            // Insert or update submission
            $stmt = $conn->prepare("REPLACE INTO Submission (student_id, assessment_id, file_path) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $student_id, $assessment_id, $target_path);
            $stmt->execute();
            $stmt->close();
            set_message("Assignment uploaded successfully.", "success");
        } else {
            set_message("File upload failed.", "danger");
        }
    } else {
        set_message("No file selected or upload error.", "danger");
    }
}
header("Location: assessments.php?course_id=" . intval($_GET['course_id'] ?? 0));
exit;
?>