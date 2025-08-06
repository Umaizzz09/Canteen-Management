<?php
// Include database connection
include('db-connection.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize it
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    // Validate if passwords match
    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit();
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL query to insert user into the users table
    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'student')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    // Execute the query
    if ($stmt->execute()) {
        echo "Registration successful!";
        header("Location: student-login.html"); // Redirect to login page
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
