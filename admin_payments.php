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

   // Fetch pending payments count
   $stmt = $conn->prepare("SELECT COUNT(*) AS total_pending FROM payments WHERE payment_status = ?");
   if (!$stmt) {
       die("Prepare failed: " . $conn->error);
   }
   $status = 'pending';
   $stmt->bind_param("s", $status);
   $stmt->execute();
   $pending_result = $stmt->get_result();
   $pending_row = $pending_result->fetch_assoc();
   $pending_payments = $pending_row['total_pending'] ?? 0;
   $pendingCounterClass = ($pending_payments > 0) ? "orange" : "green";
   $pendingCardClass = ($pending_payments > 0) ? "alert-request-card" : "";
   $stmt->close();

   // Fetch completed payments count
   $stmt = $conn->prepare("SELECT COUNT(*) AS total_completed FROM payments WHERE payment_status = ?");
   if (!$stmt) {
       die("Prepare failed: " . $conn->error);
   }
   $status = 'completed';
   $stmt->bind_param("s", $status);
   $stmt->execute();
   $completed_result = $stmt->get_result();
   $completed_row = $completed_result->fetch_assoc();
   $completed_payments = $completed_row['total_completed'] ?? 0;
   $completedCounterClass = ($completed_payments > 0) ? "green" : "red";
   $stmt->close();

   // Fetch overdue payments count
   $stmt = $conn->prepare("SELECT COUNT(*) AS total_overdue FROM payments WHERE payment_status = ?");
   if (!$stmt) {
       die("Prepare failed: " . $conn->error);
   }
   $status = 'overdue';
   $stmt->bind_param("s", $status);
   $stmt->execute();
   $overdue_result = $stmt->get_result();
   $overdue_row = $overdue_result->fetch_assoc();
   $overdue_payments = $overdue_row['total_overdue'] ?? 0;
   $overdueCounterClass = ($overdue_payments > 0) ? "red" : "green";
   $overdueCardClass = ($overdue_payments > 0) ? "alert-card" : "";
   $stmt->close();

   // Close database connection
   $conn->close();
   ?>

   <!DOCTYPE html>
   <html lang="en">
   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>Payment Details - Hostel Management System</title>
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
       </style>
   </head>
   <body>
       <header>
           <h1>Hostel Management System</h1>
           <nav>
               <a href="admin_dashboard.php">Home</a>
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
               <!-- Pending Payments Card -->
               <a href="view_pending_payments.php" class="card <?php echo $pendingCardClass; ?>" role="button" aria-label="View Pending Payments">
                   <h3>Pending Payments</h3>
                   <div class="counter <?php echo $pendingCounterClass; ?>">
                       <?php echo $pending_payments; ?>
                   </div>
                   <p>payments are awaiting confirmation.</p>
               </a>

               <!-- Completed Payments Card -->
               <a href="view_completed_payments.php" class="card" role="button" aria-label="View Completed Payments">
                   <h3>Completed Payments</h3>
                   <div class="counter <?php echo $completedCounterClass; ?>">
                       <?php echo $completed_payments; ?>
                   </div>
                   <p>payments have been successfully processed.</p>
               </a>

               <!-- Overdue Payments Card -->
               <a href="view_overdue_payments.php" class="card <?php echo $overdueCardClass; ?>" role="button" aria-label="View Overdue Payments">
                   <h3>Overdue Payments</h3>
                   <div class="counter <?php echo $overdueCounterClass; ?>">
                       <?php echo $overdue_payments; ?>
                   </div>
                   <p>payments are past due and require action.</p>
               </a>
           </div>
       </div>

       <footer>
           &copy; <?php echo date("Y"); ?> Hostel Management System. All rights reserved.
       </footer>
   </body>
   </html>