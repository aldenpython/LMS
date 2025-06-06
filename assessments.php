<?php
// Include database connection and authentication
require_once 'db_connection.php';
require_once 'be_auth.php';

// Ensure user is logged in
require_login();

// Get all courses based on user role
if (is_admin()) {
    $courses_query = "SELECT c.course_id, c.title, i.name as instructor_name
                     FROM Course c, Instructor i
                     WHERE c.instructor_id = i.instructor_id OR c.instructor_id IS NULL
                     ORDER BY c.title";
} elseif (is_instructor()) {
    $courses_query = "SELECT c.course_id, c.title, i.name as instructor_name
                     FROM Course c
                     LEFT JOIN Instructor i ON c.instructor_id = i.instructor_id
                     AND c.instructor_id = " . $_SESSION['user_id'] . "
                     ORDER BY c.title";
} else {
    // Students can only view assessments for courses they're enrolled in
    $courses_query = "SELECT c.course_id, c.title, i.name as instructor_name
                    FROM Course c
                    JOIN Enrollment e ON c.course_id = e.course_id
                    LEFT JOIN Instructor i ON c.instructor_id = i.instructor_id
                    WHERE e.student_id = " . $_SESSION['user_id'] . "
                    ORDER BY c.title";
}

$courses_result = $conn->query($courses_query);

// Get assessments for a specific course if course_id is provided
$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$assessments = array();

// Payment check for students
if (is_student() && $selected_course_id > 0) {
    $stmt = $conn->prepare("SELECT 1 FROM Payment WHERE student_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $selected_course_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        set_message("You must complete payment before accessing this course.", "danger");
        header("Location: pay.php?course_id=" . $selected_course_id);
        exit;
    }
    $stmt->close();
}

if ($selected_course_id > 0) {
    // Check if user has access to this course
    $has_access = false;
    
    if (is_admin()) {
        $has_access = true;
    } elseif (is_instructor()) {
        $access_query = "SELECT course_id FROM course WHERE course_id = ? AND instructor_id = ?";
        $stmt = $conn->prepare($access_query);
        $stmt->bind_param("ii", $selected_course_id, $_SESSION['user_id']);
        $stmt->execute();
        $has_access = ($stmt->get_result()->num_rows > 0);
        $stmt->close();
    } else {
        $access_query = "SELECT e.course_id FROM enrollment e WHERE e.course_id = ? AND e.student_id = ?";
        $stmt = $conn->prepare($access_query);
        $stmt->bind_param("ii", $selected_course_id, $_SESSION['user_id']);
        $stmt->execute();
        $has_access = ($stmt->get_result()->num_rows > 0);
        $stmt->close();
    }
    
    if ($has_access) {
        $assessments_query = "SELECT a.assessment_id, a.type, c.title AS course_name, count_submissions(a.assessment_id) AS submission_count
                             FROM Assessment a
                             LEFT JOIN Course c ON a.course_id = c.course_id
                             WHERE a.course_id = ?
                             ORDER BY a.type";
        $stmt = $conn->prepare($assessments_query);
        $stmt->bind_param("i", $selected_course_id);
        $stmt->execute();
        $assessments_result = $stmt->get_result();
        
        while ($assessment = $assessments_result->fetch_assoc()) {
            $assessments[] = $assessment;
        }
        
        $stmt->close();
    } else {
        set_message("You don't have access to this course.", "danger");
        header("Location: assessments.php");
        exit;
    }
}

// Include header
include 'inc_header_nav.php';
?>

<div class="container">
    <h2>Assessments Management</h2>
    
    <?php display_message(); ?>
    
    <!-- Course Selection -->
    <div class="card">
        <div class="card-header">
            <h3>Select Course</h3>
        </div>
        <div class="card-body">
            <form action="assessments.php" method="get">
                <div class="form-group">
                    <label for="course_id">Course:</label>
                    <select id="course_id" name="course_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Select Course --</option>
                        <?php while ($course = $courses_result->fetch_assoc()): ?>
                            <option value="<?php echo $course['course_id']; ?>" 
                                    <?php echo ($selected_course_id == $course['course_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['title']); ?> 
                                (<?php echo $course['instructor_name'] ? htmlspecialchars($course['instructor_name']) : 'No instructor'; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($selected_course_id > 0): ?>
        <!-- Assessments List -->
        <div class="card">
            <div class="card-header">
                <h3>Assessments</h3>
            </div>
            <div class="card-body">
                <?php if (count($assessments) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Course</th>
                                    <?php if (is_admin() || is_instructor()): ?>
                                        <th>Number of Submission</th>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assessments as $assessment): ?>
                                    <tr>
                                        <td><?php echo $assessment['assessment_id']; ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($assessment['type'])); ?></td>
                                        <td><?php echo htmlspecialchars($assessment['course_name']); ?></td>
                                        <?php if (is_admin() || is_instructor()): ?>
                                            <td><?php echo $assessment['submission_count']; ?></td>
                                            <td>
                                                <button class="btn btn-primary" 
                                                        onclick="showEditForm(<?php echo $assessment['assessment_id']; ?>, 
                                                                           '<?php echo $assessment['type']; ?>')">
                                                    Edit
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                        <?php if (is_student()): ?>
                                        <td>
                                            <form action="be_submission_upload.php" method="post" enctype="multipart/form-data" style="display:inline;">
                                                <input type="hidden" name="assessment_id" value="<?php echo $assessment['assessment_id']; ?>">
                                                <input type="file" name="assignment_file" required>
                                                <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                                            </form>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No assessments found for this course.</p>
                    <?php if (is_admin() || is_instructor()): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4>Add New Assessment</h4>
                            </div>
                            <div class="card-body">
                                <form action="be_assessments_create.php" method="post">
                                    <input type="hidden" name="course_id" value="<?php echo $selected_course_id; ?>">
                                    <div class="form-group">
                                        <label for="assessment_type">Assessment Type:</label>
                                        <select name="assessment_type" id="assessment_type" class="form-control" required>
                                            <option value="Quiz">Quiz</option>
                                            <option value="Project">Project</option>
                                            <option value="Report">Report</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success">Add Assessment</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if (is_admin() || is_instructor()): ?>
                    <!-- Edit Assessment Form (Hidden by default) -->
                    <div id="edit-assessment-form" class="form-container" style="display: none;">
                        <h3>Edit Assessment</h3>
                        <form action="be_assessments_update.php" method="post">
                            <input type="hidden" id="assessment_id" name="assessment_id">
                            <input type="hidden" name="course_id" value="<?php echo $selected_course_id; ?>">
                            
                            <div class="form-group">
                                <label for="assessment_type">Assessment Type:</label>
                                <select id="assessment_type" name="assessment_type" class="form-control" required>
                                    <option value="Quiz">Quiz</option>
                                    <option value="Project">Project</option>
                                    <option value="Report">Report</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-success">Update Assessment</button>
                                <button type="button" class="btn btn-secondary" onclick="hideEditForm()">Cancel</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function showEditForm(assessmentId, assessmentType) {
        // Set form values
        document.getElementById('assessment_id').value = assessmentId;
        document.getElementById('assessment_type').value = assessmentType;
        
        // Show form
        document.getElementById('edit-assessment-form').style.display = 'block';
        
        // Scroll to form
        document.getElementById('edit-assessment-form').scrollIntoView({ behavior: 'smooth' });
    }
    
    function hideEditForm() {
        document.getElementById('edit-assessment-form').style.display = 'none';
    }
</script>

<?php
// Include footer
include 'inc_footer.php';

// Close connection
$conn->close();
?>