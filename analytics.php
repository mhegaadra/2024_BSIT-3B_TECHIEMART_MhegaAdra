<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login-user.php');
    exit();
}

// Database connection settings
$host = 'localhost';        
$db = 'immuni_track';       
$user = 'root';             
$pass = '12345';            

try {
    // Create a PDO instance (connect to the database)
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // Set PDO error mode to exception to handle errors gracefully
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If the connection fails, output the error message
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}

// Get the user's email from the session
$email = $_SESSION['email'];

// Fetch the user's details and barangay from the database
$query = "SELECT usertable.email, barangay.barangay_name
          FROM usertable
          INNER JOIN barangay ON usertable.id = barangay.user_id
          WHERE usertable.email = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Extract the first letter of the email
    $initial = strtoupper(substr($user['email'], 0, 1));

    // Generate the profile image URL with the initial
    $profileImageUrl = "https://ui-avatars.com/api/?name={$initial}&background=random&color=fff";

    // Get the barangay name for the logged-in user
    $barangayName = $user['barangay_name'];
} else {
    // If the user is not found, redirect to the login page
    header('Location: login-user.php');
    exit();
}

// Fetch the number of registered children for the logged-in user's barangay by age group
$ageGroups = [
    '0-3 months' => 0,
    '4-6 months' => 0,
    '7-9 months' => 0,
    '10-12 months' => 0,
];

$query = "SELECT
              CASE
                  WHEN TIMESTAMPDIFF(MONTH, date_of_birth, CURDATE()) BETWEEN 0 AND 3 THEN '0-3 months'
                  WHEN TIMESTAMPDIFF(MONTH, date_of_birth, CURDATE()) BETWEEN 4 AND 6 THEN '4-6 months'
                  WHEN TIMESTAMPDIFF(MONTH, date_of_birth, CURDATE()) BETWEEN 7 AND 9 THEN '7-9 months'
                  WHEN TIMESTAMPDIFF(MONTH, date_of_birth, CURDATE()) BETWEEN 10 AND 12 THEN '10-12 months'
              END AS age_group,
              COUNT(*) AS count
          FROM children
          INNER JOIN barangay ON children.barangay_id = barangay.barangay_id
          WHERE barangay.barangay_name = ?
          GROUP BY age_group
          HAVING age_group IS NOT NULL";

