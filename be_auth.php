<?php
// Authentication helper functions

// Start session if not already started
// Used in: Multiple functions in this file, inc_header_nav.php (line 3), be_login.php (line 8), be_logoff.php (line 5)
function ensure_session_started() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
// Used in: index.php (line 5), require_login() function, multiple pages to check authentication status
function is_logged_in() {
    ensure_session_started();
    return isset($_SESSION['user_id']);
}

// Check if user has specific role
// Used in: is_admin(), is_instructor(), is_student() functions
function has_role($role) {
    ensure_session_started();
    if (!is_logged_in()) {
        return false;
    }
    
    return $_SESSION['role'] == $role;
}

// Check if user is admin
// Used in: courses.php (line 96), assessments.php (line 8), enrollments.php (line 8), grades.php (line 8), be_courses_update.php (line 13)
function is_admin() {
    return has_role('admin');
}

// Check if user is instructor
// Used in: courses.php (line 96), assessments.php (line 10), enrollments.php (line 10), grades.php (line 10), be_grades_update.php (line 6)
function is_instructor() {
    return has_role('instructor');
}

// Check if user is student
// Used in: enrollments.php (line 63), grades.php (line 146), be_enrollments_update.php (line 33)
function is_student() {
    return has_role('student');
}

// Redirect if not logged in
// Used in: courses.php (line 7), assessments.php (line 7), enrollments.php (line 7), grades.php (line 7), be_grades_update.php (line 5)
function require_login() {
    if (!is_logged_in()) {
        header("Location: index.php");
        exit;
    }
}

// Redirect if not admin
// Used in: Not directly called in the current codebase, but available for admin-only pages
function require_admin() {
    require_login();
    if (!is_admin()) {
        set_message("Access denied. Admin privileges required.", "danger");
        header("Location: index.php");
        exit;
    }
}

// Redirect if not instructor
// Used in: be_assessments_update.php (line 7), allows both instructors and admins
function require_instructor() {
    require_login();
    if (!is_instructor() && !is_admin()) {
        set_message("Access denied. Instructor privileges required.", "danger");
        header("Location: index.php");
        exit;
    }
}

// Redirect if not student
// Used in: Not directly called in the current codebase, but available for student-only pages
function require_student() {
    require_login();
    if (!is_student()) {
        set_message("Access denied. Student account required.", "danger");
        header("Location: index.php");
        exit;
    }
}

// Set flash message
// Used in: be_login.php (line 28), be_logoff.php (line 13), be_courses_update.php (line 33), be_assessments_update.php (line 24)
// Used for displaying success/error messages to the user after redirects
function set_message($message, $type = "info") {
    ensure_session_started();
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Get and clear flash message
// Used in: display_message() function to retrieve and clear session messages
function get_message() {
    ensure_session_started();
    
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'];
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        return [
            'text' => $message,
            'type' => $type
        ];
    }
    
    return null;
}

// Display flash message HTML
// Used in: index.php (line 23), courses.php (line 25), assessments.php (line 97), enrollments.php (line 63), grades.php (line 146)
// Displays success/error messages to the user
function display_message() {
    $message = get_message();
    
    if ($message) {
        echo '<div class="alert alert-' . htmlspecialchars($message['type']) . '">';
        echo htmlspecialchars($message['text']);
        echo '</div>';
    }
}

// Sanitize input
// Used in: Not directly called in the current codebase, but available for sanitizing user input
// Helps prevent XSS attacks by escaping special characters
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>