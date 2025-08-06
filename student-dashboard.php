<?php
session_start();
include('db-connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: student-login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found!";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Dashboard</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #e0eafc, #cfdef3);
      color: #333;
      overflow-x: hidden;
    }

    header {
      background: #1a237e;
      color: white;
      padding: 30px 20px;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    header h1 {
      font-size: 2em;
      margin-bottom: 10px;
    }

    header nav a {
      color: #c5cae9;
      text-decoration: none;
      margin: 0 15px;
      font-weight: bold;
      transition: color 0.3s ease;
    }

    header nav a:hover {
      color: #ffffff;
    }

    .dashboard-container {
      max-width: 1100px;
      margin: 40px auto;
      padding: 20px;
      animation: fadeIn 1s ease;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(20px);}
      to {opacity: 1; transform: translateY(0);}
    }

    .card {
      background: #ffffff;
      border-radius: 12px;
      padding: 25px;
      margin-bottom: 30px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .card h2 {
      margin-bottom: 15px;
      color: #1a237e;
    }

    .card p {
      margin: 5px 0;
      font-size: 16px;
    }

    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }

    .btn {
      background: #1a237e;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      font-size: 16px;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background: #303f9f;
    }

    @media (max-width: 600px) {
      header h1 {
        font-size: 1.5em;
      }

      .actions {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>
  <header>
    <h1>Welcome, <?php echo htmlspecialchars($username); ?> 👋</h1>
    <nav>
      <a href="menu.php">Menu</a>
      <a href="cart.php">Cart</a>
      <a href="order-history.php">History</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <div class="dashboard-container">
    <div class="card">
      <h2>Account Info</h2>
      <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
      <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    </div>

    <div class="card">
      <h2>Quick Actions</h2>
      <div class="actions">
        <a href="menu.php" class="btn">🍽️ Browse Menu</a>
        <a href="cart.php" class="btn">🛒 View Cart</a>
        <a href="order-history.php" class="btn">📜 Order History</a>
      </div>
    </div>
  </div>
</body>
</html>