$stmt = $pdo->prepare($query);
$stmt->execute([$barangayName]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Populate the ageGroups array with the counts
foreach ($results as $result) {
    $ageGroups[$result['age_group']] = $result['count'];
}

// Prepare data for the chart
$labels = array_keys($ageGroups);
$data = array_values($ageGroups);

// Format the current date
$currentDate = date('l, d/m/Y');

// Fetch recently registered children
$recentChildrenQuery = "SELECT first_name, last_name, registration_date 
                         FROM children
                         INNER JOIN barangay ON children.barangay_id = barangay.barangay_id
                         WHERE barangay.barangay_name = ?
                         ORDER BY registration_date DESC 
                         LIMIT 5"; // Adjust the limit as needed

$stmt = $pdo->prepare($recentChildrenQuery);
$stmt->execute([$barangayName]); // Use the logged-in user's barangay name
$recentChildren = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get the month filter from the URL
// Month filter (1-12)
$monthFilter = isset($_GET['monthFilter']) ? $_GET['monthFilter'] : '';

// Fetch the number of registered children for the logged-in user's barangay by age group
$ageGroups = [
    '0-3 months' => 0,
    '4-6 months' => 0,
    '7-9 months' => 0,
    '10-12 months' => 0,
];

$query = "SELECT
              CASE
                  WHEN TIMESTAMPDIFF(MONTH, date_of_birth, CURDATE()) BETWEEN 0 AND 3 THEN '0-3 months'
                  WHEN TIMESTAMPDIFF(MONTH, date_of_birth, CURDATE()) BETWEEN 4 AND 6 THEN '4-6 months'
                  WHEN TIMESTAMPDIFF(MONTH, date_of_birth, CURDATE()) BETWEEN 7 AND 9 THEN '7-9 months'
                  WHEN TIMESTAMPDIFF(MONTH, date_of_birth, CURDATE()) BETWEEN 10 AND 12 THEN '10-12 months'
              END AS age_group,
              COUNT(*) AS count
          FROM children
          INNER JOIN barangay ON children.barangay_id = barangay.barangay_id
          WHERE barangay.barangay_name = ?
          AND MONTH(registration_date) = ?  -- Add this condition for month filter
          GROUP BY age_group
          HAVING age_group IS NOT NULL";

$stmt = $pdo->prepare($query);
$stmt->execute([$barangayName, $monthFilter]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Populate the ageGroups array with the counts
foreach ($results as $result) {
    $ageGroups[$result['age_group']] = $result['count'];
}

// Prepare data for the chart
$labels = array_keys($ageGroups);
$data = array_values($ageGroups);

// Fetch recently registered children
$recentChildrenQuery = "SELECT first_name, last_name, registration_date 
                         FROM children
                         INNER JOIN barangay ON children.barangay_id = barangay.barangay_id
                         WHERE barangay.barangay_name = ? 
                         ORDER BY registration_date DESC"; // Removed LIMIT 5

$stmt = $pdo->prepare($recentChildrenQuery);
$stmt->execute([$barangayName]); // Ensure you are passing the correct number of parameters
$recentChildren = $stmt->fetchAll(PDO::FETCH_ASSOC);



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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <title>ImmuniTrack - Analytics</title>
    <style>
        /* Add this CSS to center the brand text */
        #sidebar .brand {
            text-align: center;
        }
        #sidebar .brand .text {
            display: inline-block;
        }
        /* Styling the container to create two columns */
        .analytics-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 20px; /* Space between the chart and text */
            margin: 40px auto;
            margin-top: 50px;
            max-width: 900px; /* Restricting the maximum width */
        }
        /* Style for the chart container */
        #chart-container {
            flex: 1;
            width: 300px;  /* Smaller chart width */
            margin: auto;
            padding: 20px;
        }
        canvas {
            max-width: 100%;  /* Ensure canvas fits within the container */
            height: auto;     /* Maintain aspect ratio */
        }
        /* Text section */
        .analytics-text {
            flex: 1;
            padding: 50px;
            background-color: #f5f5f5;
            max-width: 300px; /* Limit the width of the text section */
            transition: all 0.3s ease-in-out; /* Smooth transition for hover effects */
        }
        .analytics-text h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .analytics-text p {
            font-size: 16px;
            margin-bottom: 15px;
        }
        /* Highlighting the number of children */
        .highlight-number {
            font-size: 20px; /* Increase font size */
            font-weight: bold; /* Make it bold */
            color: #FF5733; /* Change color to a vibrant shade */
            background-color: #FCE4D6; /* Light background color */
            padding: 5px; /* Padding around the number */
        }



    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <i class='bx bxs-injection'></i> <!-- Updated icon -->
            <span class="text">ImmuniTrack</span> <!-- Updated name -->
        </a>
        <ul class="side-menu top">
            <li>
                <a href="dashboard.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li class="">
                <a href="calendar.php">
                    <i class='bx bxs-calendar-event'></i>
                    <span class="text">Calendar</span>
                </a>
            </li>
            <li class="active">
                <a href="analytics.php">
                    <i class='bx bxs-doughnut-chart'></i>
                    <span class="text">Analytics</span>
                </a>
            </li>
            <li>
                <a href="inventory.php">
                    <i class='bx bxs-package'></i>
                    <span class="text">Inventory</span>
                </a>
            </li>
            <li>
                <a href="children.php">
                    <i class='bx bxs-group'></i>
                    <span class="text">Children Profile</span>
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
            <form action="#"></form>
            <a href="user-info.php" class="profile">
                <img id="profile-image" src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['email'][0]) ?>&background=random&color=fff" alt="Profile">
            </a>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
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



