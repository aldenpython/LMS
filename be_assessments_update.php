<?php
// Include database connection and authentication
require_once 'db_connection.php';
require_once 'be_auth.php';

// Ensure user is logged in and is admin or instructor
require_login();
if (!is_admin() && !is_instructor()) {
    set_message("Access denied. You don't have permission to update assessments.", "danger");
    header("Location: assessments.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $assessment_id = isset($_POST['assessment_id']) ? intval($_POST['assessment_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $assessment_type = isset($_POST['assessment_type']) ? trim($_POST['assessment_type']) : '';
    
    // Validate input
    if ($assessment_id <= 0 || $course_id <= 0 || empty($assessment_type)) {
        set_message("Invalid assessment data.", "danger");
        header("Location: assessments.php?course_id=" . $course_id);
        exit;
    }
    
    // Validate assessment type
    $valid_types = array('Quiz', 'Project', 'Report');
    if (!in_array($assessment_type, $valid_types)) {
        set_message("Invalid assessment type.", "danger");
        header("Location: assessments.php?course_id=" . $course_id);
        exit;
    }
    
    // Check if user has permission to edit assessments for this course
    $can_edit = false;
    
    if (is_admin()) {
        $can_edit = true;
    } elseif (is_instructor()) {
        // Check if the instructor is assigned to this course
        $check_query = "SELECT c.course_id
                       FROM course c, assessment a
                       WHERE c.course_id = a.course_id
                       AND a.assessment_id = ? AND c.instructor_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $assessment_id, $_SESSION['user_id']);
        $stmt->execute();
        $can_edit = ($stmt->get_result()->num_rows > 0);
        $stmt->close();
    }
    
    if (!$can_edit) {
        set_message("You don't have permission to edit assessments for this course.", "danger");
        header("Location: assessments.php");
        exit;
    }
    
    // Update assessment
    $update_query = "UPDATE assessment SET type = ? WHERE assessment_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $assessment_type, $assessment_id);
    
    if ($stmt->execute()) {
        set_message("Assessment updated successfully.", "success");
    } else {
        set_message("Error updating assessment: " . $conn->error, "danger");
    }
    
    $stmt->close();
    
    // Redirect back to assessments page
    header("Location: assessments.php?course_id=" . $course_id);
    exit;
} else {
    set_message("Invalid request method.", "danger");
    header("Location: assessments.php");
    exit;
}

// Close connection
$conn->close();
?>