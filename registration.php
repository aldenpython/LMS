<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db_connection.php'; // make sure this file has your DB credentials
    

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role']; // 'Student' or 'Instructor'

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Decide which table to insert into
    if ($role === 'Student') {
        $stmt = $conn->prepare("INSERT INTO Student (name, email, password_hash) VALUES (?, ?, ?)");
    } else {
        $stmt = $conn->prepare("INSERT INTO Instructor (name, email, password_hash) VALUES (?, ?, ?)");
    }

    $stmt->bind_param("sss", $name, $email, $password_hash);

    if ($stmt->execute()) {
        echo "
        <p>✅ Registration successful! Redirecting to login in 10 seconds...</p>
        <script>
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 10000); // 10000ms = 10s
        </script>
        ";
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- Registration Form UI -->
<h2>Register</h2>
<form method="POST" action="registration.php">
  <label>Name:</label><br>
  <input type="text" name="name" required><br><br>

  <label>Email:</label><br>
  <input type="email" name="email" required><br><br>

  <label>Password:</label><br>
  <input type="password" name="password" required><br><br>

  <label>Role:</label><br>
  <select name="role" required>
    <option value="Student">Student</option>
    <option value="Instructor">Instructor</option>
  </select><br><br>

  <input type="submit" value="Registration">
</form>
