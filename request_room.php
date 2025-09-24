<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "hostel_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = "";

// Get current user's id
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];
$stmt->close();

// Check if user has already submitted a room request
$stmt = $conn->prepare("SELECT status FROM room_requests WHERE student_id = ? ORDER BY request_date DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$latest_request = $result->fetch_assoc();
$stmt->close();

// Check if user has a room allocation
$stmt = $conn->prepare("SELECT r.room_number FROM room_allocations ra JOIN rooms r ON ra.room_id = r.id WHERE ra.student_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$allocation = $result->fetch_assoc();
$stmt->close();

// Handle Room Request Submission
if (isset($_POST['request_room'])) {
    if ($latest_request && in_array($latest_request['status'], ['pending', 'approved'])) {
        $message = "You already have a pending or approved room request.";
    } elseif ($allocation) {
        $message = "You already have a room allocated. Please visit My Allocation page.";
    } else {
        $fullname = trim($_POST['fullname']);
        $contact = trim($_POST['contact']);
        $permanent_address = trim($_POST['permanent_address']);
        $guardian_name = trim($_POST['guardian_name']);
        $guardian_contact = trim($_POST['guardian_contact']);
        $age = intval($_POST['age']);
        $gender = $_POST['gender'];
        $room_id = intval($_POST['room_id']);

        $citizenship_file_name = null;
        $idcard_file_name = null;

        if (isset($_FILES['citizenship_file']) && $_FILES['citizenship_file']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['citizenship_file']['tmp_name'];
            $ext = pathinfo($_FILES['citizenship_file']['name'], PATHINFO_EXTENSION);
            $citizenship_file_name = 'citizenship_' . $user_id . '_' . time() . '.' . $ext;
            move_uploaded_file($tmp_name, __DIR__ . '/uploads/' . $citizenship_file_name);
        }

        if (isset($_FILES['idcard_file']) && $_FILES['idcard_file']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['idcard_file']['tmp_name'];
            $ext = pathinfo($_FILES['idcard_file']['name'], PATHINFO_EXTENSION);
            $idcard_file_name = 'idcard_' . $user_id . '_' . time() . '.' . $ext;
            move_uploaded_file($tmp_name, __DIR__ . '/uploads/' . $idcard_file_name);
        }

        $stmt = $conn->prepare("INSERT INTO room_requests 
            (student_id, room_id, fullname, contact, permanent_address, guardian_name, guardian_contact, age, gender, citizenship_file, idcard_file, request_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'pending')");
        $stmt->bind_param("iisssssisss", 
            $user_id, $room_id, $fullname, $contact, $permanent_address, $guardian_name, $guardian_contact, $age, $gender, $citizenship_file_name, $idcard_file_name
        );
        $message = $stmt->execute() ? "Room request submitted successfully." : "Error submitting request: " . $stmt->error;
        $stmt->close();
    }
}

// Fetch available rooms
$rooms = [];
$result = $conn->query("SELECT id, room_number, capacity FROM rooms WHERE status = 'available'");
if ($result) while ($row = $result->fetch_assoc()) $rooms[] = $row;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Request Room</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap');
* { 
    box-sizing: border-box;
 }
body {
      font-family: 'Inter', sans-serif; 
      margin:0;
      background:#fff8f0; 
      color:#333; 
  }

header { 
    background:#ea580c; 
    color:white; 
    padding:20px 40px; 
    display:flex; 
    justify-content:space-between; 
    align-items:center; 
    box-shadow:0 3px 6px rgba(0,0,0,0.1); 
  }
header h1 { 
  margin:0; 
  font-weight:700; 
  font-size:1.8rem; 
}
nav a { 
  color:white; 
  text-decoration:none; 
  margin-left:25px; 
  font-weight:600; 
  transition:color 0.3s ease; 
}
nav a:hover, nav a[aria-current="page"] { 
  color:#fbbf24; 
}

.container { 
  max-width:600px; 
  margin:40px auto; 
  background:white; 
  padding:30px 35px; 
  border-radius:12px; 
  box-shadow:0 8px 24px rgb(234 88 12 / 0.25); 
}
h1.page-title { 
  text-align:center; 
  color:#ea580c; 
  margin-bottom:30px; 
  font-weight:700; 
}
.message { 
  text-align:center; 
  margin-bottom:20px; 
  font-weight:600; 
  padding:14px 15px; 
  border-radius:6px; 
  color:#14532d; 
  background-color:#dcfce7; 
  border:1px solid #22c55e; 
}
form label { 
  display:block; 
  font-weight:600; 
  margin-bottom:8px; 
  color:#7c2d12; 
}
input, select, textarea { 
  width:100%; 
  padding:12px 15px; 
  font-size:16px; 
  border-radius:8px; 
  border:1.8px solid #f97316; 
  background-color:#fff7ed; 
  margin-bottom:15px; 
}
input:focus, select:focus, textarea:focus { 
  border-color:#ea580c; 
  outline:none; 
  background-color:#fff4e6; 
}
button { 
  width:100%; 
  padding:16px 0; 
  background:#ea580c; 
  border:none; 
  border-radius:10px; 
  color:white; 
  font-size:18px; 
  font-weight:700; 
  cursor:pointer; 
  box-shadow:0 6px 14px rgb(234 88 12 / 0.5); 
  transition:background-color 0.3s ease; 
}
button:hover { 
  background:#c2410c; 
}
p.no-rooms { 
  text-align:center; 
  color:#ea580c; 
  font-size:18px; 
  margin-top:30px; 
  font-weight:600; }
@media (max-width:640px){ 
  .container{
     margin:20px 15px; 
     padding:25px 20px;
     } 
  nav{ 
    flex-wrap:wrap; 
    gap:15px; 
    justify-content:center; 
    } 
}
</style>
</head>
<body>

<header>
  <h1>Hostel Management System</h1>
  <nav>
    <a href="user_dashboard.php">Home</a>
    <a href="view_rooms.php">View Room</a>
    <a href="request_room.php" aria-current="page">Request Room</a>
    <a href="my_allocation.php">My Allocation</a>
    <a href="payment.php">Payment</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<div class="container">
  <h1 class="page-title">Request a Room</h1>

  <?php if ($message): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <?php if ($allocation): ?>
    <p class="message">You already have a room allocated. Visit <a href="my_allocation.php">My Allocation</a> to view details.</p>
  <?php elseif ($latest_request && in_array($latest_request['status'], ['pending', 'approved'])): ?>
    <p class="message">You already submitted a room request. Please wait for approval.</p>
  <?php elseif (empty($rooms)): ?>
    <p class="no-rooms">No rooms available for request at the moment. Please check back later.</p>
  <?php else: ?>
    <form method="POST" action="" enctype="multipart/form-data">
      <label for="fullname">Full Name</label>
      <input type="text" id="fullname" name="fullname" required />

      <label for="contact">Contact Number</label>
      <input type="tel" id="contact" name="contact" required />

      <label for="permanent_address">Permanent Address</label>
      <textarea id="permanent_address" name="permanent_address" rows="3" required></textarea>

      <label for="guardian_name">Guardian's Name</label>
      <input type="text" id="guardian_name" name="guardian_name" required />

      <label for="guardian_contact">Guardian's Contact Number</label>
      <input type="tel" id="guardian_contact" name="guardian_contact" required />

      <label for="age">Age</label>
      <input type="number" id="age" name="age" min="10" max="100" required />

      <label for="gender">Gender</label>
      <select id="gender" name="gender" required>
        <option value="" disabled selected>-- Select Gender --</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
      </select>

      <label for="citizenship_file">Upload Citizenship Card</label>
      <input type="file" id="citizenship_file" name="citizenship_file" accept=".jpg,.jpeg,.png,.pdf" required />

      <label for="idcard_file">Upload ID Card</label>
      <input type="file" id="idcard_file" name="idcard_file" accept=".jpg,.jpeg,.png,.pdf" required />

      <label for="room_id">Select Room</label>
      <select name="room_id" id="room_id" required>
        <option value="" disabled selected>-- Choose a Room --</option>
        <?php foreach ($rooms as $room): ?>
          <option value="<?php echo $room['id']; ?>">
            <?php echo htmlspecialchars($room['room_number']) . " (Capacity: " . $room['capacity'] . ")"; ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button type="submit" name="request_room">Submit Request</button>
    </form>
  <?php endif; ?>
</div>

</body>
</html>
