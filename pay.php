<?php

require_once 'db_connection.php';
require_once 'be_auth.php';
require_login();

define('ENCRYPT_KEY', 'your-32-char-secret-key-here'); // Use a secure key!

function encrypt_data($data) {
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', ENCRYPT_KEY, 0, $iv);
    return base64_encode($iv . $encrypted);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_student()) {
    $student_id = $_SESSION['user_id'];
    $course_id = intval($_POST['course_id']);

    // Get course price
    $stmt = $conn->prepare("SELECT price FROM Course WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->bind_result($amount);
    $stmt->fetch();
    $stmt->close();

    // Get card details
    $card_number = $_POST['card_number'];
    $expiry = $_POST['expiry'];
    $card_name = $_POST['card_name'];

    // Encrypt card details
    $encrypted_card = encrypt_data($card_number);
    $encrypted_expiry = encrypt_data($expiry);
    $encrypted_name = encrypt_data($card_name);

    // Store only last 4 digits for reference
    $last4 = substr($card_number, -4);

    $invoice_number = uniqid('INV');
    $stmt = $conn->prepare("INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $student_id, $course_id, $amount, $invoice_number);
    if ($stmt->execute()) {
        // Optionally, store encrypted card info in a separate table (not recommended for real apps)
        $payment_id = $stmt->insert_id;
        $stmt2 = $conn->prepare("INSERT INTO PaymentCard (payment_id, card_last4, card_encrypted, expiry_encrypted, name_encrypted) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("issss", $payment_id, $last4, $encrypted_card, $encrypted_expiry, $encrypted_name);
        $stmt2->execute();
        $stmt2->close();

        set_message("Payment successful!", "success");
    } else {
        set_message("Payment failed.", "danger");
    }
    $stmt->close();
    header("Location: enrollments.php");
    exit;
}

// If GET, show the payment form
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$course = null;
if ($course_id > 0) {
    $stmt = $conn->prepare("SELECT title, price FROM Course WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->bind_result($title, $price);
    $stmt->fetch();
    $stmt->close();
    $course = ['title' => $title, 'price' => $price];
}

include 'inc_header_nav.php';
?>

<div class="container">
    <h2>Pay for Course</h2>
    <?php display_message(); ?>

    <?php if ($course): ?>
    <form action="pay.php" method="post" class="mb-4" autocomplete="off">
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        <div class="form-group">
            <label>Course:</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($course['title']); ?>" readonly>
        </div>
        <div class="form-group">
            <label>Amount ($):</label>
            <input type="text" class="form-control" value="<?php echo number_format($course['price'], 2); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="card_number">Card Number</label>
            <input type="text" name="card_number" id="card_number" maxlength="16" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="expiry">Expiry (MM/YY)</label>
            <input type="text" name="expiry" id="expiry" maxlength="5" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="card_name">Name on Card</label>
            <input type="text" name="card_name" id="card_name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Pay</button>
    </form>
    <?php else: ?>
        <div class="alert alert-danger">Invalid course.</div>
    <?php endif; ?>
</div>

<?php
include 'inc_footer.php';
$conn->close();
?>