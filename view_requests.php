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

// Fetch all room requests with user info and room number, and check allocations
$requests = [];
$sql = "SELECT rr.id, rr.request_date, rr.status, rr.fullname, rr.contact, rr.permanent_address,
               rr.guardian_name, rr.guardian_contact, rr.age, rr.gender, rr.citizenship_file, rr.idcard_file,
               r.room_number, u.username, ra.id AS allocation_id
        FROM room_requests rr
        JOIN rooms r ON rr.room_id = r.id
        JOIN users u ON rr.student_id = u.id
        LEFT JOIN room_allocations ra ON ra.student_id = u.id
        ORDER BY rr.request_date DESC";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Auto-approve logic
        if ($row['status'] === 'pending' && $row['allocation_id']) {
            $updateStmt = $conn->prepare("UPDATE room_requests SET status = 'approved' WHERE id = ?");
            $updateStmt->bind_param("i", $row['id']);
            $updateStmt->execute();
            $updateStmt->close();
            $row['status'] = 'approved'; // Update locally too
        }

        $requests[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>All Room Requests - Admin</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap');

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

  /* Page content */
  .container {
    padding: 20px 40px;
    max-width: 1200px;
    margin: 30px auto;
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 8px 20px rgba(29, 78, 216, 0.3);
    padding: 20px 40px;
    max-width: 1200px;
  }
  h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #1d4ed8;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.15);
  }
  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
  }
  th, td {
    border: 1px solid #93c5fd;
    padding: 10px 8px;
    text-align: left;
    vertical-align: middle;
  }
  th {
    background: #1d4ed8;
    color: white;
  }
  tr:nth-child(even) {
    background: #eff6ff;
  }
  tr:hover {
    background: #dbeafe;
  }
  .status {
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 6px;
    display: inline-block;
    color: white;
  }
  .pending {
    background-color: #f59e0b;
  }
  .approved {
    background-color: #10b981;
  }
  .rejected {
    background-color: #ef4444;
  }
  a.file-link {
    color: #1d4ed8;
    text-decoration: underline;
  }
  a.file-link:hover {
    color: #2563eb;
  }

  /* Responsive table */
  @media (max-width: 1100px) {
    table, thead, tbody, th, td, tr {
      display: block;
    }
    tr {
      margin-bottom: 25px;
    }
    th {
      background: transparent;
      color: #1d4ed8;
      font-weight: 700;
      padding: 8px 0;
    }
    td {
      border: none;
      padding: 8px 0;
      position: relative;
      padding-left: 50%;
      text-align: right;
    }
    td::before {
      content: attr(data-label);
      position: absolute;
      left: 10px;
      width: 45%;
      padding-left: 10px;
      font-weight: 600;
      text-align: left;
      color: #1d4ed8;
    }
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
    <a href="view_requests.php" aria-current="page">View Room Requests</a>
    <a href="manage_students.php">Manage Students</a>
    <a href="admin_payments.php">View Payment</a>
    <a href="logout.php" class="logout-btn">Logout</a>
  </nav>
</header>

<div class="container">
  <h2>All Room Requests</h2>

  <?php if (empty($requests)): ?>
    <p style="text-align:center; font-size:18px; color:#1d4ed8;">No room requests found.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Request Date</th>
          <th>Username</th>
          <th>Room Number</th>
          <th>Full Name</th>
          <th>Contact</th>
          <th>Permanent Address</th>
          <th>Guardian Name</th>
          <th>Guardian Contact</th>
          <th>Age</th>
          <th>Gender</th>
          <th>Citizenship Card</th>
          <th>ID Card</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
          <tr>
            <td data-label="Request Date"><?php echo htmlspecialchars($r['request_date']); ?></td>
            <td data-label="Username"><?php echo htmlspecialchars($r['username']); ?></td>
            <td data-label="Room Number"><?php echo htmlspecialchars($r['room_number']); ?></td>
            <td data-label="Full Name"><?php echo htmlspecialchars($r['fullname']); ?></td>
            <td data-label="Contact"><?php echo htmlspecialchars($r['contact']); ?></td>
            <td data-label="Permanent Address"><?php echo htmlspecialchars($r['permanent_address']); ?></td>
            <td data-label="Guardian Name"><?php echo htmlspecialchars($r['guardian_name']); ?></td>
            <td data-label="Guardian Contact"><?php echo htmlspecialchars($r['guardian_contact']); ?></td>
            <td data-label="Age"><?php echo htmlspecialchars($r['age']); ?></td>
            <td data-label="Gender"><?php echo htmlspecialchars($r['gender']); ?></td>
            <td data-label="Citizenship Card">
              <?php if ($r['citizenship_file']): ?>
                <a class="file-link" href="uploads/<?php echo urlencode($r['citizenship_file']); ?>" target="_blank">View</a>
              <?php else: ?>N/A<?php endif; ?>
            </td>
            <td data-label="ID Card">
              <?php if ($r['idcard_file']): ?>
                <a class="file-link" href="uploads/<?php echo urlencode($r['idcard_file']); ?>" target="_blank">View</a>
              <?php else: ?>N/A<?php endif; ?>
            </td>
            <td data-label="Status">
              <span class="status <?php echo strtolower($r['status']); ?>">
                <?php echo ucfirst($r['status']); ?>
              </span>
            </td>
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
