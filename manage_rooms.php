<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "hostel_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Add or Update room
if (isset($_POST['save_room'])) {
    $room_number = $_POST['room_number'];
    $capacity = $_POST['capacity'];
    $status = $_POST['status'];
    $room_size = $_POST['room_size'];
    $room_info = $_POST['room_info'];
    $room_fee = $_POST['room_fee'];

    // Handle photo upload
    $room_photo = "";
    if (!empty($_FILES['room_photo']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $room_photo = $target_dir . basename($_FILES["room_photo"]["name"]);
        move_uploaded_file($_FILES["room_photo"]["tmp_name"], $room_photo);
    }

    // Check if room already exists
    $check = $conn->prepare("SELECT id FROM rooms WHERE room_number = ?");
    $check->bind_param("s", $room_number);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Update existing room
        $row = $result->fetch_assoc();
        $room_id = $row['id'];

        if ($room_photo != "") {
            $stmt = $conn->prepare("UPDATE rooms 
                SET capacity=?, status=?, room_size=?, room_photo=?, room_info=?, room_fee=? 
                WHERE id=?");
            $stmt->bind_param("issssii", $capacity, $status, $room_size, $room_photo, $room_info, $room_fee, $room_id);
        } else {
            $stmt = $conn->prepare("UPDATE rooms 
                SET capacity=?, status=?, room_size=?, room_info=?, room_fee=? 
                WHERE id=?");
            $stmt->bind_param("isssii", $capacity, $status, $room_size, $room_info, $room_fee, $room_id);
        }

        if ($stmt->execute()) {
            $message = "Room updated successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }

    } else {
        // Insert new room
        $stmt = $conn->prepare("INSERT INTO rooms 
            (room_number, capacity, status, room_size, room_photo, room_info, room_fee) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissssi", $room_number, $capacity, $status, $room_size, $room_photo, $room_info, $room_fee);

        if ($stmt->execute()) {
            $message = "Room added successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}

// Delete room
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM rooms WHERE id=$id");
    $message = "Room deleted successfully!";
}

// Fetch existing rooms
$result = $conn->query("SELECT * FROM rooms");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Rooms - Admin</title>
<style>
  * {
    box-sizing: border-box;
  }
  body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background: #f0f4f8;
    color: #333;
  }

  /* Header/Navbar (same as dashboard) */
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
    background: #dc2626; /* red */
    padding: 8px 14px;
    border-radius: 5px;
    font-weight: bold;
  }
  nav a.logout-btn:hover {
    background: #b91c1c;
    color: #fff;
  }
  h2{
    color: #1c3066ff;
    text-align: center;
    margin-bottom: 25px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.15);
  }
h3{
   color: #1b3069ff;
   text-align: left;
   margin-bottom: 25px;
   text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.15);
 }
  .container{
    padding: 20px 40px;
    max-width: 1200px;
    margin: auto;
  }
  form, table {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(47, 8, 221, 0.1);
    margin-bottom: 30px;
  }
  input, select, textarea {
    padding: 8px;
    margin: 8px 0;
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  textarea {
    resize: vertical;
    min-height: 80px;
  }
  button {
    background:#1c3066ff;
    color: white; 
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
  }
  button:hover {
    background: #233352ff;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  th, td {
    text-align: left;
    padding: 12px;
    border-bottom: 1px solid #ddd;
    vertical-align: top;
  }
  th {
    background: #1c3066ff;
    color: white;
  }
  a.delete-btn {
    color: red;
    text-decoration: none;
    font-weight: bold;
  }
  a.delete-btn:hover {
    text-decoration: underline;
  }
  .message {
    margin-bottom: 20px;
    color: green;
    font-weight: bold;
  }
  img.room-photo {
    max-width: 200px;
    border-radius: 6px;
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
    <a href="manage_rooms.php" aria-current="page">Manage Rooms</a>
    <a href="view_requests.php">Room Requests</a>
    <a href="manage_students.php">Manage Students</a>
    <a href="admin_payments.php">View Payment</a>
    <a href="logout.php" class="logout-btn">Logout</a>
</header>

<div class="container">
  <h2>Manage Rooms</h2>

  <?php if ($message): ?>
    <p class="message"><?= $message ?></p>
  <?php endif; ?>

  <form method="POST" action="" enctype="multipart/form-data">
    <h3>Add / Update Room</h3>
    <label>Room Number:</label>
    <input type="text" name="room_number" required placeholder="e.g., A101" />
    
    <label>Capacity:</label>
    <input type="number" name="capacity" min="1" required />
    
    <label>Status:</label>
    <select name="status" required>
      <option value="available">Available</option>
      <option value="unavailable">Unavailable</option>
    </select>

    <label>Room Size:</label>
    <input type="text" name="room_size" placeholder="e.g., 200 sq.ft or 12x15 ft" />

    <label>Room Fee (per month):</label>
    <input type="number" name="room_fee" required placeholder="e.g., 5000" />

    <label>Room Photo:</label>
    <input type="file" name="room_photo" accept="image/*" />

    <label>Room Info:</label>
    <textarea name="room_info" placeholder="Enter room description..."></textarea>

    <button type="submit" name="save_room">Save Room</button>
  </form>

  <h3>Existing Rooms</h3>
  <table>
    <thead>
      <tr>
        <th>Room Number</th>
        <th>Capacity</th>
        <th>Status</th>
        <th>Size</th>
        <th>Fee</th>
        <th>Photo</th>
        <th>Info</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['room_number'] ?></td>
          <td><?= $row['capacity'] ?></td>
          <td><?= ucfirst($row['status']) ?></td>
          <td><?= $row['room_size'] ?></td>
          <td>Rs. <?= number_format($row['room_fee']) ?></td>
          <td>
            <?php if ($row['room_photo']): ?>
              <img src="<?= $row['room_photo'] ?>" alt="Room Photo" class="room-photo" />
            <?php else: ?>
              No Photo
            <?php endif; ?>
          </td>
          <td><?= nl2br($row['room_info']) ?></td>
          <td>
            <a href="?delete=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this room?');">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
  <footer>
    &copy; <?php echo date("Y"); ?> Hostel Management System. All rights reserved.
  </footer>

</body>
</html>
