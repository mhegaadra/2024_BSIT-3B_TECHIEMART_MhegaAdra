<?php
include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect the user to the login page if they are not logged in
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['cart_items']) && !empty($_SESSION['cart_items'])) {
        // Get the logged-in user_id from the session
        $user_id = $_SESSION['user_id'];
        $payment_method = $_POST['payment'];
        $phone = $_POST['phone'];

        // Create a connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare the SQL statement for inserting the order
        $stmt = $conn->prepare("INSERT INTO `order` (user_id, item_name, quantity, payment_method, date_added, total_price, item_image) VALUES (?, ?, ?, ?, NOW(), ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("isisss", $user_id, $item_name, $quantity, $payment_method, $total_price, $item_image);

        // Insert each item from the session into the database
        foreach ($_SESSION['cart_items'] as $item) {
            $item_name = $item['name'];
            $quantity = $item['quantity'];
            $total_price = $item['price'] * $quantity;

            // Fetch the item_image from the item database
            $item_image_query = $conn->prepare("SELECT item_image FROM item WHERE item_name = ?");
            $item_image_query->bind_param("s", $item_name);
            $item_image_query->execute();
            $item_image_result = $item_image_query->get_result();
            $item_image_row = $item_image_result->fetch_assoc();
            $item_image = $item_image_row['item_image'];
            
            if (!$stmt->execute()) {
                echo "Execute failed: " . $stmt->error;
            }

            $item_image_query->close();
        }

        // Close statements and connection
        $stmt->close();
        $conn->close();

        // Clear the cart after placing the order
        unset($_SESSION['cart_items']);

        // Redirect to a confirmation page
        header("Location: order_confirmation.php");
        exit();
    } else {
        // If cart is empty, redirect back to shop page or show an error message
        header("Location: shop.php");
        exit();
    }
} else {
    // If someone tries to access this page directly without submitting the form, redirect to shop page
    header("Location: shop.php");
    exit();
}
?>
