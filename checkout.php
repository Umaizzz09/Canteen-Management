<?php
session_start();
include('db-connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: student-login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$query = "SELECT cart.id AS cart_id, menu.name, menu.price, cart.quantity, menu.id AS menu_id
          FROM cart
          JOIN menu ON cart.menu_id = menu.id
          WHERE cart.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Checkout</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f6f8;
      margin: 0;
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
      margin-bottom: 20px;
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
          <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
          Quantity: <?php echo $item['quantity']; ?> | 
          Price: ₹<?php echo number_format($item['price'], 2); ?><br>
          Subtotal: ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
        </div>
      <?php endforeach; ?>
      <div class="total">
        Grand Total: ₹<?php echo number_format($total, 2); ?>
      </div>
      <form action="payment.php" method="POST" class="actions">
        <input type="hidden" name="total" value="<?php echo $total; ?>">
        <button type="submit" class="confirm-btn">Confirm & Pay</button>
        <a href="cart.php" class="back-btn">Back to Cart</a>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
