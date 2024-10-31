<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login-user.php');
    exit();
}

// Database connection settings
$host = 'localhost';        // Database host
$db = 'immuni_track';       // Database name
$user = 'root';             // Database username
$pass = '12345';            // Database password

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

// Fetch the user's details and barangay ID from the database
$query = "SELECT u.email, b.barangay_id 
          FROM usertable u
          JOIN barangay b ON u.id = b.user_id 
          WHERE u.email = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Extract the user's barangay ID and email initial
    $barangay_id = $user['barangay_id'];
    $initial = strtoupper(substr($user['email'], 0, 1));

    // Generate the profile image URL with the initial
    $profileImageUrl = "https://ui-avatars.com/api/?name={$initial}&background=random&color=fff";
} else {
    // If the user is not found, redirect to the login page
    header('Location: login-user.php');
    exit();
}

// Fetch vaccine inventory data for the user's barangay
$query = "SELECT vaccine_name, stock 
          FROM inventory 
          WHERE barangay_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$barangay_id]);
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <title>ImmuniTrack - Inventory</title>
    <style>
    /* Add this CSS to center the brand text */
 /* Add this CSS to center the brand text */
 #sidebar .brand {
        text-align: center;
    }
    #sidebar .brand .text {
        display: inline-block;
    }

    .table-data {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }

    table {
        width: 100%;
        max-width: 990px; /* You can adjust this value based on your layout */
        border-collapse: collapse;
        background-color: white;
        border-radius: 8px;
        margin: auto; /* Centers the table */
        padding: 20px; /* Adds padding around the table */
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left; /* Center align the text */
        vertical-align: top;
        white-space: normal;
    }

    th {
        background-color: #3498db;
        color: white;
        font-weight: bold;
    }

    td a {
        color: #2980b9;
        text-decoration: none;
        transition: color 0.3s;
    }

    td a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        table {
            font-size: 14px; /* Smaller font size for mobile devices */
        }
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
            <li>
                <a href="analytics.php">
                    <i class='bx bxs-doughnut-chart'></i>
                    <span class="text">Analytics</span>
                </a>
            </li>
            <li class="active">
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
        <div class="head-title">
        <div class="left">
            <h2 class="normal-weight"> </h2>
            <ul class="breadcrumb">
                <!-- Removed the breadcrumb -->
            </ul>
        </div>
    </div>
    <div class="table-data">
    <table>
        <thead>
            <tr>
                <th>Vaccine Name</th>
                <th>Details</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inventory as $item): ?>
            <tr>
                <td>
                    <a href="#" class="vaccine-link" data-name="<?php echo htmlspecialchars($item['vaccine_name']); ?>">
                        <?php echo htmlspecialchars($item['vaccine_name']); ?>
                    </a>
                </td>
                <td>
                    <?php
                    // Hardcoded vaccine details based on the vaccine name
                    switch ($item['vaccine_name']) {
                        case 'Bacille Calmette-Guérin vaccine (BCG)':
                            echo 'Used to prevent tuberculosis.';
                            break;
                        case 'Hepatitis B vaccine (HBV)':
                            echo 'Protects against Hepatitis B infection.';
                            break;
                        case 'DTwP-Hib-Hep B vaccine':
                            echo 'Combination vaccine for diphtheria, tetanus, pertussis, and Hib.';
                            break;
                        case 'Polio vaccine':
                            echo 'Prevents poliomyelitis.';
                            break;
                        case 'Pneumococcal conjugate vaccine (PCV)':
                            echo 'Protects against pneumococcal diseases.';
                            break;
                        case 'Measles-Mumps-Rubella vaccine (MMR)':
                            echo 'Combination vaccine for measles, mumps, and rubella.';
                            break;
                        case 'Tetanus-Diptheria vaccine (Td)':
                            echo 'Protects against tetanus and diphtheria.';
                            break;
                        case 'Human Papillomavirus vaccine (HPV)':
                            echo 'Protects against human papillomavirus.';
                            break;
                        default:
                            echo 'No details available.';
                    }
                    ?>
                </td>
                <td><?php echo htmlspecialchars($item['stock']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
        </main>
        <!-- MAIN -->
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
