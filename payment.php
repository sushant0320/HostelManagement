<?php
session_start();

// Check if logged in as a user (student)
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("User ID not found in session.");
}

$conn = new mysqli("localhost", "root", "", "hostel_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle payment submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_payment'])) {
    $amount = floatval($_POST['amount']);
    $payment_method = $_POST['payment_method'];
    $transaction_id = trim($_POST['transaction_id']);
    $remark = trim($_POST['remark'] ?? ''); // Optional remark field
    $payment_date = date("Y-m-d");

    // Validate inputs
    if ($amount <= 0) {
        $message = "Invalid payment amount.";
    } elseif (empty($payment_method) || empty($transaction_id)) {
        $message = "Payment method and transaction ID are required.";
    } else {
        // Insert payment record (status removed)
        $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, payment_method, transaction_id, payment_date, remark) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $message = "Error preparing query: " . $conn->error;
        } else {
            $stmt->bind_param("idssss", $user_id, $amount, $payment_method, $transaction_id, $payment_date, $remark);
            if ($stmt->execute()) {
                $message = "Payment recorded successfully!";
            } else {
                $message = "Error recording payment: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch payment history
$payments = [];
$stmt = $conn->prepare("SELECT amount, payment_method, transaction_id, payment_date, remark 
                        FROM payments WHERE user_id = ? ORDER BY payment_date DESC");
if ($stmt === false) {
    $message = "Error preparing payment history query: " . $conn->error;
} else {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
    } else {
        $message = "Error fetching payment history: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch allocated room fee (to display expected amount)
$room_fee = 0;
$stmt = $conn->prepare("SELECT r.room_fee 
                       FROM room_allocations ra 
                       JOIN rooms r ON ra.room_id = r.id 
                       WHERE ra.student_id = ?");
if ($stmt === false) {
    $message = "Error preparing room fee query: " . $conn->error;
} else {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $room_fee = $row['room_fee'];
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Payment - Hostel Management</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap');
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: #fff8f0;
      color: #333;
    }
    header {
      background: #ea580c;
      color: white;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }
    header h1 { margin: 0; font-weight: 700; font-size: 1.8rem; }
    nav a {
      color: white; text-decoration: none; margin-left: 25px; font-weight: 600;
      transition: color 0.3s ease;
    }
    nav a:hover, nav a[aria-current="page"] { color: #fbbf24; }
    .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
    h2 { color: #ea580c; text-align: center; margin-bottom: 25px; }
    .payment-form, .payment-history {
      background: white; border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      padding: 25px; margin-bottom: 30px;
    }
    .payment-form input, .payment-form select, .payment-form textarea {
      width: 100%; padding: 10px; margin: 8px 0;
      border: 1px solid #ccc; border-radius: 6px; font-size: 14px;
    }
    .payment-form button {
      width: 100%; padding: 12px; background: #ea580c;
      color: white; border: none; border-radius: 6px; font-weight: bold;
      cursor: pointer; margin-top: 10px;
    }
    .payment-form button:hover { background: #c2410c; }
    .message { color: green; font-weight: bold; text-align: center; margin-bottom: 15px; }
    .error { color: red; font-weight: bold; text-align: center; margin-bottom: 15px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px; border-bottom: 1px solid #f0f0f0; text-align: left; }
    th { background: #ea580c; color: white; }
    tr:nth-child(even) { background: #fef3c7; }
    tr:hover { background: #fde68a; }
  </style>
</head>
<body>
  <header>
    <h1>Hostel Management System</h1>
    <nav>
      <a href="user_dashboard.php">Home</a>
      <a href="view_rooms.php">View Rooms</a>
      <a href="request_room.php">Request Room</a>
      <a href="my_allocation.php">My Allocation</a>
      <a href="payment.php" aria-current="page">Payment</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <div class="container">
    <h2>Make a Payment</h2>

    <?php if ($message): ?>
      <div class="<?php echo strpos($message, 'Error') !== false ? 'error' : 'message'; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <div class="payment-form">
      <form method="POST" action="">
        <label>Amount (Rs.):</label>
        <input type="number" name="amount" min="1">

        <label>Payment Method:</label>
        <select name="payment_method" required>
          <option value="" disabled selected>Select Payment Method</option>
          <option value="bank_transfer">Bank Transfer</option>
          <option value="mobile_payment">Mobile Payment</option>
        </select>

        <label>Transaction ID:</label>
        <input type="text" name="transaction_id" required placeholder="Enter transaction ID">

        <label>Remark:</label>
        <textarea name="remark" placeholder="Enter any remarks (e.g., payment purpose)"></textarea>

        <button type="submit" name="submit_payment">Submit Payment</button>
      </form>
    </div>

    <h2>Payment History</h2>
    <div class="payment-history">
      <?php if (empty($payments)): ?>
        <p style="text-align:center; color:#666;">No payment history available.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Amount (Rs.)</th>
              <th>Payment Method</th>
              <th>Transaction ID</th>
              <th>Remark</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $payment): ?>
              <tr>
                <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                <td><?php echo number_format($payment['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $payment['payment_method']))); ?></td>
                <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                <td><?php echo htmlspecialchars($payment['remark'] ?? 'N/A'); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
