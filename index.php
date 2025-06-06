<?php
// Include authentication functions
require_once 'be_auth.php';

// If already logged in, redirect to appropriate page
if (is_logged_in()) {
    if (is_admin() || is_instructor()) {
        header("Location: courses.php");
    } else {
        header("Location: enrollments.php");
    }
    exit;
}

// Include header
include 'inc_header_nav.php';
?>

<div class="form-container">
    <h2 class="form-title">Login to Online Learning Platform</h2>
    
    <?php display_message(); ?>
    
    <form action="be_login.php" method="post">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="role">Role:</label>
            <select id="role" name="role" class="form-control" required>
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="instructor">Instructor</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-login">Login</button>
        </div>
    </form>

    <p style="margin-top: 15px;">
        Don’t have an account? 
        <a href="registration.php">Register here</a>
    </p>
            
   
</div>

<?php
// Include footer
include 'inc_footer.php';
?>