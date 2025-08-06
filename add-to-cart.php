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

// Get the menu item ID from the POST request
$menu_item_id = $_POST['menu_item_id'];

// Check if the item already exists in the cart
$query = "SELECT * FROM cart_items WHERE user_id = ? AND menu_item_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $menu_item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // If the item exists, increase the quantity
    $cart_item = $result->fetch_assoc();
    $new_quantity = $cart_item['quantity'] + 1;

    $update_query = "UPDATE cart_items SET quantity = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
    $update_stmt->execute();
} else {
    // If the item doesn't exist, add it to the cart
    $insert_query = "INSERT INTO cart_items (user_id, menu_item_id, quantity) VALUES (?, ?, 1)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ii", $user_id, $menu_item_id);
    $insert_stmt->execute();
}

$insert_stmt->close();
$stmt->close();
$conn->close();

header("Location: menu.php"); // Redirect back to the menu page
exit();
?>
