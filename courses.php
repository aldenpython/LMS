<?php
// Include database connection and authentication
require_once 'db_connection.php';
require_once 'be_auth.php';

// Ensure user is logged in
require_login();

// Get all courses
$courses_query = "SELECT c.course_id, c.title, c.price,
                 i.name as instructor_name, i.instructor_id as instructor_id
                 FROM Course c
                 LEFT JOIN Instructor i ON c.instructor_id = i.instructor_id";
$courses_result = $conn->query($courses_query);

// Get all instructors for dropdown
$instructors_query = "SELECT instructor_id, name FROM Instructor";
$instructors_result = $conn->query($instructors_query);

// Include header
include 'inc_header_nav.php';
?>

<div class="container">
    <h2>Course Management</h2>
    
    <?php display_message(); ?>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Instructor</th>
                    <th>Fees ($)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($courses_result->num_rows > 0): ?>
                    <?php while ($course = $courses_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $course['course_id']; ?></td>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td>
                                <?php 
                                    echo $course['instructor_name'] 
                                        ? htmlspecialchars($course['instructor_name']) 
                                        : '<em>Not assigned</em>'; 
                                ?>
                            </td>
                            <td><?php echo number_format($course['price'], 2); ?></td>
                            <td>
                                <?php if (is_admin() || (is_instructor() && $_SESSION['user_id'] == $course['instructor_id'])): ?>
                                    <button class="btn btn-primary" 
                                            onclick="showEditForm(<?php echo $course['course_id']; ?>, 
                                                               '<?php echo addslashes($course['title']); ?>', 
                                                               <?php echo $course['instructor_id'] ? $course['instructor_id'] : 'null'; ?>, 
                                                               <?php echo $course['price']; ?>)">
                                        Edit
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No courses found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Edit Course Form (Hidden by default) -->
    <div id="edit-course-form" class="form-container" style="display: none;">
        <h3>Edit Course</h3>
        <form action="be_courses_update.php" method="post">
            <input type="hidden" id="course_id" name="course_id">
            
            <div class="form-group">
                <label for="course_name">Course Name:</label>
                <input type="text" id="course_name" name="course_name" class="form-control" required>
            </div>
            
            <?php if (is_admin()): ?>
                <div class="form-group">
                    <label for="instructor_id">Instructor:</label>
                    <select id="instructor_id" name="instructor_id" class="form-control">
                        <option value="">-- Select Instructor --</option>
                        <?php 
                        $instructors_result->data_seek(0);
                        while ($instructor = $instructors_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $instructor['instructor_id']; ?>">
                                <?php echo htmlspecialchars($instructor['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="fees">Fees ($):</label>
                <input type="number" id="fees" name="fees" class="form-control" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-success">Update Course</button>
                <button type="button" class="btn btn-secondary" onclick="hideEditForm()">Cancel</button>
            </div>
        </form>
    </div>
</div>

    <?php if (is_admin() || is_instructor()): ?>
    <div class="form-container mb-4">
        <h3>Add New Course</h3>
        <form action="be_courses_create.php" method="post">
            <div class="form-group">
                <label for="new_course_name">Course Name:</label>
                <input type="text" id="new_course_name" name="course_name" class="form-control" required>
            </div>
            <?php if (is_admin()): ?>
            <div class="form-group">
                <label for="new_instructor_id">Instructor:</label>
                <select id="new_instructor_id" name="instructor_id" class="form-control" required>
                    <option value="">-- Select Instructor --</option>
                    <?php 
                    $instructors_result->data_seek(0);
                    while ($instructor = $instructors_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $instructor['instructor_id']; ?>">
                            <?php echo htmlspecialchars($instructor['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="new_fees">Fees ($):</label>
                <input type="number" id="new_fees" name="fees" class="form-control" step="0.01" min="0" required>
            </div>
            <button type="submit" class="btn btn-success">Add Course</button>
        </form>
    </div>
    <?php endif; ?>                   

<script>
    function showEditForm(courseId, courseName, instructorId, fees) {
        // Set form values
        document.getElementById('course_id').value = courseId;
        document.getElementById('course_name').value = courseName;
        document.getElementById('fees').value = fees;
        
        // Set instructor if the dropdown exists
        const instructorDropdown = document.getElementById('instructor_id');
        if (instructorDropdown && instructorId) {
            instructorDropdown.value = instructorId;
        }
        
        // Show form
        document.getElementById('edit-course-form').style.display = 'block';
        
        // Scroll to form
        document.getElementById('edit-course-form').scrollIntoView({ behavior: 'smooth' });
    }
    
    function hideEditForm() {
        document.getElementById('edit-course-form').style.display = 'none';
    }
</script>

<?php
// Include footer
include 'inc_footer.php';

// Close connection
$conn->close();
?>