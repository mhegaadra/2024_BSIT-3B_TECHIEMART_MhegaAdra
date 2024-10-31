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
    SELECT b.barangay_name, COUNT(c.id) as total_children
    FROM barangay b
    LEFT JOIN children c ON b.barangay_id = c.barangay_id
    GROUP BY b.barangay_id, b.barangay_name
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data = [];
$colors = [];

foreach ($barangays as $barangay) {
    $labels[] = $barangay['barangay_name'];
    $data[] = $barangay['total_children'];
    // Generate light colors for each barangay
    $colors[] = 'rgba(' . rand(200, 255) . ',' . rand(200, 255) . ',' . rand(200, 255) . ', 0.7)';
}

// Format the current date
$currentDate = date('l, d/m/Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <title>ImmuniTrack - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.css' rel='stylesheet' />
    <style>
/* Set font family for the entire body */
body {
    font-family: 'Poppins', sans-serif;
    overflow-x: hidden;
    height: 100vh;
}

/* Fix the navbar at the top */
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background-color: #fff;
    padding: 10px 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: flex;
    align-items: center;
}

.date-time {
    margin-left: auto;
    padding: 0 15px;
    font-size: 14px;
    color: #333;
}

.main-content {
    margin-top: 70px;
    width: 70%;
    display: flex;
    flex-direction: column;
    align-items: center;
    background-color: #f0f0f0;
    padding: 20px;
    margin: 0 auto;
}

/* Box container styling */
.box-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin-bottom: 20px;
}

/* Side box styling */
.side-box {
    flex: 1 1 30%;
    margin: 10px;
    background: #fff;
    padding: 15px;
    min-height: 100px;
    text-align: left;
}

.side-box h3 {
    font-size: 1.4em;
    color: #333;
    margin: 0;
    padding-bottom: 10px;
}

.side-box p {
    font-size: 0.9em;
    color: #666;
    margin: 5px 0;
}

/* Resizing Connect with Us and Feedback boxes */
.connect-with-us-box, .feedback-box {
    background: #fff;
    padding: 15px;
    text-align: left;
    width: 100%;
    max-width: 400px;
    margin: 0 auto; /* Set top and bottom margin to 0, keeping auto for left/right */
    min-height: 150px;
    border-radius: 5px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.feedback-box button {
    background-color: transparent;
    border: 1px solid #FF6600;
    color: #FF6600;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s; /* Smooth transition */
}

.feedback-box button:hover {
    background-color: #FF6600; /* Change background color on hover */
    color: #fff; /* Change text color on hover */
    border-color: #FF6600; /* Keep border same */
}

.feedback-box button i {
    margin-right: 5px;
    color: #FF6600; /* Ensure icon color matches text color */
    transition: color 0.3s; /* Smooth transition for icon color */
}

.feedback-box button:hover i {
    color: #fff; /* Change icon color to white on hover */
}

/* Adjusted styles for Recent Activities box */
.side-box.recent-activities-box {
    margin-top: -15px; /* Adjust this negative value to maximize upward space */
    background: #fff; /* Ensure background color for visibility */
    padding: 15px; /* Add padding for aesthetics */
    border-radius: 5px; /* Optional: Add border radius for rounded corners */
}

/* Red dot for recent activities */
.recent-activities-list li {
    position: relative;
    padding-left: 20px; /* To create space for the dot */
}

.recent-activities-list li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 8px;
    height: 8px;
    background-color: red;
    border-radius: 50%;
}




    </style>
<body>
<!-- SIDEBAR -->
<section id="sidebar">
    <a href="#" class="brand">
        <i class='bx bxs-injection'></i>
        <span class="text">ImmuniTrack</span>
    </a>
    <ul class="side-menu top">
        <li class="active">
            <a href="admin-dashboard.php">
                <i class='bx bxs-dashboard'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
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
<main class="main-content">
    <div class="container">
    <div class="box-container">
<!-- Box 4: Recent Activities in RHU -->
<div class="side-box recent-activities-box">
    <div>
        <h3>
            <i class="bx bxs-bell" style="color: #FFA726;"></i>
            Recent Activities
        </h3>
        <ul class="recent-activities-list">
            <li>Health Awareness Campaign conducted in Barangay Sagpon.</li>
            <li>Immunization drive completed for children under 5 years.</li>
            <li>COVID-19 vaccination slots filled for Barangay Binitayan.</li>
            <li>Monthly health statistics report generated.</li>
        </ul>
    </div>
</div>

            <div class="box-container">
                <!-- Box 1: Vaccination Coverage -->
                <div class="side-box vaccination-coverage-box">
                    <div>
                        <h3>
                            <i class="bx bxs-chart" style="font-size: 25px; color: #FF6F61;"></i>
                            Vaccination Coverage
                        </h3>
                        <p>
                            Monitor vaccination rates in each barangay to meet coverage goals.
                        </p>
                    </div>
                </div>

                <!-- Box 2: Upcoming Checkups -->
                <div class="side-box upcoming-checkups-box">
                    <div>
                        <h3>
                            <i class="bx bxs-calendar-event" style="color: #77CDFF;"></i>
                            Upcoming Checkups
                        </h3>
                        <p>
                            Scheduled checkups for children in various barangays. Don’t miss out!
                        </p>
                    </div>
                </div>

                <!-- Box 3: Community Health Workshops -->
                <div class="side-box health-workshops-box">
                    <div>
                        <h3>
                            <i class="bx bxs-group" style="color: #72BF78;"></i>
                            Health Workshops
                        </h3>
                        <p>
                            Join workshops on maternal and child health, nutrition, and hygiene.
                        </p>
                    </div>
                </div>
            </div>

<!-- Box 5: Connect with Us -->
<div class="side-box connect-with-us-box" style="background: none;">
    <div>
        <h3>
            <i class="bx bxs-envelope" style="color: #FF6F61;"></i>
            Connect with Us
        </h3>
        <p>
            For any concerns about the system, just email the administrator at <a href="mailto:immunitrack2024@gmail.com" style="color: #007BFF; text-decoration: underline;">immunitrack2024@gmail.com</a>.
        </p>
    </div>
</div>

<div class="side-box feedback-box" style="padding: 15px; border-radius: 5px; background: none;">
    <div>
        <h3>
            <i class="bx bxs-feedback" style="color: #6C757D;"></i>
            Need Feedback?
        </h3>
        <p>
            Let us know if you encounter any problems or have concerns regarding the Immunization Tracking System.
        </p>
        <button id="send-feedback-btn">
            <i class="bx bxs-star" style="margin-right: 5px;"></i> <!-- Star icon -->
            Send Feedback
        </button>
    </div>
</div>
</main>

</section>

<!-- FullCalendar JS -->
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

