<?php
session_start();

// Admin access check
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "hostel_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Handle Delete Student
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($delete_id == $_SESSION['user_id']) {
        $message = "You cannot delete your own account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_students.php");
        exit();
    }
}

// Handle Room Allocation
if (isset($_POST['allocate_room'])) {
    $student_id = intval($_POST['student_id']);
    $room_id = intval($_POST['room_id']);

    $stmt = $conn->prepare("SELECT id FROM room_allocations WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $stmt = $conn->prepare("UPDATE room_allocations SET room_id = ?, allocation_date = CURDATE() WHERE student_id = ?");
        $stmt->bind_param("ii", $room_id, $student_id);
        $stmt->execute();
        $message = "Room allocation updated successfully.";
    } else {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO room_allocations (student_id, room_id, allocation_date) VALUES (?, ?, CURDATE())");
        $stmt->bind_param("ii", $student_id, $room_id);
        $stmt->execute();
        $message = "Room allocated successfully.";
    }
    $stmt->close();
}

// Fetch students
$students = [];
$result = $conn->query("SELECT id, username, fullname, email FROM users WHERE role = 'user' ORDER BY username ASC");
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Fetch rooms
$rooms = [];
$result2 = $conn->query("SELECT id, room_number FROM rooms WHERE status = 'available' ORDER BY room_number ASC");
while ($row = $result2->fetch_assoc()) {
    $rooms[] = $row;
}

// Fetch allocations
$allocations = [];
$sqlAlloc = "SELECT ra.student_id, r.room_number FROM room_allocations ra JOIN rooms r ON ra.room_id = r.id";
$resAlloc = $conn->query($sqlAlloc);
while ($row = $resAlloc->fetch_assoc()) {
    $allocations[$row['student_id']] = $row['room_number'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Students - Admin</title>
<style>
  body {
    font-family: 'Inter', sans-serif;
    background: #f7f9fc;
    margin: 0;
    padding: 0;
  }
  header {
    background: #1c3066ff;
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
    color: #93c5fd;
  }
  nav a.logout-btn {
    background: #dc2626;
    padding: 8px 14px;
    border-radius: 5px;
    font-weight: bold;
  }
  nav a.logout-btn:hover {
    background: #b91c1c;
    color: #fff;
  }
  .container {
    padding: 20px 40px;
    max-width: 1200px;
    margin: 30px auto;
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 8px 20px rgba(29, 78, 216, 0.3);
  }
  h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #1d4ed8;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.15);
  }
  table {
    width: 100%;
    background: white;
    border-collapse: collapse;
    margin-top: 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
  }
  th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
  }
  th {
    background: #1d4ed8;
    color: white;
  }
  a.delete-btn {
    color: red;
    font-weight: bold;
    text-decoration: none;
  }
  .message {
    color: green;
    font-weight: bold;
    margin: 15px 0;
  }
   footer {
        text-align: center;
        margin-top: 40px;
        padding: 15px 0;
        color: black;
        font-size: 0.9rem;
    }
</style>
</head>
<body>

<header>
  <h1>Hostel Management System</h1>
  <nav>
    <a href="admin_dashboard.php">Home</a>
    <a href="manage_rooms.php">Manage Rooms</a>
    <a href="view_requests.php">View Room Requests</a>
    <a href="manage_students.php" class="active">Manage Students</a>
    <a href="admin_payments.php">View Payment</a>
    <a href="logout.php" class="logout-btn">Logout</a>
  </nav>
</header>

<div class="container">
  <h2>Manage Students</h2>

  <?php if ($message): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>Username</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Allocated Room</th>
        <th>Allocate / Change Room</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($students)): ?>
        <tr><td colspan="6" style="text-align:center;">No students found.</td></tr>
      <?php else: ?>
        <?php foreach ($students as $student): ?>
          <tr>
            <td><?= htmlspecialchars($student['username']); ?></td>
            <td><?= htmlspecialchars($student['fullname']); ?></td>
            <td><?= htmlspecialchars($student['email']); ?></td>
            <td><?= $allocations[$student['id']] ?? '<em>None</em>'; ?></td>
            <td>
              <form method="POST" style="display:flex; gap:10px; align-items:center;">
                <input type="hidden" name="student_id" value="<?= $student['id']; ?>" />
                <select name="room_id" required>
                  <option value="" disabled selected>Select Room</option>
                  <?php foreach ($rooms as $room): ?>
                    <option value="<?= $room['id']; ?>"><?= htmlspecialchars($room['room_number']); ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" name="allocate_room">Allocate</button>
              </form>
            </td>
            <td>
              <a href="?delete=<?= $student['id']; ?>" class="delete-btn" onclick="return confirm('Delete this student?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
  <footer>
    &copy; <?php echo date("Y"); ?> Hostel Management System. All rights reserved.
  </footer>
</body>
</html>
