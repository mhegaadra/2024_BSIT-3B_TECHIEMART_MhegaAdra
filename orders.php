<?php 
include 'config.php';

session_start();

// Check if items are passed via GET (from shop.php) and store them in session
if (isset($_GET['items'])) {
    $_SESSION['cart_items'] = json_decode(urldecode($_GET['items']), true);
}

if(isset($_SESSION['cart_items'])) {
    $cartItems = $_SESSION['cart_items'];
} else {
    $cartItems = [];
}

// Check if the user is logged in
if(!isset($_SESSION['user_id'])) {
    // Redirect the user to the login page if they are not logged in
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .orders-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .orders-container h1, .orders-container h2, .orders-container h3 {
            text-align: center;
            color: #333;
        }

        .orders-container .cart-items ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .orders-container .cart-items ul li {
            display: flex;
            align-items: center;
            gap: 20px;
            background-color: #f9f9f9;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .orders-container .cart-items ul li img {
            width: 100px;
            height: auto;
            border-radius: 5px;
        }

        .orders-container .cart-items ul li p {
            margin: 0;
            color: #666;
        }

        .orders-container .payment-options {
            margin-top: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .orders-container .payment-options label,
        .orders-container .payment-options select,
        .orders-container .payment-options input,
        .orders-container .payment-options button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }

        .orders-container .payment-options button {
            background-color: #333;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .orders-container .payment-options button:hover {
            background-color: #555;
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
                    <li class="tooltip-container"><a href="login.php"><i class="fa-solid fa-user"></i><span class="tooltip">Sign In</span></a></li>
                </ul>
            </div>
        </div>
    </section>
    
    <div class="orders-container">
        <h1>Orders</h1>
        <div class="cart-items">
            <?php if(!empty($cartItems)): ?>
                <ul>
                    <?php foreach($cartItems as $item): ?>
                        <li>
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                            <p><?php echo $item['name']; ?> - Quantity: <?php echo $item['quantity']; ?> - Price: $<?php echo $item['price']; ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No items in your cart.</p>
            <?php endif; ?>
        </div>
        
        <div class="payment-options">
            <h2>Payment Options</h2>
            <form method="POST" action="order_process.php">
    <!-- Add a hidden input field to store the user_id -->
    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

    <label for="payment">Select Payment Method:</label>
    <select name="payment" id="payment" required>
        <option value="Credit Card">Credit Card</option>
        <option value="PayPal">PayPal</option>
        <option value="Cash on Delivery">Cash on Delivery</option>
    </select>
    
    <label for="phone">Phone Number:</label>
    <input type="tel" name="phone" id="phone" placeholder="Enter your phone number" required>

    <button type="submit">Submit Order</button>
</form>
        </div>
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
                <p>&copy; 2024 TECHIEMART. All rights reserved</p>
            </div>
        </section>
    </footer>
</body>
</html>
