<?php 
session_start();

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

// Retrieve barangay names and total registered children
$query = "
    SELECT b.barangay_name, b.barangay_id, COUNT(c.id) AS total_children
    FROM barangay b
    LEFT JOIN children c ON b.barangay_id = c.barangay_id
    GROUP BY b.barangay_name, b.barangay_id
";
$stmt = $pdo->query($query);
$barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    <title>ImmuniTrack - Barangay Management</title>
    <style>
.barangay-box {
    background-color: #f5f9ff; /* Light blue background similar to the design */
    border: 1px solid #e0e0e0; /* Light gray border */
    border-radius: 10px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Softer shadow for depth */
    width: 220px; /* Fixed width similar to user card */
    padding: 10px;
    box-sizing: border-box;
    text-align: center; /* Center-align the text */
    transition: transform 0.3s ease;
    margin: 10px; /* Added margin around each box */
}


.barangay-box:hover {
    transform: translateY(-5px); /* Slight lift effect on hover */
}

.barangay-box .name {
    font-size: 18px; /* Larger font size for barangay name */
    font-weight: bold;
    margin-top: 15px; /* Space between avatar and name */
    color: #333;
}

.barangay-box .info {
    font-size: 14px; /* Slightly smaller text for manager info */
    color: #6c757d; /* Muted color for the info */
}

.barangay-box .icon {
    width: 60px;
    height: 60px;
    border-radius: 30px; /* Slightly rounded square */
    background-color: #e8f0ff; /* Light blue background for icon */
    margin: 0 auto 15px auto; /* Center the avatar with some space below */
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #007bff; /* Example icon or text color for avatar */
}

.barangay-list {
    margin-top: 10px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* Flexible grid layout */
    gap: 5px; /* Increased space between boxes */
    justify-content: center;
    padding: 5px;
}



        .barangay-box {
    background-color: #ffffff; /* White background */
    border: 1px solid #e0e0e0; /* Light gray border similar to user cards */
    border-radius: 10px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Softer shadow for depth */
    width: 220px; /* Fixed width similar to user card */
    padding: 20px;
    box-sizing: border-box;
    text-align: center; /* Center-align the text */
    transition: transform 0.3s ease;
}

.barangay-box:hover {
    transform: translateY(-5px); /* Slight lift effect on hover */
}

.barangay-box .name {
    font-size: 18px; /* Larger font size for barangay name */
    font-weight: bold;
    margin-top: 15px; /* Space between avatar and name */
}

.barangay-box .info {
    font-size: 14px; /* Slightly smaller text for manager info */
    color: #6c757d; /* Muted color for the info */
}

.barangay-box .avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%; /* Circular avatar */
    background-color: #f0f0f0; /* Placeholder background color */
    margin: 0 auto 15px auto; /* Center the avatar with some space below */
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #007bff; /* Example icon or text color for avatar */
}
.barangay-box .status {
    font-size: 14px; /* Slightly larger than info text */
    margin-top: 10px; /* Margin above status */
}

.barangay-box .status span {
    font-weight: bold; /* Bold for the status text */
}



        .add-barangay {
            margin-top: 20px;
            background-color: #ffffff; /* Set to white for consistency */
            padding: 15px; /* Added padding for consistency */
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 300px; /* Same max-width as barangay boxes */
            margin-left: auto; /* Center the box horizontally */
            margin-right: auto; /* Center the box horizontally */
            display: flex; /* Flexbox for centering */
            flex-direction: column; /* Stack elements vertically */
            align-items: center; /* Center items horizontally */
        }
        .add-barangay h2 {
            font-size: 16px;
            margin-bottom: 10px; /* Space between heading and button */
            cursor: pointer; /* Indicate that it's clickable */
        }
        .head-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .head-title .left {
            flex: 1;
        }
        .head-title .right {
            display: flex;
            align-items: center;
        }
        .search-bar {
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
            padding: 5px 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            font-size: 18px;
            color: #007bff;
        }
        .date-time {
            margin-left: auto; /* Push to the right */
            padding: 0 15px; /* Optional padding */
            font-size: 14px; /* Adjust font size as needed */
            color: #333; /* Change text color if desired */
        }

        /* New styles for the button at the bottom right */
        .add-client-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 15px 30px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s;
        }
        .add-client-button:hover {
            background-color: #0056b3;
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
            <li class="active">
                <a href="admin-barangay.php">
                    <i class='bx bxs-home'></i>
                    <span class="text">Barangay</span>
                </a>
            </li>
            <li>
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
            <div class="head-title">
                <div class="left">
                    <h1></h1>
                </div>
            </div>

            <div class="barangay-list">
    <?php if (isset($barangays) && count($barangays) > 0): ?>
        <?php foreach ($barangays as $barangay): ?>
            <?php if (!empty($barangay['barangay_name']) && $barangay['barangay_name'] !== 'N/A'): ?> <!-- Skip empty or N/A barangay names -->
                <div class="barangay-box">
                    <div class="icon"><i class='bx bxs-home'></i></div>
                    <div class="name">
                        <?php echo htmlspecialchars($barangay['barangay_name']); ?> <!-- Display barangay name -->
                    </div>
                    <div class="info">
                        Registered Children: <?php echo htmlspecialchars($barangay['total_children'] ?? 0); ?> <!-- Display total children -->
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No barangays have been registered yet.</p>
    <?php endif; ?>
</div>






        </main>
        <!-- MAIN -->

        <!-- Add New Client Button -->
        <button class="add-client-button" onclick="location.href='add-client.php'">
            &#43; Add New Client
        </button>
    </section>
    <!-- CONTENT -->

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
