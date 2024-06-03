<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['cart_items']) && !empty($_SESSION['cart_items'])) {
        // Extract user_id from session or any other source
        $user_id = 1; // Assuming user_id is 1 for demonstration purposes
        $payment_method = $_POST['payment']; // Get payment method from form
        $phone = $_POST['phone']; // Get phone number from form if needed

        // Prepare and bind SQL statement
        $stmt = $conn->prepare("INSERT INTO `order` (user_id, item_name, quantity, payment_method, date_added, total_price) VALUES (?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("isiss", $user_id, $item_name, $quantity, $payment_method, $total_price);

        // Insert each item from the session into the database
        foreach ($_SESSION['cart_items'] as $item) {
            $item_name = $item['name'];
            $quantity = $item['quantity'];
            $total_price = $item['price'] * $quantity;
            $stmt->execute();
        }

        // Close statement
        $stmt->close();

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
