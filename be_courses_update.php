<?php
// Include database connection and authentication
require_once 'db_connection.php';
require_once 'be_auth.php';

// Ensure user is logged in
require_login();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $course_name = isset($_POST['course_name']) ? trim($_POST['course_name']) : '';
    $fees = isset($_POST['fees']) ? floatval($_POST['fees']) : 0;
    
    // Instructor ID handling (only admins can change this)
    if (is_admin() && isset($_POST['instructor_id'])) {
        $instructor_id = !empty($_POST['instructor_id']) ? intval($_POST['instructor_id']) : null;
        if ($instructor_id) {
            // Call the stored procedure to assign instructor
            $stmt = $conn->prepare("CALL assign_instructor(?, ?)");
            $stmt->bind_param("ii", $course_id, $instructor_id);
            $stmt->execute();
            $stmt->close();
        }
}
    
    // Validate input
    if (empty($course_name)) {
        set_message("Course name cannot be empty.", "danger");
        header("Location: courses.php");
        exit;
    }
    
    // Check if user has permission to edit this course
    $can_edit = false;
    
    if (is_admin()) {
        $can_edit = true;
    } elseif (is_instructor()) {
        // Check if the instructor is assigned to this course
        $check_query = "SELECT instructor_id FROM course WHERE course_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $can_edit = ($row['instructor_id'] == $_SESSION['user_id']);
        }
        $stmt->close();
    }
    
    if (!$can_edit) {
        set_message("You don't have permission to edit this course.", "danger");
        header("Location: courses.php");
        exit;
    }
    
    // Update course
    $update_query = "UPDATE course SET title = ?, price = ?";
    $param_types = "sd"; // string, double
    $params = array($course_name, $fees);
    
    // Add instructor_id to query if it's set
    if (is_admin()) {
        $update_query .= ", instructor_id = ?";
        $param_types .= "i"; // integer
        $params[] = $instructor_id;
    }
    
    $update_query .= " WHERE course_id = ?";
    $param_types .= "i"; // integer
    $params[] = $course_id;
    
    $stmt = $conn->prepare($update_query);
    
    // Dynamically bind parameters
    $stmt->bind_param($param_types, ...$params);
    
    if ($stmt->execute()) {
        set_message("Course updated successfully.", "success");
    } else {
        set_message("Error updating course: " . $conn->error, "danger");
    }
    
    $stmt->close();
} else {
    set_message("Invalid request method.", "danger");
}

// Redirect back to courses page
header("Location: courses.php");
exit;

// Close connection
$conn->close();
?>