<!-- Parent container to center the inner content -->
<div style="display: flex; justify-content: center; align-items: center; height: 80vh; ">

    <div class="container" style="display: flex; flex-direction: column; gap: 20px; padding: 20px; max-width: 1200px; width: 100%;">
        <!-- First row: Recently Registered Children on the left, Number of children on the right -->
        <div style="display: flex; justify-content: space-between; gap: 20px;">
            <!-- Recently Registered Children (Left) -->
            <div style="width: 48%; padding: 20px; background-color: white;">
                <p style="font-weight: bold; font-size: 1.1em; margin-bottom: 20px;">Recently Registered Children</p>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                    <thead>
                        <tr>
                            <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Name</th>
                            <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentChildren)): ?>
                            <?php foreach ($recentChildren as $child): ?>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($child['first_name']); ?> <?php echo htmlspecialchars($child['last_name']); ?></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd; color: #ff5722; font-weight: bold;"><?php echo htmlspecialchars(date('F j, Y', strtotime($child['registration_date']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" style="padding: 8px; text-align: center;">No recent registrations found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Number of Children (Right) -->
            <div style="width: 48%; padding: 20px; background-color: white;">
                <p style="font-weight: bold; font-size: 1.1em; margin-bottom: 10px;">Number of children: <span style="color: red;"><?php echo htmlspecialchars(array_sum($data)); ?></span></p>
                
                <div class="month-selector-container" style="margin-top: 10px;">
    <label for="month-selector" style="font-weight: normal; font-size: 14px;">Filter by Month:</label>
    <form method="GET" action="">
        <select id="month-selector" name="monthFilter" onchange="this.form.submit()" style="padding: 5px; font-size: 14px; width: 50%;">
            <option value="01" <?= $monthFilter == '01' ? 'selected' : ''; ?>>January</option>
            <option value="02" <?= $monthFilter == '02' ? 'selected' : ''; ?>>February</option>
            <option value="03" <?= $monthFilter == '03' ? 'selected' : ''; ?>>March</option>
            <option value="04" <?= $monthFilter == '04' ? 'selected' : ''; ?>>April</option>
            <option value="05" <?= $monthFilter == '05' ? 'selected' : ''; ?>>May</option>
            <option value="06" <?= $monthFilter == '06' ? 'selected' : ''; ?>>June</option>
            <option value="07" <?= $monthFilter == '07' ? 'selected' : ''; ?>>July</option>
            <option value="08" <?= $monthFilter == '08' ? 'selected' : ''; ?>>August</option>
            <option value="09" <?= $monthFilter == '09' ? 'selected' : ''; ?>>September</option>
            <option value="10" <?= $monthFilter == '10' ? 'selected' : ''; ?>>October</option>
            <option value="11" <?= $monthFilter == '11' ? 'selected' : ''; ?>>November</option>
            <option value="12" <?= $monthFilter == '12' ? 'selected' : ''; ?>>December</option>
        </select>
    </form>
</div>

            </div>
        </div>

        <!-- Container for both charts (Bar Chart and Line Chart) -->
        <div style="display: flex; justify-content: space-between; width: 100%; padding: 20px;">

            <!-- Bar Chart section (Left) -->
            <div style="width: 50%; padding: 20px;">
                <canvas id="barangayChart" style="width: 100%; height: 200px;"></canvas> <!-- Bar chart height -->
            </div>

            <!-- Line Chart section (Right) -->
            <div style="width: 50%; padding: 20px;">
                <canvas id="ageGroupLineChart" style="width: 200%; height: 200px;"></canvas> <!-- Line chart -->
            </div>

        </div>
    </div>
</div>

<script>
// Chart.js Script
const ctxBar = document.getElementById('barangayChart').getContext('2d');
const barangayChart = new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
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
            barThickness: 60, // Set a fixed width for the bars (e.g., 30 pixels)
            // maxBarThickness: 30 // Uncomment this line to set a maximum bar width instead
        }]
    },
    options: {
        scales: {
            x: {
                beginAtZero: true
            },
            y: {
                beginAtZero: true,
                ticks: {
                    // This will filter out the unwanted tick labels
                    callback: function(value) {
                        // Return an empty string for specific unwanted values
                        if (value === 0 || value === 0.2 || value === 0.4 || value === 0.6 || value === 0.8 || value === 1.0) {
                            return '';
                        }
                        return value; // Return the value for other cases
                    }
                }
            }
        }
    }
});



    // Line Chart: Age Group Distribution
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
                    display: false, // Hide the x-axis title
                }
            },
            y: {
                title: {
                    display: false, // Hide the y-axis title
                },
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        // Convert numerical values to month names
                        const months = [
                            'January', 'February', 'March', 'April', 'May', 
                            'June', 'July', 'August', 'September', 'October', 
                            'November', 'December'
                        ];
                        return months[index]; // Return the month corresponding to the index
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


