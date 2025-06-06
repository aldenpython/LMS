<?php
// Include database connection and authentication
require_once 'db_connection.php';
require_once 'be_auth.php';

// Ensure user is logged in
require_login();

// Get courses based on user role
if (is_admin()) {
    $courses_query = "SELECT c.course_id, c.title, i.name as instructor_name
                     FROM Course c, Instructor i
                     WHERE c.instructor_id = i.instructor_id OR c.instructor_id IS NULL
                     ORDER BY c.title";
} elseif (is_instructor()) {
    $courses_query = "SELECT c.course_id, c.title AS course_name, i.name as instructor_name
                     FROM Course c
                     LEFT JOIN Instructor i ON c.instructor_id = i.instructor_id
                     WHERE c.instructor_id = " . $_SESSION['user_id'] . "
                     ORDER BY c.title";
} else {
    // Students can only view grades for courses they're enrolled in
    $courses_query = "SELECT c.course_id, c.title AS course_name, i.name as instructor_name
                    FROM Course c, Enrollment e, Instructor i
                    WHERE c.course_id = e.course_id
                    AND c.instructor_id = i.instructor_id
                    AND e.student_id = " . intval($_SESSION['user_id']) . "
                    ORDER BY c.title";
}

$courses_result = $conn->query($courses_query);

// Get selected course
$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

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

// Get assessments and grades for the selected course
$assessments = array();
$students = array();
$grades = array();

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
        // Get assessments for this course
        $assessments_query = "SELECT a.assessment_id, a.type FROM Assessment a
                             WHERE course_id = ? ORDER BY a.type";
        $stmt = $conn->prepare($assessments_query);
        $stmt->bind_param("i", $selected_course_id);
        $stmt->execute();
        $assessments_result = $stmt->get_result();
        
        while ($assessment = $assessments_result->fetch_assoc()) {
            $assessments[] = $assessment;
        }
        
        $stmt->close();
        
        // Get students for this course
        if (is_admin() || is_instructor()) {
            $students_query = "SELECT s.student_id, s.name FROM student s, enrollment e
                              WHERE s.student_id = e.student_id
                              AND e.course_id = ? 
                              ORDER BY s.name";
            $stmt = $conn->prepare($students_query);
            $stmt->bind_param("i", $selected_course_id);
            $stmt->execute();
            $students_result = $stmt->get_result();
            
            while ($student = $students_result->fetch_assoc()) {
                $students[] = $student;
            }
            
            $stmt->close();
        } else {
            // For students, only show their own grades
            $students[] = array(
                'student_id' => $_SESSION['user_id'],
                'name' => $_SESSION['name']
            );
        }
        
        // Get all grades for this course
        $grades_query = "SELECT s.submission_id, s.student_id, s.assessment_id, s.grade
                        FROM Submission s, Assessment a
                        WHERE s.assessment_id = a.assessment_id
                        AND a.course_id = ?";
        $stmt = $conn->prepare($grades_query);
        $stmt->bind_param("i", $selected_course_id);
        $stmt->execute();
        $grades_result = $stmt->get_result();
        
        while ($grade = $grades_result->fetch_assoc()) {
            $grades[$grade['student_id']][$grade['assessment_id']] = array(
                'submission_id' => $grade['submission_id'],
                'grade' => $grade['grade']
            );
        }
        
        $stmt->close();
    } else {
        set_message("You don't have access to this course.", "danger");
        header("Location: grades.php");
        exit;
    }
}

// Include header
include 'inc_header_nav.php';
?>

