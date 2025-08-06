<?php
session_start();
include('db-connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: student-login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$query = "SELECT cart.menu_id, menu.name, menu.price, cart.quantity 
          FROM cart 
          JOIN menu ON cart.menu_id = menu.id 
          WHERE cart.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle payment confirmation
if ($_SERVER["REQUEST_METHOD"] == "POST" && count($cart_items) > 0) {
    // Check if the total_amount column exists in the orders table
    $check_column = $conn->query("DESCRIBE orders");
    $columns = [];
    while ($row = $check_column->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    // If the 'total_amount' column does not exist, we log and halt.
    if (!in_array('total_amount', $columns)) {
        die('Error: The "total_amount" column is missing from the orders table.');
    }

    // Insert into orders table
    $insert_order = $conn->prepare("INSERT INTO orders (user_id, total_amount, created_at) VALUES (?, ?, NOW())");
    $insert_order->bind_param("id", $user_id, $total);
    $insert_order->execute();
    $order_id = $insert_order->insert_id;
    $insert_order->close();

    // Insert order items
    $insert_item = $conn->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart_items as $item) {
        $menu_id = $item['menu_id'];
        $qty = $item['quantity'];
        $price = $item['price'];
        $insert_item->bind_param("iiid", $order_id, $menu_id, $qty, $price);
        $insert_item->execute();
    }
    $insert_item->close();

    // Clear cart after successful order placement
    $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $delete_cart->bind_param("i", $user_id);
    $delete_cart->execute();
    $delete_cart->close();

    // Redirect to payment success page
    $conn->close();
    header("Location: order-success.php?order_id=" . $order_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Payment</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f6f8;
      padding: 20px;
    }
    .container {
      max-width: 700px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
    }
    .item {
      border-bottom: 1px solid #ddd;
      padding: 10px 0;
    }
    .total {
      text-align: right;
      font-size: 1.2em;
      margin-top: 20px;
    }
    .actions {
      text-align: right;
      margin-top: 20px;
    }
    .actions a, .actions button {
      padding: 10px 20px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      margin-left: 10px;
    }
    .back-btn {
      background: #999;
      color: white;
    }
    .confirm-btn {
      background: #0077cc;
      color: white;
      border: none;
      cursor: pointer;
    }
    .confirm-btn:hover {
      background: #005fa3;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Confirm Your Order</h2>
    <?php if (count($cart_items) === 0): ?>
      <p>Your cart is empty. <a href="menu.php">Back to Menu</a></p>
    <?php else: ?>
      <?php foreach ($cart_items as $item): ?>
        <div class="item">
          <p><strong><?php echo htmlspecialchars($item['name']); ?></strong></p>
          <p>Quantity: <?php echo $item['quantity']; ?> | Price: ₹<?php echo number_format($item['price'], 2); ?></p>
        </div>
      <?php endforeach; ?>
      <div class="total">
        Total Amount: ₹<?php echo number_format($total, 2); ?>
      </div>
      <form method="POST" class="actions">
        <a href="cart.php" class="back-btn">Back to Cart</a>
        <button type="submit" class="confirm-btn">Confirm Order</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
