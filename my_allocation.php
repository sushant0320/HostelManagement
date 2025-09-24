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

// Fetch student's allocated room details (added room_photo)
$sql = "SELECT r.room_number, r.capacity, r.status, r.room_photo, ra.allocation_date
        FROM room_allocations ra
        JOIN rooms r ON ra.room_id = r.id
        WHERE ra.student_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$allocation = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>My Room Allocation</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap');

  body {
    font-family: 'Inter', sans-serif;
    margin: 0;
    background: #fff8f0; /* light orange */
    color: #7c2d12;
  }

  /* Dashboard-style Nav Bar */
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
  }
  nav a {
    color: white;
    text-decoration: none;
    margin-left: 25px;
    font-weight: 600;
    transition: color 0.3s ease;
  }
  nav a:hover,
  nav a[aria-current="page"] {
    color: #fbbf24; /* yellow on hover/current */
  }

  h1.page-title {
    text-align: center;
    color: #ea580c;
    margin: 30px 0 20px;
    font-weight: 700;
  }

  .room-details {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    max-width: 500px;
    margin: auto;
    border-left: 6px solid #f97316;
    text-align: center;
  }

  .room-details p {
    font-size: 16px;
    margin: 12px 0;
    text-align: left;
  }

  .room-details strong {
    color: #c2410c;
  }

  .room-image {
    margin: 15px 0;
  }

  .room-image img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
  }

  .no-allocation {
    font-style: italic;
    font-size: 18px;
    color: #9a3412;
  }

  @media (max-width: 640px) {
    header { flex-wrap: wrap; gap: 15px; justify-content: center; }
    nav a { margin-left: 15px; margin-top: 5px; }
  }
</style>
</head>
<body>

<header>
  <h1>Hostel Management System</h1>
  <nav>
    <a href="user_dashboard.php">Home</a>
    <a href="view_rooms.php">View Room</a>
    <a href="request_room.php">Request Room</a>
    <a href="my_allocation.php" aria-current="page">My Allocation</a>
    <a href="payment.php">Payment</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<h1 class="page-title">My Room Allocation</h1>

<div class="room-details">
<?php if ($allocation): ?>
  <p><strong>Room Number:</strong> <?php echo htmlspecialchars($allocation['room_number']); ?></p>
  <p><strong>Capacity:</strong> <?php echo htmlspecialchars($allocation['capacity']); ?></p>
  <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($allocation['status'])); ?></p>
  <p><strong>Allocated On:</strong> <?php echo htmlspecialchars($allocation['allocation_date']); ?></p>

  <div class="room-image">
    <?php if (!empty($allocation['room_photo']) && file_exists($allocation['room_photo'])): ?>
      <img src="<?php echo htmlspecialchars($allocation['room_photo']); ?>" alt="Room Photo">
    <?php else: ?>
      <p style="color:#9a3412; font-style:italic;">No Image Available</p>
    <?php endif; ?>
  </div>

<?php else: ?>
  <p class="no-allocation">You do not have any room allocated yet.</p>
<?php endif; ?>
</div>

</body>
</html>
