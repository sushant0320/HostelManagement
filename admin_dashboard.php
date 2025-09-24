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

// Fetch available rooms count
$stmt = $conn->prepare("SELECT COUNT(*) AS total_available FROM rooms WHERE status = ?");
$status = 'available';
$stmt->bind_param("s", $status);
$stmt->execute();
$rooms_result = $stmt->get_result();
$rooms_row = $rooms_result->fetch_assoc();
$available_rooms = $rooms_row['total_available'] ?? 0;
$roomsCounterClass = ($available_rooms > 0) ? "green" : "red";
$roomsCardClass = ($available_rooms > 0) ? "" : "alert-card";
$stmt->close();

// Fetch pending room requests
$stmt = $conn->prepare("SELECT COUNT(*) AS total_pending FROM room_requests WHERE status = ?");
$status = 'pending';
$stmt->bind_param("s", $status);
$stmt->execute();
$requests_result = $stmt->get_result();
$requests_row = $requests_result->fetch_assoc();
$pending_requests = $requests_row['total_pending'] ?? 0;
$requestsCounterClass = ($pending_requests > 0) ? "orange" : "green";
$requestsCardClass = ($pending_requests > 0) ? "alert-request-card" : "";
$stmt->close();

// Fetch number of students whose rooms are allocated
$stmt = $conn->prepare("SELECT COUNT(DISTINCT student_id) AS total_allocated FROM room_allocations WHERE room_id IS NOT NULL");
$stmt->execute();
$allocated_result = $stmt->get_result();
$allocated_row = $allocated_result->fetch_assoc();
$allocated_students = $allocated_row['total_allocated'] ?? 0;
$allocatedCounterClass = ($allocated_students > 0) ? "green" : "red";
$stmt->close();

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Management System</title>
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
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
        }
        .card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 25px 20px;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            color: inherit;
        }
        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(29,78,216,0.25);
        }
        .card h3 {
            margin: 0 0 10px;
            color: #1d4ed8;
            font-size: 1.4rem;
        }
        .card p {
            font-size: 1rem;
            color: #555;
        }
        .counter {
            font-size: 3rem;
            font-weight: 800;
            margin: 15px 0;
        }
        .counter.green {
            color: #16a34a;
        }
        .counter.red {
            color: #dc2626;
        }
        .counter.orange {
            color: #ea580c;
        }
        .alert-card {
            background: #fee2e2;
            border: 1px solid #fecaca;
        }
        .alert-card h3 {
            color: #b91c1c;
        }
        .alert-request-card {
            background: #ffedd5;
            border: 1px solid #fdba74;
        }
        .alert-request-card h3 {
            color: #c2410c;
        }
        footer {
            text-align: center;
            margin-top: 50px;
            padding: 15px 0;
            color: #374151;
            font-size: 0.9rem;
        }
        img {
            max-width: 70px;
            margin-bottom: 12px;
        }
    </style>
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

        <div class="dashboard-grid">
            <!-- Manage Rooms Card -->
            <a href="manage_rooms.php" class="card <?php echo $roomsCardClass; ?>" role="button" aria-label="Manage Rooms">
                <h3>Manage Rooms</h3>
                <div class="counter <?php echo $roomsCounterClass; ?>">
                    <?php echo $available_rooms; ?>
                </div>
                <p>rooms are currently available.</p>
            </a>

            <!-- View Requests Card -->
            <a href="view_requests.php" class="card <?php echo $requestsCardClass; ?>" role="button" aria-label="View Room Requests">
                <h3>View Room Requests</h3>
                <div class="counter <?php echo $requestsCounterClass; ?>">
                    <?php echo $pending_requests; ?>
                </div>
                <p>students have requested a room and are waiting for allocation.</p>
            </a>

            <!-- Manage Students Card -->
            <a href="manage_students.php" class="card" role="button" aria-label="Manage Students">
                <h3>Manage Students</h3>
                <div class="counter <?php echo $allocatedCounterClass; ?>">
                    <?php echo $allocated_students; ?>
                </div>
                <p>students have been allocated rooms.</p>
            </a>

            <!-- Payments Card -->
            <a href="admin_payments.php" class="card" role="button" aria-label="View Payment Details">
                <img src="images/pay.png" alt="Payment Icon">
                <h3>View Payment Details</h3>
                <p>Check and verify student payment information.</p>
            </a>
        </div>
    </div>

    <footer>
        &copy; <?php echo date("Y"); ?> Hostel Management System. All rights reserved.
    </footer>
</body>
</html>