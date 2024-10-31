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

// Get the selected month from the query string or default to January
$selectedMonth = isset($_GET['monthFilter']) ? $_GET['monthFilter'] : '01';

// Query to get the barangay names and the number of children registered in the selected month
$query = "
    SELECT b.barangay_name, COUNT(c.id) as total_children
    FROM barangay b
    LEFT JOIN children c ON b.barangay_id = c.barangay_id AND MONTH(c.registration_date) = :selectedMonth
    GROUP BY b.barangay_id, b.barangay_name
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_STR);
$stmt->execute();
$barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the chart
$labels = [];
$data = [];
foreach ($barangays as $barangay) {
    $labels[] = $barangay['barangay_name'];
    $data[] = $barangay['total_children'];
}

// Format the current date
$currentDate = date('l, d/m/Y');

// Query to get recently registered children with their barangay
$recentChildrenQuery = "
    SELECT CONCAT(c.first_name, ' ', c.last_name) AS name, b.barangay_name 
    FROM children c
    JOIN barangay b ON c.barangay_id = b.barangay_id
    ORDER BY c.registration_date DESC
    LIMIT 5
";
$recentStmt = $pdo->prepare($recentChildrenQuery);
$recentStmt->execute();
$recentChildren = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

// Query to get the total number of registered children in the selected month
$totalChildrenQuery = "
    SELECT COUNT(c.id) as total_children
    FROM children c
    WHERE MONTH(c.registration_date) = :selectedMonth
";

// Prepare and execute the query
$totalStmt = $pdo->prepare($totalChildrenQuery);
$totalStmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_STR);
$totalStmt->execute();
$totalChildrenCount = $totalStmt->fetchColumn(); // Get the total count
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <title>ImmuniTrack - Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
/* Container for charts and table */
#chart-and-table-container { 
    display: flex;
    justify-content: center;
    align-items: flex-start;
    margin: 40px auto 10px;
    width: 90%;
}

/* Styling for the boxes containing recent children section */
.empty-white-box,
.recent-children-section {
    background-color: white;
    padding: 20px;
    width: 30%; /* Width of the section */
    min-height: 100px;
    margin: 0 15px; /* Adjusted margin for equal spacing */
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); /* Optional shadow for better appearance */
    border-radius: 5px; /* Optional rounded corners */
}

/* Scrollable content inside recent children section */
.recent-children-section {
    max-height: 250px; /* Adjusted max height for six rows with scrollbar */
    overflow-y: auto; /* Enable vertical scrolling within the box */
}

/* Styling for the table of recently registered children */
.recent-children-table {
    width: 100%; 
    border-collapse: collapse; 
    font-size: 0.9em;
}

.recent-children-table th,
.recent-children-table td {
    text-align: left;
    padding: 8px; 
    border-bottom: 1px solid #ddd;
}

/* Container for the charts (bar and line) */
#charts-container {
    display: flex;
    justify-content: center; /* Center the graphs horizontally */
    align-items: flex-start; /* Align items at the start */
    width: 90%;
    margin: 5px auto; /* Reduced top margin to 10px */
}

.chart-box {
    width: 48%; /* Each chart takes up 48% of the total width */
    height: 350px; /* Set a fixed height */
    padding: 10px;
    margin: 15px 15px; /* Reduced top margin to 15px, left and right unchanged */
    display: flex;
    justify-content: center;
    align-items: center; /* Center the content within each chart box */
}

/* Ensure canvas charts are responsive */
.chart-container canvas {
    width: 100% !important;
    height: 100% !important;
}
</style>



