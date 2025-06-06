<?php
require_once 'db_connection.php';
require_once 'be_auth.php';

require_login();
if (!is_admin() && !is_instructor()) {
    set_message("Access denied. You don't have permission to update grades.", "danger");
    header("Location: grades.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
    $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
    $assessment_id = isset($_POST['assessment_id']) ? intval($_POST['assessment_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $grade = isset($_POST['grade']) ? trim($_POST['grade']) : '';

    // Validate input
    if ($student_id <= 0 || $assessment_id <= 0 || $course_id <= 0 || empty($grade)) {
        set_message("Invalid grade data.", "danger");
        header("Location: grades.php?course_id=" . $course_id);
        exit;
    }

    // Validate grade
    $valid_grades = array('HD', 'D', 'C', 'P', 'F', 'N');
    if (!in_array($grade, $valid_grades)) {
        set_message("Invalid grade value.", "danger");
        header("Location: grades.php?course_id=" . $course_id);
        exit;
    }

    // Check if instructor has permission to update grades for this course
    if (is_instructor()) {
        $check_query = "SELECT c.course_id
                        FROM Course c, Assessment a
                        WHERE c.course_id = a.course_id
                        AND a.assessment_id = ? AND c.instructor_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $assessment_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            set_message("You don't have permission to update grades for this course.", "danger");
            header("Location: grades.php");
            exit;
        }
        $stmt->close();
    }

    // Update or insert grade in Submission table
    if ($submission_id > 0) {
        // Update existing submission
        $update_query = "UPDATE Submission SET grade = ? WHERE submission_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $grade, $submission_id);

        if ($stmt->execute()) {
            set_message("Grade updated successfully.", "success");
        } else {
            set_message("Error updating grade: " . $conn->error, "danger");
        }
        $stmt->close();
    } else {
        // Insert new submission
        $insert_query = "INSERT INTO Submission (student_id, assessment_id, grade) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iis", $student_id, $assessment_id, $grade);

        if ($stmt->execute()) {
            set_message("Grade added successfully.", "success");
        } else {
            set_message("Error adding grade: " . $conn->error, "danger");
        }
        $stmt->close();
    }

    header("Location: grades.php?course_id=" . $course_id);
    exit;
} else {
    set_message("Invalid request method.", "danger");
    header("Location: grades.php");
    exit;
}

$conn->close();
?>