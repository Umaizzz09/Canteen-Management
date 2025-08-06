<?php
session_start();
include('db-connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: student-login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT menu.name, menu.price, cart.quantity, cart.id 
          FROM cart 
          JOIN menu ON cart.menu_id = menu.id 
          WHERE cart.user_id = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die('SQL prepare error: ' . $conn->error);
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    die('SQL execute error: ' . $stmt->error);
}

$result = $stmt->get_result();
$cart_items = $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];

$stmt->close();
$conn->close();

function calculateCartTotal($cart_items) {
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return number_format($total, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Cart</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background: #eef2f5;
    }
    header {
      background-color: #0077cc;
      padding: 20px;
      color: white;
      text-align: center;
    }
    header a {
      color: #ffffff;
      text-decoration: none;
      margin: 0 10px;
      font-weight: bold;
    }
    .cart-container {
      max-width: 800px;
      margin: 30px auto;
      padding: 20px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .cart-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px;
      margin-bottom: 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      transition: transform 0.2s ease;
    }
    .cart-item:hover {
      transform: scale(1.01);
      background: #f9f9f9;
    }
    .cart-details {
      flex-grow: 1;
    }
    .cart-details p {
      margin: 5px 0;
    }
    .cart-actions {
      text-align: right;
    }
    .cart-actions button {
      background-color: #e53935;
      border: none;
      color: white;
      padding: 8px 12px;
      border-radius: 5px;
      cursor: pointer;
    }
    .cart-actions button:hover {
      background-color: #c62828;
    }
    .cart-total {
      text-align: right;
      margin-top: 20px;
      font-size: 1.2em;
      font-weight: bold;
    }
    .buttons {
      margin-top: 20px;
      text-align: right;
    }
    .buttons a {
      text-decoration: none;
      padding: 10px 18px;
      margin-left: 10px;
      border-radius: 6px;
      background: #0077cc;
      color: white;
      font-weight: bold;
      transition: background 0.2s ease;
    }
    .buttons a:hover {
      background: #005fa3;
    }
    .empty {
      text-align: center;
      padding: 40px;
      color: #888;
    }
  </style>
</head>
<body>
  <header>
    <h1>Your Cart</h1>
    <a href="student-dashboard.php">Dashboard</a> |
    <a href="menu.php">Back to Menu</a>
  </header>

  <div class="cart-container">
    <?php if (count($cart_items) > 0): ?>
      <?php foreach ($cart_items as $item): ?>
        <div class="cart-item">
          <div class="cart-details">
            <p><strong><?php echo htmlspecialchars($item['name']); ?></strong></p>
            <p>Price: ₹<?php echo number_format($item['price'], 2); ?></p>
            <p>Quantity: <?php echo $item['quantity']; ?></p>
            <p>Total: ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
          </div>
          <div class="cart-actions">
            <form action="remove-from-cart.php" method="POST">
              <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
              <button type="submit">Remove</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty">🛒 Your cart is empty.</div>
    <?php endif; ?>

    <?php if (count($cart_items) > 0): ?>
      <div class="cart-total">
        Total: ₹<?php echo calculateCartTotal($cart_items); ?>
      </div>
      <div class="buttons">
        <a href="menu.php">⬅ Back to Menu</a>
        <a href="checkout.php">Checkout ➡</a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
