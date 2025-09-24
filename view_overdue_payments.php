<?php
session_start();

// Validate session
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Sanitize session data
$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$role = htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8');

// Database connection
$conn = new mysqli("localhost", "root", "", "hostel_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch overdue payments
$stmt = $conn->prepare("SELECT id, user_id, amount, due_date, payment_date FROM payments WHERE payment_status = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$status = 'overdue';
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Payments - Hostel Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f9fafb;
            color: #333;
        }
        header {
            background: #1c3066;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 3px 6px rgba(0,0,0,0.15);
        }
        header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 1.9rem;
            letter-spacing: 1px;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin-left: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        nav a:hover {
            color: #93c5fd;
        }
        nav a.logout-btn {
            background: #dc2626;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: bold;
        }
        nav a.logout-btn:hover {
            background: #b91c1c;
            color: #fff;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .welcome {
            font-size: 1.2rem;
            margin-bottom: 35px;
            color: #374151;
        }
        .welcome span {
            font-weight: 700;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #1c3066;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tr:hover {
            background-color: #f1f5f9;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #1d4ed8;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: #1e40af;
            color: #fff;
        }
        footer {
            text-align: center;
            margin-top: 50px;
            padding: 15px 0;
            color: #374151;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>Hostel Management System</h1>
        <nav>
            <a href="manage_rooms.php">Manage Rooms</a>
            <a href="view_requests.php">View Room Requests</a>
            <a href="manage_students.php">Manage Students</a>
            <a href="admin_payments.php">Payment Details</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <div class="container">
        <div class="welcome">
            Welcome, <span class="role"><?php echo $role; ?></span> <span class="username"><?php echo $username; ?></span>.
        </div>

        <h2>Overdue Payments</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Payment Date</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo $row['amount']; ?></td>
                    <td><?php echo $row['due_date'] ?? 'N/A'; ?></td>
                    <td><?php echo $row['payment_date'] ?? 'N/A'; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <a href="admin_payments.php" class="back-btn">Back to Payment Details</a>
    </div>

    <footer>
        &copy; <?php echo date("Y"); ?> Hostel Management System. All rights reserved.
    </footer>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>