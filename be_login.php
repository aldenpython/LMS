<?php
require_once 'db_connection.php';
require_once 'be_auth.php';

ensure_session_started();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    $role = isset($_POST['role']) ? $_POST['role'] : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($role) || empty($email) || empty($password)) {
        set_message("Please enter all fields.", "warning");
        header("Location: index.php");
        exit;
    }

    if ($role === 'student') {
        $stmt = $conn->prepare("SELECT student_id AS id, name, email, password_hash FROM Student WHERE email = ?");
    } elseif ($role === 'instructor') {
        $stmt = $conn->prepare("SELECT instructor_id AS id, name, email, password_hash FROM Instructor WHERE email = ?");
    } elseif ($role === 'admin') {
        $stmt = $conn->prepare("SELECT admin_id AS id, name, email, password_hash FROM Admin WHERE email = ?");
    } else {
        set_message("Invalid role selected.", "danger");
        header("Location: index.php");
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $role;

            

            set_message("Welcome, " . $user['name'] . "!", "success");
        if ($role == 'admin' || $role == 'instructor') {
                header("Location: courses.php");
            } else {
                header("Location: enrollments.php");
            }
            exit;
        }
    }
    set_message("Invalid email or password.", "danger");
    header("Location: index.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>