<?php
// Include database connection and authentication
require_once 'db_connection.php';
require_once 'be_auth.php';

// Ensure user is logged in
require_login();

// Get enrollments based on user role
if (is_admin()) {
    // Admins can see all enrollments
    $enrollments_query = "SELECT e.enrollment_id, e.completion_status,
                         c.course_id, c.title, c.price,
                         s.student_id, s.name
                         FROM Enrollment e, Course c, Student s
                         WHERE e.course_id = c.course_id
                         AND e.student_id = s.student_id
                         ORDER BY e.completion_status, s.name, c.title";
    $enrollments_result = $conn->query($enrollments_query);
    
    // Get all students for dropdown
    $students_query = "SELECT student_id, name FROM student";
    $students_result = $conn->query($students_query);
    
    // Get all courses for dropdown
    $courses_query = "SELECT course_id, title FROM course";
    $courses_result = $conn->query($courses_query);
} elseif (is_instructor()) {
    // Instructors can see enrollments for their courses
    $enrollments_query = "SELECT e.enrollment_id, e.completion_status,
                         c.course_id, c.title, c.price,
                         s.student_id, s.name
                         FROM Enrollment e, Course c, Student s
                         WHERE e.course_id = c.course_id
                         AND e.student_id = s.student_id
                         AND c.instructor_id = " . $_SESSION['user_id'] . "
                         ORDER BY e.completion_status, s.name, c.title";
    $enrollments_result = $conn->query($enrollments_query);
} else {
    // Students can only see their own enrollments
    $enrollments_query = "
                        SELECT 
                            e.enrollment_id, e.completion_status,
                            c.course_id, c.title, c.price,
                            s.student_id, s.name,
                            p.payment_id
                        FROM Enrollment e
                        JOIN Course c ON e.course_id = c.course_id
                        JOIN Student s ON e.student_id = s.student_id
                        LEFT JOIN Payment p ON p.student_id = e.student_id AND p.course_id = e.course_id
                        WHERE e.student_id = " . intval($_SESSION['user_id']) . "
                        ORDER BY e.completion_status, c.title
                    ";
    $enrollments_result = $conn->query($enrollments_query);
    
    // Get available courses for student enrollment
    $available_courses_query = "SELECT c.course_id, c.title, c.price, i.name as instructor_name
                           FROM Course c
                           LEFT JOIN Instructor i ON c.instructor_id = i.instructor_id
                           WHERE c.course_id NOT IN (
                               SELECT course_id FROM Enrollment WHERE student_id = " . intval($_SESSION['user_id']) . "
                           )
                           ORDER BY c.title";
    $available_courses_result = $conn->query($available_courses_query);
}

// Include header
include 'inc_header_nav.php';
?>

<div class="container">
    <h2><?php echo is_student() ? 'My Enrollments' : 'Enrollment Management'; ?></h2>
    
    <?php display_message(); ?>
    
    <!-- Enrollments List -->
    <div class="card">
        <div class="card-header">
            <h3>Current Enrollments</h3>
        </div>
        <div class="card-body">
            <?php if ($enrollments_result->num_rows > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <?php if (!is_student()): ?>
                                    <th>Student</th>
                                <?php endif; ?>
                                <th>Course</th>
                                <th>Fees ($)</th>
                                <th>Status</th>
                                <?php if (is_student()): ?>
                                    <th>Payment</th>
                                    <th>Drop</th>
                                <?php endif; ?>
                                <?php if (is_admin()): ?>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($enrollment = $enrollments_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $enrollment['enrollment_id']; ?></td>
                                <?php if (!is_student()): ?>
                                    <td><?php echo htmlspecialchars($enrollment['name']); ?></td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($enrollment['title']); ?></td>
                                <td><?php echo number_format($enrollment['price'], 2); ?></td>
                                <td class="status-<?php echo $enrollment['completion_status']; ?>">
                                    <?php echo ucfirst($enrollment['completion_status']); ?>
                                </td>
                                <?php if (is_student()): ?>
                                    <td>
                                        <?php if (!empty($enrollment['payment_id'])): ?>
                                            <span class="badge badge-success">Paid</span>
                                        <?php else: ?>
                                            <!-- In enrollments.php, for unpaid courses -->
                                            <form action="pay.php" method="get" style="display:inline;">
                                                        <input type="hidden" name="course_id" value="<?php echo $enrollment['course_id']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm">Pay Now</button>
                                                    </form>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="be_enrollments_update.php" method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="drop">
                                            <input type="hidden" name="course_id" value="<?php echo $enrollment['course_id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to drop this course?');">Drop</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                                <?php if (is_admin()): ?>
                                    <td>
                                        <button class="btn btn-primary" 
                                                onclick="showEditForm(<?php echo $enrollment['enrollment_id']; ?>, 
                                                                '<?php echo $enrollment['completion_status']; ?>')">
                                            Edit
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No enrollments found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (is_student() && isset($available_courses_result) && $available_courses_result->num_rows > 0): ?>
        <!-- Available Courses for Student Enrollment -->
        <div class="card">
            <div class="card-header">
                <h3>Available Courses</h3>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Instructor</th>
                                <th>Fees ($)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($course = $available_courses_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['title']); ?></td>
                                    <td>
                                        <?php 
                                            echo isset($course['instructor_name']) 
                                                ? htmlspecialchars($course['instructor_name']) 
                                                : '<em>Not assigned</em>'; 
                                        ?>
                                    </td>
                                    <td><?php echo number_format($course['price'], 2); ?></td>
                                    <td>
                                        <form action="be_enrollments_update.php" method="post">
                                            <input type="hidden" name="action" value="enroll">
                                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                            <button type="submit" class="btn btn-success">Enroll</button>
                                        </form>
                                    </td>
                                    
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>


    
    <?php if (is_admin()): ?>
        <!-- Edit Enrollment Form (Hidden by default) -->
        <div id="edit-enrollment-form" class="form-container" style="display: none;">
            <h3>Edit Enrollment</h3>
            <form action="be_enrollments_update.php" method="post">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="enrollment_id" name="enrollment_id">
                
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="pending">Pending</option>
                        <option value="finalized">Finalized</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success">Update Enrollment</button>
                    <button type="button" class="btn btn-secondary" onclick="hideEditForm()">Cancel</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

        

<script>
    function showEditForm(enrollmentId, status) {
        // Set form values
        document.getElementById('enrollment_id').value = enrollmentId;
        document.getElementById('status').value = status;
        
        // Show form
        document.getElementById('edit-enrollment-form').style.display = 'block';
        
        // Scroll to form
        document.getElementById('edit-enrollment-form').scrollIntoView({ behavior: 'smooth' });
    }
    
    function hideEditForm() {
        document.getElementById('edit-enrollment-form').style.display = 'none';
    }
</script>

<?php
// Include footer
include 'inc_footer.php';

// Close connection
$conn->close();
?>