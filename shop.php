<html> 
    <head>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </head>
    <body>
        <section class="header">
            <div class="container-shop">
                <div class="navbar-shop">
                    <img src="img/logo.png" class="logo">
                    <ul>
                        <li><a href="index.php">HOME</a></li>
                        <li><a href="shop.php">SHOP</a></li>
                        <li><a href="#">ORDERS</a></li>
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
        <div class="cart">
            <li class="tooltip-container"><a href="#"><i class="fa-solid fa-cart-shopping"></i><span class="tooltip">Cart</span></a></li>
            <p>0</p>
        </div> 
</div>     
    <div class="container-products">
        <div id="root"></div>
        <script src="cart.js"></script>
    </div>   
<!-- Modal Structure -->
<div id="preview-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span id="close-modal-btn" class="modal-close">&times;</span>
        <div class="product-info">
            <img id="modal-image" src="" alt="Product Image">
            <h2 id="modal-title"></h2>
            <p id="modal-price"></p> <!-- New element to display the product's price -->
            <p id="modal-description"></p>
            <button id="add-to-cart-btn" class="add-to-cart-btn">Add to Cart</button>
        </div>
    </div>
    <script src="preview.js"></script>
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
    </body>   
    
</html>