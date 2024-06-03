<?php
include 'config.php';
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // User is logged in, get their user_id
    $user_id = $_SESSION['user_id'];
} else {
    // Redirect the user to the login page
    header("Location: login.php");
    exit(); // Stop further execution
}

// Establish a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch orders from the database including item information for the logged-in user
$sql = "SELECT o.item_name, o.total_price, o.quantity, o.item_image
        FROM `order` o
        WHERE o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch user information from users_info table for the logged-in user
$user_info_sql = "SELECT * FROM `users_info` WHERE user_id = ?";
$user_info_stmt = $conn->prepare($user_info_sql);
$user_info_stmt->bind_param("i", $user_id);
$user_info_stmt->execute();
$user_info_result = $user_info_stmt->get_result();
$user_info = $user_info_result->fetch_assoc();
$user_info_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Orders</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Existing styles */
        .orders-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .orders-container h1 {
            text-align: center;
            color: #333;
        }

        .order-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .order-item img {
            width: 100px;
            height: auto;
            float: left;
            margin-right: 20px;
        }

        .order-item-content {
            padding: 20px;
        }

        .order-item-content h2 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .order-item-content p {
            margin: 0;
            color: #666;
        }
    </style>
</head>
<body>
    <section class="header">
        <div class="container-shop">
            <div class="navbar-shop">
                <img src="img/logo.png" class="logo">
                <ul>
                    <li><a href="index.php">HOME</a></li>
                    <li><a href="shop.php">SHOP</a></li>
                    <li><a href="track_orders.php">TRACK ORDERS</a></li>
                    <!-- Show different link or icon for logged in user -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="tooltip-container"><a href="logout.php"><i class="fa-solid fa-sign-out"></i><span class="tooltip">Logout</span></a></li>
                    <?php else: ?>
                    <li class="tooltip-container"><a href="login.php"><i class="fa-solid fa-user"></i><span class="tooltip">Sign In</span></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </section>

    <!-- Display orders -->
    <!-- Display orders -->
    <div class="orders-container">
        <h1>Track Orders</h1>
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-item">
                <img src="img/<?php echo $order['item_image']; ?>" alt="<?php echo $order['item_name']; ?>">
                    <div class="order-item-content">
                        <h2><?php echo $order['item_name']; ?></h2>
                        <p>Price: <?php echo $order['total_price']; ?></p>
                        <p>Quantity: <?php echo $order['quantity']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>


    <!-- Footer section -->
    <footer>
        <section class="footer">
            <div class="container">
                <div class="footer-content">
                    <h3>Contact Us</h3>
                    <p>Email: techiemart@gmail.com</p>
                    <p>Phone: +0912-345-6789</p>
                    <p>Website: techiemart.com</p>
                </div>
                <div class="footer-content">
                    <h3>Quick Links</h3>
                    <ul class="list">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#about-section" class="scroll-to">About</a></li>
                        <li><a href="shop.php">Products</a></li>
                        <li><a href="">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-content">
                    <h3>Follow Us</h3>
                    <ul class="social-icons">
                        <li><a href="https://www.facebook.com"><i class="fab fa-facebook"></i></a></li>
                        <li><a href="https://www.twitter.com"><i class="fab fa-twitter"></i></a></li>
                        <li><a href="https://www.instagram.com"><i class="fab fa-instagram"></i></a></li>
                        <li><a href="https://www.tiktok.com"><i class="fab fa-tiktok"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="bottom-bar">
                <p>© 2024 TECHIEMART. All rights reserved</p>
            </div>
        </section>
    </footer>
</body>
</html>

