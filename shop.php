<?php 
@include 'config.php';

?>
<html> 
    <head>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }
    </script>
    </head>
    <body>
        <section class="header">
            <div class="container-shop">
                <div class="navbar-shop">
                    <img src="img/logo.png" class="logo">
                    <ul>
                        <li><a href="index.php">HOME</a></li>
                        <li><a href="shop.php">SHOP</a></li>
                        <li><a href="orders.php">ORDERS</a></li>
                        <li class="tooltip-container"><a href="login.php"><i class="fa-solid fa-user"></i><span class="tooltip">Sign In</span></a></li>
                    </ul>
                </div>
            </div>

        </section>
<!---------categories------>
<div class="header-b">
    <div class="dropdown">
            <select name="category" id="categoryFilter">
                <option value="">Categories</option>
                <option value="Apple">Apple</option>
                <option value="Samsung">Samsung</option>
                <option value="Huawei">Huawei</option>
                <option value="Oppo">Oppo</option>
                <option value="Vivo">Vivo</option>
            </select>
        <script src="filter.js"></script> 
    </div>
        <div class="search">
            <input type="search" id="search-item" placeholder="Search...">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <script src="search.js"></script>
        <div class="cart-icon" onclick="toggleSidebar(event)">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>3</span>
        </div>
</div> 

<!---------products-------->
<section class="products">
        <div class="container-products">
            <div id="root">
                <?php
                $sql = "SELECT item_id, item_name, item_price, item_image, item_description FROM item";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='box' data-id='".$row['item_id']."' data-name='".$row['item_name']."' data-price='".$row['item_price']."' data-image='img/".$row['item_image']."' data-description='".$row['item_description']."'>";
                        echo "<div class='img-box'><img src='img/".$row['item_image']."'></div>";
                        echo "<div class='left'>";
                        echo "<p>".$row['item_name']."</p>";
                        echo "<h2>$".$row['item_price']."</h2>";
                        echo "<button class='button-style' onclick='showModal(this)'>SEE PREVIEW<span class='span-effect'></span></button>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "0 results";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Modal Structure -->
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-body">
                <div class="modal-image">
                    <img id="modalImage" src="" alt="Item Image">
                </div>
                <div class="modal-details">
                    <h2 id="modalName"></h2>
                    <p id="modalPrice"></p>
                    <p id="modalDescription"></p>
                    <button id="addToCartBtn">Add to Cart</button>
                </div>
            </div>
        </div>
    </div>

    <script src="modal.js"></script>
<!---------sidebar-------->
<div class="sidevar" id="sidebar">
        <div class="sidebar-close" onclick="toggleSidebar()">
            <i class="fa-solid fa-close"></i>
            </div>
    <div class="cart-menu">
        <h3>My Cart</h3>
        <div class="cart-items"></div>
    </div>
    <div class="sidebar---footer">
        <div class="total---amount">
            <h5>Total</h5>
            <div class="cart-total">$0.00</div>
        </div>
        <button id="checkoutBtn" class="checkout-btn">Checkout</button>
    </div>
</div>


<!----------footer--------->
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
                    <li><a href="index.php#about-section">About</a></li>
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
<script src="sidebar.js"></script>
    </body>   
    
</html>
