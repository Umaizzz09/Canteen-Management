<?php
// Start the session
session_start();

// Include database connection
include('db-connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: student-login.html");
    exit();
}

// Get the user ID from session
$user_id = $_SESSION['user_id'];

// Get the cart item ID from the POST request
$cart_item_id = $_POST['cart_item_id'];

// Remove the item from the cart
$query = "DELETE FROM cart_items WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $cart_item_id, $user_id);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: cart.php"); // Redirect back to the cart page
exit();
?>
