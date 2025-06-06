<?php


require_once 'db_connection.php';
require_once 'be_auth.php';
require_login();

// Handle thread deletion (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_thread']) && $_SESSION['role'] === 'admin') {
    $thread_id = intval($_POST['delete_thread']);
    // Delete posts first to maintain FK constraints
    $conn->query("DELETE FROM ForumPost WHERE thread_id = $thread_id");
    $conn->query("DELETE FROM ForumThread WHERE thread_id = $thread_id");
    set_message("Thread and its posts deleted.", "success");
    header("Location: forum.php");
    exit;
}

// Handle new thread submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_thread'])) {
    $title = trim($_POST['title']);
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role']; // 'student', 'instructor', or 'admin'

    if ($title !== '') {
        $stmt = $conn->prepare("INSERT INTO ForumThread (user_id, user_role, title) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $user_role, $title);
        $stmt->execute();
        $stmt->close();
        set_message("Thread created!", "success");
    }
    header("Location: forum.php");
    exit;
}

// Fetch all threads
$threads = $conn->query("SELECT t.*, 
    CASE t.user_role 
        WHEN 'student' THEN s.name 
        WHEN 'instructor' THEN i.name 
        WHEN 'admin' THEN a.name 
        ELSE 'User' END AS author_name
    FROM ForumThread t
    LEFT JOIN Student s ON t.user_role='student' AND t.user_id=s.student_id
    LEFT JOIN Instructor i ON t.user_role='instructor' AND t.user_id=i.instructor_id
    LEFT JOIN Admin a ON t.user_role='admin' AND t.user_id=a.admin_id
    ORDER BY t.created_at DESC
");

include 'inc_header_nav.php';
?>

<div class="container">
    <h2>Discussion Forum</h2>
    <?php display_message(); ?>

    <!-- New Thread Form -->
    <form method="post" class="mb-4">
        <input type="hidden" name="new_thread" value="1">
        <div class="form-group">
            <label for="title">Start a new thread:</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Create Thread</button>
    </form>

    <!-- List Threads -->
    <table class="table">
        <thead>
            <tr>
                <th>Thread Title</th>
                <th>Started By</th>
                <th>Date</th>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($thread = $threads->fetch_assoc()): ?>
            <tr>
                <td>
                    <a href="thread.php?thread_id=<?php echo $thread['thread_id']; ?>">
                        <?php echo htmlspecialchars($thread['title']); ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($thread['author_name']); ?></td>
                <td><?php echo $thread['created_at']; ?></td>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <td>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this thread and all its posts?');">
                        <input type="hidden" name="delete_thread" value="<?php echo $thread['thread_id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
include 'inc_footer.php';
$conn->close();
?>