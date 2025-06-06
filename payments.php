<?php

require_once 'db_connection.php';
require_once 'be_auth.php';
require_admin(); // Only allow admins

$result = $conn->query("
    SELECT p.*, s.name AS student_name, c.title AS course_title
    FROM Payment p
    JOIN Student s ON p.student_id = s.student_id
    JOIN Course c ON p.course_id = c.course_id
    ORDER BY p.payment_date DESC
");
include 'inc_header_nav.php';
?>

<div class="container">
    <h2>All Payments</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Student</th>
                <th>Course</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['invoice_number']); ?></td>
                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                <td><?php echo htmlspecialchars($row['course_title']); ?></td>
                <td><?php echo number_format($row['amount'], 2); ?></td>
                <td><?php echo $row['payment_date']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
include 'inc_footer.php';
$conn->close();
?>