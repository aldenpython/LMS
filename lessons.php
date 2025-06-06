<?php

require_once 'db_connection.php';
require_once 'be_auth.php';
require_login();

// Get courses for dropdown based on user role
if (is_admin() || is_instructor()) {
    $courses_query = "SELECT course_id, title FROM Course";
} else {
    // Students: only courses they're enrolled in
    $courses_query = "SELECT c.course_id, c.title
                      FROM Course c
                      JOIN Enrollment e ON c.course_id = e.course_id
                      WHERE e.student_id = " . intval($_SESSION['user_id']);
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

// Fetch lessons for the selected course
$lessons = [];
if ($selected_course_id > 0) {
    $lessons_query = "SELECT lesson_id, title, content, video_url, order_number, material_path
                      FROM Lesson
                      WHERE course_id = ?
                      ORDER BY order_number ASC, lesson_id ASC";
    $stmt = $conn->prepare($lessons_query);
    $stmt->bind_param("i", $selected_course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $lessons[] = $row;
    }
    $stmt->close();
}

// Include header
include 'inc_header_nav.php';
?>

<div class="container">
    <h2>Lessons</h2>
    <?php display_message(); ?>

    <!-- Course selection -->
    <form action="lessons.php" method="get" class="form-inline mb-3">
        <label for="course_id">Select Course:</label>
        <select id="course_id" name="course_id" class="form-control ml-2" onchange="this.form.submit()">
            <option value="">-- Choose --</option>
            <?php if ($courses_result) : ?>
                <?php while ($course = $courses_result->fetch_assoc()): ?>
                    <option value="<?php echo $course['course_id']; ?>" <?php if ($selected_course_id == $course['course_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($course['title']); ?>
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
            
        </select>
    </form>

    <?php if ($selected_course_id > 0): ?>
        <?php if (count($lessons) > 0): ?>
            <?php if (is_student()): ?>
                <?php
                // Count completed lessons for this course and student
                $stmt = $conn->prepare("SELECT COUNT(*) FROM LessonCompletion lc JOIN Lesson l ON lc.lesson_id = l.lesson_id WHERE lc.student_id = ? AND l.course_id = ?");
                $stmt->bind_param("ii", $_SESSION['user_id'], $selected_course_id);
                $stmt->execute();
                $stmt->bind_result($completed_count);
                $stmt->fetch();
                $stmt->close();

                $total_lessons = count($lessons);
                $progress = $total_lessons > 0 ? round(($completed_count / $total_lessons) * 100) : 0;
                ?>
                <div class="mb-3">
                    <strong>Progress:</strong>
                    <?php echo "$completed_count / $total_lessons lessons completed ($progress%)"; ?>
                    <div class="custom-progress" style="height: 24px;">
                        <div class="custom-progress-bar" role="progressbar"
                            style="width: <?php echo $progress; ?>%;"
                            aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo $progress; ?>%
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php foreach ($lessons as $lesson): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4><?php echo htmlspecialchars($lesson['title']); ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($lesson['content'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($lesson['content'])); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($lesson['video_url'])): ?>
                            <video width="480" controls>
                                <source src="<?php echo htmlspecialchars($lesson['video_url']); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php endif; ?>

                        <?php if (!empty($lesson['material_path'])): ?>
                            <p>
                                <a href="<?php echo htmlspecialchars($lesson['material_path']); ?>" download>
                                    Download Material
                                </a>
                            </p>
                        <?php endif; ?>

                        <?php if (is_admin() || is_instructor()): ?>
                            <form action="be_lesson_material_upload.php" method="post" enctype="multipart/form-data" class="mt-3">
                                <input type="hidden" name="lesson_id" value="<?php echo $lesson['lesson_id']; ?>">
                                <label>Upload/Replace Material (PDF, DOCX, etc.):</label>
                                <input type="file" name="material_file" required>
                                <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                            </form>
                        <?php endif; ?>

                        <?php if (is_student()): ?>
                        <?php
                        // Check if this lesson is completed by the current student
                        $completed = false;
                        $check_stmt = $conn->prepare("SELECT 1 FROM LessonCompletion WHERE student_id = ? AND lesson_id = ?");
                        $check_stmt->bind_param("ii", $_SESSION['user_id'], $lesson['lesson_id']);
                        $check_stmt->execute();
                        $check_stmt->store_result();
                        if ($check_stmt->num_rows > 0) $completed = true;
                        $check_stmt->close();
                        ?>
                        <?php if ($completed): ?>
                            <span class="badge badge-success">Completed</span>
                        <?php else: ?>
                            <form action="mark_lesson_complete.php" method="post" style="display:inline;">
                                <input type="hidden" name="lesson_id" value="<?php echo $lesson['lesson_id']; ?>">
                                <input type="hidden" name="course_id" value="<?php echo $selected_course_id; ?>">
                                <button type="submit" class="btn btn-success btn-sm">Mark as Completed</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (is_admin() || is_instructor()): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Add New Lesson</h4>
                    </div>
                    <div class="card-body">
                        <form action="be_lessons_create.php" method="post">
                            <input type="hidden" name="course_id" value="<?php echo $selected_course_id; ?>">
                            <div class="form-group">
                                <label for="lesson_title">Lesson Title:</label>
                                <input type="text" name="lesson_title" id="lesson_title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="lesson_content">Content:</label>
                                <textarea name="lesson_content" id="lesson_content" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="video_url">Video URL:</label>
                                <input type="text" name="video_url" id="video_url" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="order_number">Order Number:</label>
                                <input type="number" name="order_number" id="order_number" class="form-control" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-success">Add Lesson</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php else: ?>
            <p>No lessons found for this course.</p>
            <?php if (is_admin() || is_instructor()): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Add New Lesson</h4>
                    </div>
                    <div class="card-body">
                        <form action="be_lessons_create.php" method="post">
                            <input type="hidden" name="course_id" value="<?php echo $selected_course_id; ?>">
                            <div class="form-group">
                                <label for="lesson_title">Lesson Title:</label>
                                <input type="text" name="lesson_title" id="lesson_title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="lesson_content">Content:</label>
                                <textarea name="lesson_content" id="lesson_content" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="video_url">Video URL:</label>
                                <input type="text" name="video_url" id="video_url" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="order_number">Order Number:</label>
                                <input type="number" name="order_number" id="order_number" class="form-control" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-success">Add Lesson</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php elseif ($selected_course_id == 0 && $courses_result && $courses_result->num_rows > 0): ?>
        <p>Please select a course to view its lessons.</p>
    <?php endif; ?>

</div>

<?php
include 'inc_footer.php';
$conn->close();
?>