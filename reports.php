<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600&display=swap');

        :root {
            --green: #27ae60;
            --black: #333;
            --white: #fff;
            --bg-color: #eee;
            --box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1);
            --border: .1rem solid var(--black);
        }

        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
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

        .btn {
            display: block;
            width: 100%;
            cursor: pointer;
            border-radius: .5rem;
            margin-top: 1rem;
            font-size: 1.6rem;
            padding: 1rem 3rem;
            background: var(--green);
            color: var(--white);
            text-align: center;
        }

        body {
            display: flex;
            justify-content: center;
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

        .report-section {
            margin-bottom: 2rem;
        }

        .report-title {
            font-size: 2.4rem;
            margin-bottom: 1rem;
        }

        .report-content {
            background: var(--bg-color);
            padding: 2rem;
            border-radius: .5rem;
            box-shadow: var(--box-shadow);
            text-align: left;
        }

        .report-content table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-content table, .report-content th, .report-content td {
            border: var(--border);
        }

        .report-content th, .report-content td {
            padding: 1rem;
            text-align: left;
        }

        .report-content th {
            background: var(--black);
            color: var(--white);
        }

        .report-content td {
            background: var(--white);
        }

        canvas {
            display: block;
            margin: 0 auto;
            max-width: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
<div id="about-section">
    <aside class="sidebar">
        <nav>
            <ul>
                <li><button class="btn" onclick="location.href='dashboard.php'">Dashboard</button></li>
                <li><button class="btn" onclick="location.href='view_items.php'">View Items</button></li>
                <li><button class="btn" onclick="location.href='view_orders.php'">View Orders</button></li>
                <li><button class="btn" onclick="location.href='add_items.php'">Manage Items</button></li>
                <li><button class="btn" onclick="location.href='reports.php'">Reports</button></li><br><br><br>
                <li><button class="btn" onclick="location.href='logout.php'">Logout</button></li>
            </ul>
        </nav>
    </aside>
    <div class="main-content">
        <?php
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "12345";
        $dbname = "techiemart";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch sales today and yesterday
        $sales_today_query = "SELECT SUM(amount) AS total FROM sales WHERE sale_date = CURDATE()";
        $sales_yesterday_query = "SELECT SUM(amount) AS total FROM sales WHERE sale_date = CURDATE() - INTERVAL 1 DAY";
        $sales_today_result = $conn->query($sales_today_query);
        $sales_yesterday_result = $conn->query($sales_yesterday_query);
        $sales_today = $sales_today_result->fetch_assoc()['total'];
        $sales_yesterday = $sales_yesterday_result->fetch_assoc()['total'];

        // Fetch annual sales
        $annual_sales_query = "SELECT YEAR(sale_date) as year, SUM(amount) as total FROM sales GROUP BY YEAR(sale_date)";
        $annual_sales_result = $conn->query($annual_sales_query);
        $annual_sales = [];
        while ($row = $annual_sales_result->fetch_assoc()) {
            $annual_sales[] = $row;
        }

        // Fetch inventory
        $inventory_query = "SELECT i.item_name, inv.quantity FROM inventory inv JOIN items i ON inv.item_id = i.item_id";
        $inventory_result = $conn->query($inventory_query);
        $inventory = [];
        while ($row = $inventory_result->fetch_assoc()) {
            $inventory[] = $row;
        }

        // Fetch recent user activity
        $user_activity_query = "SELECT user_id, activity, timestamp FROM user_activity ORDER BY timestamp DESC LIMIT 10";
        $user_activity_result = $conn->query($user_activity_query);
        $user_activity = [];
        while ($row = $user_activity_result->fetch_assoc()) {
            $user_activity[] = $row;
        }

        // Fetch item movement
        $item_movement_query = "SELECT i.item_name, movement_date, movement_type, quantity FROM item_movement im JOIN items i ON im.item_id = i.item_id ORDER BY movement_date DESC LIMIT 10";
        $item_movement_result = $conn->query($item_movement_query);
        $item_movement = [];
        while ($row = $item_movement_result->fetch_assoc()) {
            $item_movement[] = $row;
        }

        // Fetch top 10 items
        $top_items_query = "SELECT i.item_name, SUM(s.quantity) as total_quantity FROM sales s JOIN items i ON s.item_id = i.item_id GROUP BY s.item_id ORDER BY total_quantity DESC LIMIT 10";
        $top_items_result = $conn->query($top_items_query);
        $top_items = [];
        while ($row = $top_items_result->fetch_assoc()) {
            $top_items[] = $row;
        }

        $conn->close();
        ?>
        <div class="report-section">
            <h2 class="report-title">Sales Today vs Yesterday</h2>
            <div class="report-content">
                <p>Sales Today: $<?= $sales_today ?: 0 ?></p>
                <p>Sales Yesterday: $<?= $sales_yesterday ?: 0 ?></p>
            </div>
        </div>
        <div class="report-section">
            <h2 class="report-title">Annual Sales Distribution</h2>
            <div class="report-content">
                <canvas id="myChart"></canvas>
            </div>
        </div>
        <div class="report-section">
            <h2 class="report-title">Inventory Report</h2>
            <div class="report-content">
                <table>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                    </tr>
                    <?php foreach ($inventory as $item): ?>
                        <tr>
                            <td><?= $item['item_name'] ?></td>
                            <td><?= $item['quantity'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <div class="report-section">
            <h2 class="report-title">Recent User Activity</h2>
            <div class="report-content">
                <table>
                    <tr>
                        <th>User ID</th>
                        <th>Activity</th>
                        <th>Timestamp</th>
                    </tr>
                    <?php foreach ($user_activity as $activity): ?>
                        <tr>
                            <td><?= $activity['user_id'] ?></td>
                            <td><?= $activity['activity'] ?></td>
                            <td><?= $activity['timestamp'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <div class="report-section">
            <h2 class="report-title">Item Movement Report</h2>
            <div class="report-content">
                <table>
                    <tr>
                        <th>Item Name</th>
                        <th>Movement Date</th>
                        <th>Movement Type</th>
                        <th>Quantity</th>
                    </tr>
                    <?php foreach ($item_movement as $movement): ?>
                        <tr>
                            <td><?= $movement['item_name'] ?></td>
                            <td><?= $movement['movement_date'] ?></td>
                            <td><?= $movement['movement_type'] ?></td>
                            <td><?= $movement['quantity'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <div class="report-section">
            <h2 class="report-title">Top 10 Items</h2>
            <div class="report-content">
                <table>
                    <tr>
                        <th>Item Name</th>
                        <th>Total Quantity Sold</th>
                    </tr>
                    <?php foreach ($top_items as $item): ?>
                        <tr>
                            <td><?= $item['item_name'] ?></td>
                            <td><?= $item['total_quantity'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sample data for the pie chart
    const labels = <?= json_encode(array_column($annual_sales, 'year')) ?>;
    const data = {
        labels: labels,
        datasets: [{
            label: 'Annual Sales Distribution',
            backgroundColor: [
                'rgba(231, 234, 20, 0.69)',
                'rgba(255, 41, 17, 0.69)',
                'rgba(234, 106, 20, 0.69)',
                'rgb(75, 192, 192)',
                'rgba(0, 184, 0, 0.36)'
            ],
            data: <?= json_encode(array_column($annual_sales, 'total')) ?>
        }]
    };

    // Configuration options for the pie chart
    const config = {
        type: 'pie',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Annual Sales Distribution'
                }
            }
        },
    };

    // Create the pie chart
    const myChart = new Chart(
        document.getElementById('myChart'),
        config
    );
</script>
</body>
</html>