</head>
<body>
<!-- SIDEBAR -->
<section id="sidebar">
    <!-- Sidebar Content -->
    <a href="#" class="brand">
        <i class='bx bxs-injection'></i>
        <span class="text">ImmuniTrack</span>
    </a>
    <ul class="side-menu top">
        <li><a href="admin-dashboard.php"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
        <li class="active"><a href="admin-analytics.php"><i class='bx bxs-doughnut-chart'></i> Analytics</a></li>
        <li><a href="admin-barangay.php"><i class='bx bxs-home'></i> Barangay</a></li>
        <li><a href="admin-inventory.php"><i class='bx bxs-package'></i> Inventory</a></li>
    </ul>
    <ul class="side-menu">
        <li><a href="logout-user.php" class="logout"><i class='bx bxs-log-out-circle'></i> Logout</a></li>
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
        <!-- Flex Container for Top Section -->
        <div id="top-container" style="display: flex; gap: 20px;">

            <!-- Total Children Box -->
            <div class="empty-white-box" style="width: 48%; padding: 20px;">
                <p style="font-weight: bold; font-size: 1.1em;">Number of children: 
                    <span style="color: red;"><?php echo $totalChildrenCount; ?></span>
                </p>
                
                <div class="month-selector-container" style="margin-top: 10px;">
                    <label for="month-selector" style="font-weight: normal; font-size: 14px;">Filter by Month:</label>
                    <form method="GET" action="">
                        <select id="month-selector" name="monthFilter" onchange="this.form.submit()" style="padding: 5px; font-size: 14px;">
                            <option value="01" <?php echo $selectedMonth == '01' ? 'selected' : ''; ?>>January</option>
                            <option value="02" <?php echo $selectedMonth == '02' ? 'selected' : ''; ?>>February</option>
                            <option value="03" <?php echo $selectedMonth == '03' ? 'selected' : ''; ?>>March</option>
                            <option value="04" <?php echo $selectedMonth == '04' ? 'selected' : ''; ?>>April</option>
                            <option value="05" <?php echo $selectedMonth == '05' ? 'selected' : ''; ?>>May</option>
                            <option value="06" <?php echo $selectedMonth == '06' ? 'selected' : ''; ?>>June</option>
                            <option value="07" <?php echo $selectedMonth == '07' ? 'selected' : ''; ?>>July</option>
                            <option value="08" <?php echo $selectedMonth == '08' ? 'selected' : ''; ?>>August</option>
                            <option value="09" <?php echo $selectedMonth == '09' ? 'selected' : ''; ?>>September</option>
                            <option value="10" <?php echo $selectedMonth == '10' ? 'selected' : ''; ?>>October</option>
                            <option value="11" <?php echo $selectedMonth == '11' ? 'selected' : ''; ?>>November</option>
                            <option value="12" <?php echo $selectedMonth == '12' ? 'selected' : ''; ?>>December</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Recent Children Box -->
            <div class="recent-children-section" style="width: 48%; padding: 20px;">
                <h3>Recently Registered Children</h3>
                <?php if (empty($recentChildren)): ?>
                    <p>No recently registered children.</p>
                <?php else: ?>
                    <table class="recent-children-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Barangay</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentChildren as $child): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($child['name']); ?></td>
                                    <td><?php echo htmlspecialchars($child['barangay_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

<!-- Container for the two charts side by side -->
<div id="charts-container">
    <!-- Bar Chart Container -->
    <div class="chart-box">
        <canvas id="barangayChart"></canvas>
    </div>

    <!-- Line Chart Container -->
    <div class="chart-box">
        <canvas id="ageGroupLineChart"></canvas>
    </div>
</div>
    </main>
    <!-- MAIN -->
</section>
<!-- CONTENT -->

<script>
// Bar Chart (Barangay Data)
const ctxBar = document.getElementById('barangayChart').getContext('2d');
const barangayChart = new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: ['0-3 months', '4-6 months', '7-9 months', '10-11 months'], // X-axis labels (age ranges)
        datasets: [{
            label: 'Number of Children',
            data: <?php echo json_encode($data); ?>,
            backgroundColor: [
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255, 99, 132, 1)',
            ],
            barThickness: 60, // Adjust bar width
        }]
    },
    options: {
        scales: {
            x: {
                beginAtZero: true
            },
            y: {
                beginAtZero: true
            }
        }
    }
});


// Line Chart (Age Group Distribution)
const ageGroupLineCtx = document.getElementById('ageGroupLineChart').getContext('2d');
const ageGroupLineChart = new Chart(ageGroupLineCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: '', // Removed the label text
            data: <?php echo json_encode($data); ?>,
            backgroundColor: 'rgba(0, 123, 255, 0.5)',
            borderColor: 'rgba(0, 123, 255, 1)',
            borderWidth: 1,
            fill: false
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                title: {
                    display: false, // Hide x-axis title
                }
            },
            y: {
                title: {
                    display: false, // Hide y-axis title
                },
                beginAtZero: true,
                ticks: {
                    callback: function(value, index) {
                        const months = [
                            'January', 'February', 'March', 'April', 'May', 
                            'June', 'July', 'August', 'September', 'October', 
                            'November', 'December'
                        ];
                        return months[index];
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false // Hide the legend
            }
        }
    }
});
</script>
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
