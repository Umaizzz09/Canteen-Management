<?php
// Include database connection
include('db-connection.php');

// Start the session
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $adminCode = mysqli_real_escape_string($conn, $_POST['adminCode']);
    $password = $_POST['password'];

    // Prepare SQL query to get the admin by admin code
    $query = "SELECT * FROM users WHERE username = ? AND role = 'admin'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $adminCode);

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the admin exists
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $admin['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            header("Location: admin-dashboard.php"); // Redirect to admin dashboard
        } else {
            echo "Incorrect password!";
        }
    } else {
        echo "Admin not found!";
    }

    $stmt->close();
    $conn->close();
}
?>
