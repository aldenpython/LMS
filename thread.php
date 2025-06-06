<?php


require_once 'db_connection.php';
require_once 'be_auth.php';
require_login();

$thread_id = isset($_GET['thread_id']) ? intval($_GET['thread_id']) : 0;

// Handle post deletion (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post']) && $_SESSION['role'] === 'admin') {
    $post_id = intval($_POST['delete_post']);
    $conn->query("DELETE FROM ForumPost WHERE post_id = $post_id");
    set_message("Post deleted.", "success");
    header("Location: thread.php?thread_id=$thread_id");
    exit;
}

// Handle new post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_post'])) {
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'];
    if ($content !== '' && $thread_id > 0) {
        $stmt = $conn->prepare("INSERT INTO ForumPost (thread_id, user_id, user_role, content) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $thread_id, $user_id, $user_role, $content);
        $stmt->execute();
        $stmt->close();
        set_message("Reply posted!", "success");
    }
    header("Location: thread.php?thread_id=$thread_id");
    exit;
}

// Fetch thread info
$stmt = $conn->prepare("SELECT t.*, 
    CASE t.user_role 
        WHEN 'student' THEN s.name 
        WHEN 'instructor' THEN i.name 
        WHEN 'admin' THEN a.name 
        ELSE 'User' END AS author_name
    FROM ForumThread t
    LEFT JOIN Student s ON t.user_role='student' AND t.user_id=s.student_id
    LEFT JOIN Instructor i ON t.user_role='instructor' AND t.user_id=i.instructor_id
    LEFT JOIN Admin a ON t.user_role='admin' AND t.user_id=a.admin_id
    WHERE t.thread_id = ?");
$stmt->bind_param("i", $thread_id);
$stmt->execute();
$thread = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$thread) {
    echo "Thread not found.";
    exit;
}

// Fetch posts
$posts = $conn->query("SELECT p.*, 
    CASE p.user_role 
        WHEN 'student' THEN s.name 
        WHEN 'instructor' THEN i.name 
        WHEN 'admin' THEN a.name 
        ELSE 'User' END AS author_name
    FROM ForumPost p
    LEFT JOIN Student s ON p.user_role='student' AND p.user_id=s.student_id
    LEFT JOIN Instructor i ON p.user_role='instructor' AND p.user_id=i.instructor_id
    LEFT JOIN Admin a ON p.user_role='admin' AND p.user_id=a.admin_id
    WHERE p.thread_id = $thread_id
    ORDER BY p.created_at ASC
");

include 'inc_header_nav.php';
?>

<div class="container">
    <h3><?php echo htmlspecialchars($thread['title']); ?></h3>
    <p><small>Started by <?php echo htmlspecialchars($thread['author_name']); ?> on <?php echo $thread['created_at']; ?></small></p>
    <hr>

    <!-- Posts -->
    <?php while ($post = $posts->fetch_assoc()): ?>
        <div class="card mb-2">
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <small>By <?php echo htmlspecialchars($post['author_name']); ?> on <?php echo $post['created_at']; ?></small>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this post?');">
                        <input type="hidden" name="delete_post" value="<?php echo $post['post_id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>

    <!-- New Post Form -->
    <form method="post" class="mt-4">
        <input type="hidden" name="new_post" value="1">
        <div class="form-group">
            <label for="content">Reply:</label>
            <textarea name="content" id="content" class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Post Reply</button>
    </form>
</div>

<?php
include 'inc_footer.php';
$conn->close();
?>