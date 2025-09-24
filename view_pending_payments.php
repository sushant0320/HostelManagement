<?php
   session_start();

   if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
       header("Location: login.php");
       exit();
   }

   $username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
   $role = htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8');

   $conn = new mysqli("localhost", "root", "", "hostel_db");
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }

   $stmt = $conn->prepare("SELECT id, user_id, amount, due_date, payment_date FROM payments WHERE payment_status = ?");
   $status = 'pending';
   $stmt->bind_param("s", $status);
   $stmt->execute();
   $result = $stmt->get_result();
   ?>

   <!DOCTYPE html>
   <html lang="en">
   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>Pending Payments - Hostel Management System</title>
       <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
       <style>
           body { font-family: 'Inter', sans-serif; margin: 20px; }
           table { border-collapse: collapse; width: 100%; }
           th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
           th { background-color: #1c3066; color: white; }
       </style>
   </head>
   <body>
       <h2>Pending Payments</h2>
       <table>
           <tr>
               <th>ID</th>
               <th>User ID</th>
               <th>Amount</th>
               <th>Due Date</th>
               <th>Payment Date</th>
           </tr>
           <?php while ($row = $result->fetch_assoc()): ?>
               <tr>
                   <td><?php echo $row['id']; ?></td>
                   <td><?php echo $row['user_id']; ?></td>
                   <td><?php echo $row['amount']; ?></td>
                   <td><?php echo $row['due_date'] ?? 'N/A'; ?></td>
                   <td><?php echo $row['payment_date'] ?? 'N/A'; ?></td>
               </tr>
           <?php endwhile; ?>
       </table>
       <a href="admin_payments.php">Back to Payment Details</a>
   </body>
   </html>
   <?php
   $stmt->close();
   $conn->close();
   ?>