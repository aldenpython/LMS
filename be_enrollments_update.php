<?php
// Include database connection and authentication
require_once 'db_connection.php';
require_once 'be_auth.php';

// Ensure user is logged in
require_login();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get action type
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action == 'update') {
        // Only admins can update enrollment status
        if (!is_admin()) {
            set_message("Access denied. Admin privileges required to update enrollment status.", "danger");
            header("Location: enrollments.php");
            exit;
        }
        
        // Get form data
        $enrollment_id = isset($_POST['enrollment_id']) ? intval($_POST['enrollment_id']) : 0;
        $status = isset($_POST['status']) ? trim($_POST['status']) : '';
        
        // Validate input
        if ($enrollment_id <= 0 || empty($status)) {
            set_message("Invalid enrollment data.", "danger");
            header("Location: enrollments.php");
            exit;
        }
        
        // Validate status
        $valid_statuses = array('pending', 'finalized');
        if (!in_array($status, $valid_statuses)) {
            set_message("Invalid enrollment status.", "danger");
            header("Location: enrollments.php");
            exit;
        }
        
        // Update enrollment
        $update_query = "UPDATE enrollment SET completion_status = ? WHERE enrollment_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $status, $enrollment_id);
        
        if ($stmt->execute()) {
            set_message("Enrollment status updated successfully.", "success");
        } else {
            set_message("Error updating enrollment status: " . $conn->error, "danger");
        }
        
        $stmt->close();
    } elseif ($action == 'enroll') {
        // Students can enroll themselves in courses
        if (!is_student()) {
            set_message("Access denied. Student account required to enroll in courses.", "danger");
            header("Location: enrollments.php");
            exit;
        }
        
        // Get form data
        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        
        // Validate input
        if ($course_id <= 0) {
            set_message("Invalid course selection.", "danger");
            header("Location: enrollments.php");
            exit;
        }
        
        // Call the enroll_student$$ procedure instead of manual check/insert
        $stmt = $conn->prepare("CALL enroll_student(?, ?)");
        $stmt->bind_param("ii", $_SESSION['user_id'], $course_id);

        try {
            $stmt->execute();
            set_message("Successfully enrolled in course.", "success");
        } catch (mysqli_sql_exception $e) {
            set_message("Enrollment failed: " . $e->getMessage(), "danger");
        }
        $stmt->close();
    } elseif ($action == 'drop') {
    if (!is_student()) {
        set_message("Access denied. Only students can drop courses.", "danger");
        header("Location: enrollments.php");
        exit;
    }

    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $student_id = $_SESSION['user_id'];

    if ($course_id <= 0) {
        set_message("Invalid course selection.", "danger");
        header("Location: enrollments.php");
        exit;
    }

    // Call the stored procedure
    $stmt = $conn->prepare("CALL drop_student_from_course(?, ?)");
    $stmt->bind_param("ii", $student_id, $course_id);

    try {
        $stmt->execute();
        $stmt->close();
        // Flush any remaining results
        while ($conn->more_results() && $conn->next_result()) {;}
        set_message("Successfully dropped from course.", "success");
    } catch (mysqli_sql_exception $e) {
        set_message("Drop failed: " . $e->getMessage(), "danger");
    }

    header("Location: enrollments.php");
    exit;
}
}

// Redirect back to enrollments page
header("Location: enrollments.php");
exit;

// Close connection
$conn->close();
?>