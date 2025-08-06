<?php
// Include database connection
include('db-connection.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize it
    $adminCode = $_POST['adminCode'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Validate admin code (example: checking for a specific code)
    if ($adminCode !== 'admin123') {
        echo "Invalid admin code!";
        exit();
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL query to insert user into the users table
    $query = "INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $hashedPassword);

    // Execute the query
    if ($stmt->execute()) {
        echo "Registration successful!";
        header("Location: admin-login.html"); // Redirect to login page
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
