<?php
session_start();
include('db-connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: student-login.html");
    exit();
}

// Check if order_id is passed in the URL
if (!isset($_GET['order_id'])) {
    die("Invalid request. No order ID found.");
}

$order_id = $_GET['order_id'];

// Fetch order details (you can adjust this based on your order structure)
$query = "SELECT orders.id, orders.total_amount, orders.created_at, users.name
          FROM orders 
          JOIN users ON orders.user_id = users.id
          WHERE orders.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found.");
}

$order = $result->fetch_assoc();
$stmt->close();

// Generate QR code (you can use a library like PHP QR Code library or any other QR generation method)
$qr_content = "Order ID: " . $order['id'] . "\nTotal: ₹" . number_format($order['total_amount'], 2);
$qr_code_image = generateQRCode($qr_content);

// QR code generation function
function generateQRCode($content) {
    // Using a QR code generation library such as PHP QR Code
    // Example with "endroid/qr-code" (you can install via Composer or manually include it)
    require_once 'phpqrcode/qrlib.php';
    $file = 'qrcodes/order_' . rand() . '.png';
    QRcode::png($content, $file);
    return $file;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
            text-align: center;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: auto;
        }
        h2 {
            color: #4CAF50;
        }
        .order-details {
            margin-top: 20px;
            font-size: 1.2em;
        }
        .qr-code {
            margin-top: 30px;
        }
        .action-buttons {
            margin-top: 40px;
        }
        .button {
            background-color: #0077cc;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
        }
        .button:hover {
            background-color: #005fa3;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Order Confirmed</h2>
        <p>Thank you for your order, <strong><?php echo htmlspecialchars($order['name']); ?></strong>!</p>
        
        <div class="order-details">
            <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
            <p><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong>Order Date:</strong> <?php echo date("d M Y, H:i:s", strtotime($order['created_at'])); ?></p>
        </div>

        <div class="qr-code">
            <p>Your QR Code for the Order:</p>
            <img src="<?php echo $qr_code_image; ?>" alt="Order QR Code" />
        </div>

        <div class="action-buttons">
            <a href="student-dashboard.php" class="button">Go to Dashboard</a>
            <a href="menu.php" class="button">Back to Menu</a>
        </div>
    </div>

</body>
</html>
