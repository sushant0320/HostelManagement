<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "hostel_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch available rooms (added room_size)
$sql = "SELECT id, room_number, capacity, status, room_photo, room_info, room_fee, room_size FROM rooms";
$result = $conn->query($sql);

$username = htmlspecialchars($_SESSION['username']);
$role = htmlspecialchars($_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>View Rooms - Student</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap');

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: #fff7ed;
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
      font-size: 1.5rem;
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

    h2 {
      color: #ea580c;
      text-align: center;
      margin-top: 30px;
    }

    table {
      width: 100%;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
      border-collapse: collapse;
      margin: 30px auto;
      overflow: hidden;
    }

    th, td {
      padding: 14px 18px;
      border-bottom: 1px solid #f0f0f0;
      text-align: left;
      vertical-align: middle;
    }

    th {
      background: #ea580c;
      color: white;
      font-size: 16px;
    }

    tr:nth-child(even) {
      background: #fef3c7;
    }

    tr:hover {
      background: #fde68a;
    }

    img {
      width: 120px;
      height: 90px;
      object-fit: cover;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .room-info {
      max-width: 300px;
      white-space: pre-line;
      color: #333;
      font-size: 14px;
    }

    @media (max-width: 600px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }

      th {
        position: sticky;
        top: 0;
        background: #ea580c;
        color: white;
      }

      td {
        border: none;
        padding: 10px;
        text-align: right;
        position: relative;
      }

      td::before {
        content: attr(data-label);
        position: absolute;
        left: 15px;
        font-weight: bold;
        color: #ea580c;
        text-transform: uppercase;
      }
    }
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
    <a href="payment.php">Payment</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<h2>Available Rooms</h2>

<table>
  <thead>
    <tr>
      <th>Room Number</th>
      <th>Capacity</th>
      <th>Status</th>
      <th>Size</th>
      <th>Fee (Rs.)</th>
      <th>Image</th>
      <th>Info</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td data-label="Room Number"><?php echo htmlspecialchars($row['room_number']); ?></td>
              <td data-label="Capacity"><?php echo htmlspecialchars($row['capacity']); ?></td>
              <td data-label="Status"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
              <td data-label="Size"><?php echo htmlspecialchars($row['room_size']); ?> sq.ft</td>
              <td data-label="Fee (Rs.)"><?php echo "Rs. " . number_format($row['room_fee']); ?></td>
              <td data-label="Image">
                <?php if (!empty($row['room_photo']) && file_exists($row['room_photo'])): ?>
                  <img src="<?php echo htmlspecialchars($row['room_photo']); ?>" alt="Room Photo">
                <?php else: ?>
                  No Image
                <?php endif; ?>
              </td>
              <td data-label="Info" class="room-info">
                <?php echo !empty($row['room_info']) ? nl2br(htmlspecialchars($row['room_info'])) : "No Info"; ?>
              </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="7" style="text-align:center; color:#666;">No rooms available</td></tr>
    <?php endif; ?>
  </tbody>
</table>

</body>
</html>
<?php $conn->close(); ?>
