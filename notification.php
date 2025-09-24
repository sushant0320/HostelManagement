<?php
session_start();

// Check if logged in as student
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

// Fetch all notifications for the student
$notifications = [];
$stmt = $conn->prepare("
    SELECT f.id, u.username AS admin_name, f.message, f.sent_at
    FROM fee_notification f
    JOIN users u ON f.admin_id = u.id
    WHERE f.user_id = ?
    ORDER BY f.sent_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Fee Notifications - Hostel Management</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap');
    body { 
           margin:0;
           font-family:'Inter',sans-serif; 
           background:#f0f4f8; color:#333; 
           display:flex; 
           flex-direction:column; 
           min-height:100vh; 
          }
    header { 
              background:#ea580c; 
              color:white; 
              padding:20px 40px; 
              display:flex; 
              justify-content:space-between; 
              align-items:center; 
            }
    header h1 { 
                margin:0; 
                font-size:1.8rem; 
              }
    nav a { 
            color:white; 
            text-decoration:none; 
            margin-left:20px; 
            font-weight:600; 
          }
    nav a.logout-btn { 
                      background:#dc2626; 
                      padding:8px 14px; 
                      border-radius:5px; 
                    }
    .container { 
                max-width:1100px; 
                margin:40px auto; 
                padding:0 20px; 
                flex:1; 
              }
    h2 { 
        text-align:center; 
        color:#ea580c; 
        margin-bottom:20px; 
       }
    table { 
            width:100%; 
            border-collapse:collapse; 
            background:#fff; 
            border-radius:10px; 
            overflow:hidden;
            box-shadow:0 4px 15px rgba(0,0,0,0.05);
           }
    th, td { 
            padding:12px; 
            border-bottom:1px solid #eee; 
            text-align:left; 
          }
    th { 
        background:#ea580c; 
        color:white; 
      }
    tr:nth-child(even) { 
                        background:#fef3c7;
                       }
    tr:hover { 
              background:#fde68a;
             }
    .no-data { 
              text-align:center; 
              padding:20px; 
              color:#666; 
            }
    footer { text-align:center; margin-top:40px; padding:15px 0; background:#ea580c; color:white; font-size:0.9rem; border-top:3px solid #b45309; }
  </style>
</head>
<body>
  <header>
    <h1>Hostel Management System</h1>
    <nav>
      <a href="user_dashboard.php">Home</a>
      <a href="view_rooms.php">View Rooms</a>
      <a href="my_allocation.php">My Allocation</a>
      <a href="payment.php">Payment</a>
      <a href="notifications.php" aria-current="page">Notifications</a>
      <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
  </header>

  <div class="container">
    <h2>Fee Notifications</h2>

    <?php if (empty($notifications)): ?>
      <p class="no-data">No notifications available.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>S.N</th>
            <th>Message</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($notifications as $note): ?>
            <tr>
              <td><?php echo $note['id']; ?></td>
              <td><?php echo htmlspecialchars($note['message']); ?></td>
              <td><?php echo htmlspecialchars($note['sent_at']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <footer>
    &copy; <?php echo date("Y"); ?> Hostel Management System. All rights reserved.
  </footer>
</body>
</html>
