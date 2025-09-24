<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
$role = htmlspecialchars($_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard - Hostel Management</title>
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

    header h1 {
      margin: 0;
      font-weight: 700;
      font-size: 1.8rem;
      letter-spacing: 1px;
    }

    nav a {
      color: white;
      text-decoration: none;
      margin-left: 25px;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    nav a:hover {
      color: #fbbf24;
    }

    .container {
      max-width: 1100px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .welcome {
      font-size: 1.2rem;
      margin-bottom: 40px;
    }

    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
    }

    .card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      padding: 20px;
      transition: transform 0.2s ease;
      cursor: pointer;
      text-align: center;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(234, 88, 12, 0.3);
    }

    .card img {
      max-width: 100px;
      margin-bottom: 15px;
    }

    .card h3 {
      margin: 0 0 10px;
      color: #ea580c;
    }

    .card p {
      font-size: 0.95rem;
      color: #555;
    }

    footer {
      text-align: center;
      padding: 25px;
      font-size: 0.9rem;
      color: #666;
    }
  </style>
</head>
<body>

<header>
  <h1>Hostel Management System</h1>
  <nav>
    <a href="view_rooms.php">View Rooms</a>
    <a href="request_room.php">Request Room</a>
    <a href="my_allocation.php">My Allocation</a>
    <a href="payment.php">Payment</a>
    <a href="notification.php">Notification</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<div class="container">
  <div class="welcome">
    Welcome, <strong><?php echo $username; ?></strong>.
  </div>

  <div class="dashboard-grid">
    <a href="view_rooms.php" class="card">
      <img src="images/room.png" alt="View Rooms">
      <h3>View Rooms</h3>
      <p>Browse available hostel rooms and their details.</p>
    </a>

    <a href="request_room.php" class="card">
      <img src="images/request.png" alt="Request Room">
      <h3>Request Room</h3>
      <p>Submit a request for room allocation.</p>
    </a>

    <a href="my_allocation.php" class="card">
      <img src="images/allocation.png" alt="My Allocation">
      <h3>My Allocation</h3>
      <p>View your current room allocation status.</p>
    </a>

    <a href="payment.php" class="card">
      <img src="images/pay.png" alt="Payment">
      <h3>Payment</h3>
      <p>Pay hostel room fees securely.</p>
    </a>

    <a href="notification.php" class="card">
      <img src="images/notification.png" alt="Notification">
      <h3>Notification</h3>
      <p>Check messages from the admin.</p>
    </a>
  </div>
</div>

<footer>
  &copy; <?php echo date("Y"); ?> Hostel Management System. All rights reserved.
</footer>

</body>
</html>
