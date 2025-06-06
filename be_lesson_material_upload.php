<?php

require_once 'db_connection.php';
require_once 'be_auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && (is_admin() || is_instructor())) {
    $lesson_id = intval($_POST['lesson_id']);
    if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
        $upload_dir = "lesson_materials/";
        if (!is_dir($upload_dir)) mkdir($upload_dir);
        $filename = uniqid() . "_" . basename($_FILES['material_file']['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['material_file']['tmp_name'], $target_path)) {
            $stmt = $conn->prepare("UPDATE Lesson SET material_path = ? WHERE lesson_id = ?");
            $stmt->bind_param("si", $target_path, $lesson_id);
            $stmt->execute();
            $stmt->close();
            set_message("Material uploaded successfully.", "success");
        } else {
            set_message("File upload failed.", "danger");
        }
    } else {
        set_message("No file selected or upload error.", "danger");
    }
}
header("Location: lessons.php");
exit;
?>