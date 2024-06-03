<?php
session_start(); // Start session if not already started

// Database connection
$servername = "localhost";
$username = "root";
$password = "12345";
$dbname = "techiemart";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Login handling
$error_message = ""; // Initialize error message

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if the login attempt is for admin
    if ($username === "admin" && $password === "admin1") {
        $_SESSION['admin'] = true; // Set a session variable to indicate admin login
        header("Location: dashboard.php"); // Redirect to admin dashboard
        exit();
    }

    // Check regular user login
    $sql = "SELECT * FROM users_info WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_success'] = true; // Set session variable for successful login
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "No user found with this username.";
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Login</title>
</head>
<body>
<div class="container-login">
    <div class="form-box">
        <h1 id="title">Login</h1>
        <form id="loginForm" method="POST" action="login.php">
            <div class="input-group">
                <div class="input-field">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-field">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
            </div>
            <div class="btn-field">
                <button type="submit" name="login" class="btn">Login</button>
                <a href="register.php" class="btn-link">Register</a>
            </div>
        </form>
        <!-- Display the error message if login fails -->
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