<div class="container">
    <h2><?php echo is_student() ? 'My Grades' : 'Grades Management'; ?></h2>
    
    <?php display_message(); ?>
    
    <!-- Course Selection -->
    <div class="card">
        <div class="card-header">
            <h3>Select Course</h3>
        </div>
        <div class="card-body">
            <form action="grades.php" method="get">
                <div class="form-group">
                    <label for="course_id">Course:</label>
                    <select id="course_id" name="course_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Select Course --</option>
                        <?php while ($course = $courses_result->fetch_assoc()): ?>
                            <option value="<?php echo $course['course_id']; ?>" 
                                    <?php echo ($selected_course_id == $course['course_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_name']); ?> 
                                (<?php echo $course['instructor_name'] ? htmlspecialchars($course['instructor_name']) : 'No instructor'; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($selected_course_id > 0 && count($assessments) > 0 && count($students) > 0): ?>
        <!-- Grades Table -->
        <div class="card">
            <div class="card-header">
                <h3>Grades</h3>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <?php foreach ($assessments as $assessment): ?>
                                    <th><?php echo ucfirst(htmlspecialchars($assessment['type'])); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <?php foreach ($assessments as $assessment): ?>
                                        <td>
                                            <?php
                                            $grade_value = isset($grades[$student['student_id']][$assessment['assessment_id']]) 
                                                ? $grades[$student['student_id']][$assessment['assessment_id']]['grade'] 
                                                : 'N';
                                            $grade_id = isset($grades[$student['student_id']][$assessment['assessment_id']]) 
                                                ? $grades[$student['student_id']][$assessment['assessment_id']]['submission_id'] 
                                                : 0;
                                            ?>
                                            
                                            <?php if (is_admin() || is_instructor()): ?>
                                                <form action="be_grades_update.php" method="post" class="grade-form">
                                                    <input type="hidden" name="submission_id" value="<?php echo $submission_id; ?>">
                                                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                                    <input type="hidden" name="assessment_id" value="<?php echo $assessment['assessment_id']; ?>">
                                                    <input type="hidden" name="course_id" value="<?php echo $selected_course_id; ?>">
                                                    
                                                    <select name="grade" class="form-control grade-select" onchange="this.form.submit()">
                                                        <option value="HD" <?php echo ($grade_value == 'HD') ? 'selected' : ''; ?>>HD</option>
                                                        <option value="D" <?php echo ($grade_value == 'D') ? 'selected' : ''; ?>>D</option>
                                                        <option value="C" <?php echo ($grade_value == 'C') ? 'selected' : ''; ?>>C</option>
                                                        <option value="P" <?php echo ($grade_value == 'P') ? 'selected' : ''; ?>>P</option>
                                                        <option value="F" <?php echo ($grade_value == 'F') ? 'selected' : ''; ?>>F</option>
                                                        <option value="N" <?php echo ($grade_value == 'N') ? 'selected' : ''; ?>>N</option>
                                                    </select>
                                                </form>
                                            <?php else: ?>
                                                <span class="grade-<?php echo $grade_value; ?>">
                                                    <?php echo $grade_value; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="grade-legend">
                    <h4>Grade Legend</h4>
                    <ul>
                        <li><span class="grade-HD">HD</span> - High Distinction</li>
                        <li><span class="grade-D">D</span> - Distinction</li>
                        <li><span class="grade-C">C</span> - Credit</li>
                        <li><span class="grade-P">P</span> - Pass</li>
                        <li><span class="grade-F">F</span> - Fail</li>
                        <li><span class="grade-N">N</span> - Not Submitted</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php elseif ($selected_course_id > 0): ?>
        <div class="alert alert-info">
            <?php if (count($assessments) == 0): ?>
                <p>No assessments found for this course.</p>
            <?php elseif (count($students) == 0): ?>
                <p>No students enrolled in this course.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .grade-form {
        margin: 0;
    }
    
    .grade-select {
        width: 70px;
        padding: 2px;
        font-weight: bold;
    }
    
    .grade-legend {
        margin-top: 20px;
    }
    
    .grade-legend ul {
        list-style: none;
        padding: 0;
        display: flex;
        flex-wrap: wrap;
    }
    
    .grade-legend li {
        margin-right: 20px;
        margin-bottom: 10px;
    }
</style>

<?php
// Include footer
include 'inc_footer.php';

// Close connection
$conn->close();
?>