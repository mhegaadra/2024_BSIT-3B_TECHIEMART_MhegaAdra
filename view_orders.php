<?php 
include 'config.php';

// Check if an action is submitted
if(isset($_POST['action']) && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $action = $_POST['action'];

    // Update the status of the order in the database based on the selected action
    if($action === 'confirm') {
        $updateStatusQuery = "UPDATE `order` SET status = 'Confirmed' WHERE order_id = $orderId";
    } elseif($action === 'cancel') {
        $updateStatusQuery = "UPDATE `order` SET status = 'Canceled' WHERE order_id = $orderId";
    }

    // Execute the update query
    if(mysqli_query($conn, $updateStatusQuery)) {
        // If the update is successful, display a success message
        echo "<script>alert('Order #$orderId $action successfully.');</script>";
    } else {
        // If the update fails, display an error message
        echo "<script>alert('Failed to $action order #$orderId.');</script>";
    }
}

$select = mysqli_query($conn, "SELECT order_id, user_id, item_name, quantity, date_added, total_price, status FROM `order` ORDER BY user_id");
$prev_user_id = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600&display=swap');

        :root {
            --green: #27ae60;
            --black: #333;
            --white: #fff;
            --bg-color: #eee;
            --box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
            --border: .1rem solid var(--black);
        }

        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none;
            border: none;
            text-decoration: none;
            text-transform: none;
        }

        html {
            font-size: 62.5%;
            overflow-x: hidden;
        }

        body {
            display: flex;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: var(--black);
            color: var(--white);
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 0;
        }

        .sidebar nav ul {
            list-style: none;
            width: 100%;
            padding: 0;
        }

        .sidebar nav ul li {
            width: 100%;
            padding: 1rem 0;
            text-align: center;
        }

        .sidebar nav ul li a {
            color: var(--white);
            font-size: 1.6rem;
            display: block;
            width: 100%;
            text-decoration: none;
            transition: background 0.3s;
        }

        .sidebar .btn {
            background: var(--black);
            color: var(--white);
            border: none;
            width: 100%;
            padding: 1rem;
            font-size: 1.6rem;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s;
        }

        .sidebar .btn:hover {
            background: var(--green);
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
            width: calc(100% - 250px);
        }

        .btn {
            display: block;
            width: auto;
            cursor: pointer;
            border-radius: .5rem;
            margin-top: 0.5rem;
            font-size: 1.6rem;
            padding: 0.5rem 1rem;
            background: var(--green);
            color: var(--white);
            text-align: center;
        }

        .btn:hover {
            background: var(--black);
        }

        .container {
            max-width: 1000px;
            padding: 10rem;
            margin: 0 auto;
        }

        .item-display {
            margin: 1rem 0;
        }

        .item-display .item-display-table {
            width: 100%;
            text-align: center;
            border-collapse: collapse;
        }

        .item-display .item-display-table th,
        .item-display .item-display-table td {
            padding: 1rem;
            font-size: 1.6rem;
            border-bottom: var(--border);
        }

        .item-display .item-display-table th {
            background: var(--bg-color);
        }

        .item-display .item-display-table .btn {
            background: var(--green);
            color: var(--white);
            border: none;
            border-radius: .5rem;
            padding: .5rem 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .item-display .item-display-table .btn:hover {
            background: var(--black);
        }

        @media (max-width: 991px) {
            html {
                font-size: 55%;
            }
        }

        @media (max-width: 768px) {
            .item-display {
                overflow-y: scroll;
            }
        }

        @media (max-width: 450px) {            
            html {
                font-size: 50%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <aside class="sidebar">
        <nav>
            <ul>
                <li><button class="btn" onclick="location.href='dashboard.php'">Dashboard</button></li>
                <li><button class="btn" onclick="location.href='view_items.php'">View Items</button></li>
                <li><button class="btn" onclick="location.href='view_orders.php'">View Orders</button></li>
                <li><button class="btn" onclick="location.href='add_items.php'">Manage Items</button></li>
                <li><button class="btn" onclick="location.href='reports.php'">Reports</button></li>
                <br><br><br>
                <li><button class="btn" onclick="location.href='logout.php'">Logout</button></li>
            </ul>
        </nav>
    </aside>

    <div class="main-content">
        <div class="item-display">
            <?php while ($row = mysqli_fetch_assoc($select)): ?>
                <?php if ($prev_user_id !== $row['user_id']): ?>
                    <?php if ($prev_user_id !== null): ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                <div class="user-orders-box">
                    <h2>User ID: <?php echo $row['user_id']; ?></h2>
                    <table class="item-display-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Date Added</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                <?php endif; ?>
                <tr>
                    <td><?php echo $row['order_id']; ?></td>
                    <td><?php echo $row['item_name']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['date_added']; ?></td>
                    <td>$<?php echo $row['total_price']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <?php if ($row['status'] === 'Pending'): ?>
                            <form method="post">
                                <input type="hidden" name="action" value="confirm">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" class="btn">Confirm</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" class="btn">Cancel</button>
                            </form>
                        <?php else: ?>
                            <span>Order <?php echo $row['status']; ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php $prev_user_id = $row['user_id']; ?>
            <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
