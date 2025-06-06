<?php

require_once 'db_connection.php';
require_once 'be_auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && is_student()) {
    $student_id = $_SESSION['user_id'];
    $course_id = intval($_POST['course_id']);

    // Get course price
    $stmt = $conn->prepare("SELECT price FROM Course WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->bind_result($amount);
    $stmt->fetch();
    $stmt->close();

    // Simulate payment (for real payment, integrate Stripe/PayPal here)
    $invoice_number = uniqid('INV');
    $stmt = $conn->prepare("INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $student_id, $course_id, $amount, $invoice_number);
    if ($stmt->execute()) {
        set_message("Payment successful!", "success");
    } else {
        set_message("Payment failed.", "danger");
    }
    $stmt->close();
}
header("Location: enrollments.php");
exit;
?>