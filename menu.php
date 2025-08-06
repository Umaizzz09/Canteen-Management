<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session
session_start();

// Include database connection
include('db-connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: student-login.html");
    exit();
}

// Fetch menu items from the database
$query = "SELECT * FROM menu";
$result = $conn->query($query);

// Handle adding items to the cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    // Check if menu_id is set and not empty
    if (!isset($_POST['menu_id']) || empty($_POST['menu_id'])) {
        die('Error: Menu ID is missing.');
    }

    // Get the menu item ID and user ID
    $menu_id = $_POST['menu_id'];
    $user_id = $_SESSION['user_id'];
    $quantity = 1;  // Default quantity is 1

    // Check if the item is already in the cart
    $check_query = "SELECT * FROM cart WHERE user_id = ? AND menu_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $user_id, $menu_id);
    $stmt->execute();
    $result_check = $stmt->get_result();

    if ($result_check->num_rows > 0) {
        // Item already exists in cart, update quantity
        $update_query = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND menu_id = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param("ii", $user_id, $menu_id);
        $stmt_update->execute();
    } else {
        // Insert item into cart
        $insert_query = "INSERT INTO cart (user_id, menu_id, quantity) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param("iii", $user_id, $menu_id, $quantity);
        $stmt_insert->execute();
    }

    $stmt->close();
    $conn->close();
    header("Location: menu.php");  // Redirect to menu page after adding to cart
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Menu - Canteen</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background: #f8f9fa;
    }

    header {
      background-color: #1e88e5;
      padding: 20px;
      text-align: center;
      color: white;
    }

    header h1 {
      margin: 0;
    }

    .nav-buttons {
      display: flex;
      justify-content: center;
      margin-top: 10px;
    }

    .nav-buttons a {
      background-color: #ffffff;
      padding: 10px 20px;
      margin: 0 10px;
      border-radius: 5px;
      color: #1e88e5;
      text-decoration: none;
      font-weight: bold;
    }

    .nav-buttons a:hover {
      background-color: #1565c0;
      color: white;
    }

    .menu-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 20px;
      padding: 30px;
      max-width: 1200px;
      margin: auto;
    }

    .menu-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.2s;
    }

    .menu-card:hover {
      transform: translateY(-5px);
    }

    .menu-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    .menu-content {
      padding: 15px;
    }

    .menu-content h3 {
      margin: 0;
      font-size: 1.2rem;
      color: #333;
    }

    .menu-content p {
      font-size: 0.95rem;
      color: #555;
      margin: 8px 0;
    }

    .menu-content .price {
      font-weight: bold;
      color: #2e7d32;
    }

    .back-link {
      display: inline-block;
      margin: 20px;
      color: #1e88e5;
      text-decoration: none;
      font-weight: bold;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    .add-to-cart-btn {
      display: block;
      background-color: #1e88e5;
      color: white;
      padding: 10px;
      text-align: center;
      font-size: 1rem;
      text-decoration: none;
      margin-top: 15px;
      border-radius: 8px;
    }

    .add-to-cart-btn:hover {
      background-color: #1565c0;
    }
  </style>
</head>
<body>
  <header>
    <h1>Menu</h1>
    <div class="nav-buttons">
      <a href="student-dashboard.php">Dashboard</a>
      <a href="cart.php">Cart</a>
      <a href="order-history.php">Order History</a>
    </div>
  </header>

  <div class="menu-container">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="menu-card">
        <!-- Ensure the image path is correct -->
        <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
        <div class="menu-content">
          <h3><?php echo htmlspecialchars($row['name']); ?></h3>
          <p><?php echo htmlspecialchars($row['description']); ?></p>
          <p class="price">₹<?php echo number_format($row['price'], 2); ?></p>
          <form method="POST" action="menu.php">
            <input type="hidden" name="menu_id" value="<?php echo $row['id']; ?>">
            <button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart</button>
          </form>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</body>
</html>
