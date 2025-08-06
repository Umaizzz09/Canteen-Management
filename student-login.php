<?php
// Include database connection
include('db-connection.php');

// Start the session
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Prepare SQL query to get the user by username and role
    $query = "SELECT * FROM users WHERE username = ? AND role = 'student'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: student-dashboard.php"); // Redirect to student dashboard
        } else {
            echo "Incorrect password!";
        }
    } else {
        echo "User not found!";
    }

    $stmt->close();
    $conn->close();
}
?>
