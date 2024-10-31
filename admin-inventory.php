<?php  
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login-user.php');
    exit();
}

// Set the default timezone to Philippine Standard Time
date_default_timezone_set('Asia/Manila');

// Database connection settings
$host = 'localhost';
$db = 'immuni_track';
$user = 'root';
$pass = '12345';

try {
    // Create a PDO instance (connect to the database)
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If the connection fails, output the error message
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}

$query = "
SELECT b.barangay_name, b.barangay_id, SUM(i.stock) AS total_stock
FROM barangay b
LEFT JOIN inventory i ON b.barangay_id = i.barangay_id
GROUP BY b.barangay_name, b.barangay_id
";


$stmt = $pdo->prepare($query);
$stmt->execute();
$barangayVaccines = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format the current date
$currentDate = date('l, d/m/Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="style.css">
    <title>ImmuniTrack - Inventory Management</title>
    <style>
        /* Existing styles */
        .main-content {
            max-width: 800px;
            margin: auto;
            padding: 5px;
        }

        /* New Inventory-specific styles */
        .inventory-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .inventory-cards .inventory-item {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.2s ease-in-out;
            cursor: pointer;
        }

        .inventory-cards .inventory-item:hover {
            transform: translateY(-5px);
        }

        .inventory-cards .item-icon {
            font-size: 40px;
            color: #007bff;
            margin-bottom: 15px;
        }

        .inventory-cards .item-details .name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .inventory-cards .item-details .info {
            font-size: 14px;
            color: #6c757d;
        }

        /* Search bar styling */
        .search-bar {
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
            padding: 5px 8px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-left: auto;
        }

        .search-bar input[type="text"] {
            border: none;
            padding: 5px;
            background: none;
            outline: none;
            flex: 1;
        }

        .search-bar button {
            background-color: transparent;
            border: none;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
        }

        .search-bar button i {
            font-size: 16px;
            color: #007bff;
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <i class='bx bxs-injection'></i>
            <span class="text">ImmuniTrack</span>
        </a>
        <ul class="side-menu top">
        <li class="">
        <a href="admin-dashboard.php">
            <i class='bx bxs-dashboard'></i>
            <span class="text">Dashboard</span>
        </a>
            <li>
                <a href="admin-analytics.php">
                    <i class='bx bxs-doughnut-chart'></i>
                    <span class="text">Analytics</span>
                </a>
            </li>
            <li>
                <a href="admin-barangay.php">
                    <i class='bx bxs-home'></i>
                    <span class="text">Barangay</span>
                </a>
            </li>
            <li class="active">
                <a href="admin-inventory.php">
                    <i class='bx bxs-package'></i>
                    <span class="text">Inventory</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="logout-user.php" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <span id="date_now" class="d-none d-sm-block"><?php echo $currentDate; ?></span>
            <span id="current-time" class="clock ps-2 text-muted"></span>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="main-content">
            <div class="inventory-cards">
            <!-- Loop to display inventory items -->
            <?php if (count($barangayVaccines) > 0): ?>
            <?php foreach ($barangayVaccines as $barangay): ?>
                <div class="inventory-item" onclick="location.href='update-inventory.php?barangay_id=<?php echo htmlspecialchars($barangay['barangay_id']); ?>'">
                    <div class="item-icon">
                        <i class='bx bxs-package'></i>
                    </div>
                    <div class="item-details">
                        <div class="name"><?php echo htmlspecialchars($barangay['barangay_name']); ?></div>
                        <div class="info">Total Stock: <?php echo htmlspecialchars($barangay['total_stock']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No barangays have been registered yet.</p>
        <?php endif; ?>
    </div>
</div>

        </main>
    </section>
    <!-- CONTENT -->

    <!-- Script to update current time -->
    <script>
        // Function to update the time
        function updateTime() {
            const now = new Date();
            const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true, timeZone: 'Asia/Manila' };
            const timeString = now.toLocaleTimeString('en-US', options);
            document.getElementById('current-time').textContent = timeString;
        }

        setInterval(updateTime, 1000); // Update time every second
        updateTime(); // Initial call
    </script>
</body>
</html>
