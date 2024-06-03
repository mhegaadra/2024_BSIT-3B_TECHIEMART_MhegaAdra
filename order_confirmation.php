<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .confirmation-message {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 60vh;
            text-align: center;
        }

        .confirmation-message h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 20px;
        }

        .confirmation-message p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 20px;
        }

        .confirmation-message .btn {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1rem;
        }

        .confirmation-message .btn:hover {
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
    
    <div class="confirmation-message">
        <h1>Your order has been processed!</h1>
        <p>Thank you for shopping with us.</p>
        <a href="track_orders.php" class="btn">Track Orders</a>
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
