<?php
// Handle form submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "hostel_db");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $secret_code = $_POST['secret_code'];

    // Determine user role based on secret code
    $role = ($secret_code === "admin123") ? "admin" : "user"; 

    // Basic validation
    if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database
        $sql = "INSERT INTO users (fullname, email, username, password, role)
                VALUES ('$fullname', '$email', '$username', '$hashed_password', '$role')";

        if ($conn->query($sql) === TRUE) {
            $message = "Registration successful as $role! <a href='login.php'>Go to login</a>";
        } else {
            $message = "Error: " . $conn->error;
        }
    }

    $conn->close();
}
?>

<!-- Registration Form HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Account</title>
  <style>
    body {
      font-family: 'Times New Roman', Times, serif;
      background: #ececec;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .form-container {
      background: white;
      padding: 25px;
      border-radius: 10px;
      width: 300px;
      box-shadow: 0 0 10px rgba(69, 7, 240, 0.2);
    }
    .form-container h2 {
      text-align: center;
    }
    .form-container input {
      width: 100%;
      margin: 8px 0;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .form-container button {
      width: 100%;
      padding: 10px;
      background: #1d06e4;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .form-container button:hover {
      background: #5c329e;
    }
    .message {
      color: red;
      margin-bottom: 10px;
      text-align: center;
    }
  </style>
</head>
<body>
  <form class="form-container" action="" method="POST">
    <h2>Create Account</h2>
    <?php if (!empty($message)): ?>
      <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    <input type="text" name="fullname" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    

    <input type="text" name="secret_code" placeholder="Enter Admin Code (Optional)">

    <button type="submit">Sign Up</button>
    <p style="text-align: center; margin-top: 10px; font-size: 14px;">
      Already have an account? 
      <a href="login.php" style="color: #8278e0ff; text-decoration: none; font-weight: bold;">
        Login
      </a>
    </p>
  </form>
</body>
</html>