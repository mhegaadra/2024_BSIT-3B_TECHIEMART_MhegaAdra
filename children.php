<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login-user.php');
    exit();
}

// Database connection settings
$host = 'localhost';        // Database host
$db = 'immuni_track';       // The database name
$user = 'root';             // Your database username
$pass = '12345';            // Your database password

try {
    // Create a PDO instance (connect to the database)
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    
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
$query = "
    SELECT 
        u.email, 
        b.barangay_id
    FROM 
        usertable u
    JOIN 
        barangay b ON u.id = b.user_id
    WHERE 
        u.email = ?
";
$stmt = $pdo->prepare($query);
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Extract the initials from the email
    $initial = strtoupper(substr($user['email'], 0, 1));

    // Generate the profile image URL with the initial
    $profileImageUrl = "https://ui-avatars.com/api/?name={$initial}&background=random&color=fff";

    // Get the barangay ID for filtering children profiles
    $barangayId = $user['barangay_id'];
} else {
    // If the user is not found, redirect to the login page
    header('Location: login-user.php');
    exit();
}

// Fetch children profiles along with parent details and registration date from the database, filtered by barangay
$query = "
    SELECT 
        c.id, 
        c.first_name, 
        c.last_name, 
        c.date_of_birth, 
        p.parent_name, 
        p.address,
        c.registration_date,
        c.age_of_registration  -- Fetching the age of registration
    FROM 
        children c
    JOIN 
        parents p ON c.parent_id = p.id
    JOIN 
        barangay b ON b.barangay_id = c.barangay_id
    WHERE 
        b.barangay_id = ?
";
$stmt = $pdo->prepare($query);
$stmt->execute([$barangayId]);
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>ImmuniTrack - Children Profile</title>
    <style>
        .profile-boxes {
            display: grid;
            grid-template-columns: repeat(4, minmax(200px, 1fr)); /* Four columns */
            gap: 20px; /* Increased space between boxes */
            justify-content: center; /* Center the grid */
            margin: 20px auto; /* Added margin above the boxes */
            padding: 5px; /* Padding around the grid */
            max-width: 1200px; /* Maximum width to keep the grid manageable */
        }

        .profile-box {
            padding: 10px; /* Increase padding for a more spacious feel */
            background: #fff; /* White background for each box */
            border-radius: 8px; /* Rounded corners for smoother design */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Soft shadow for a lifted effect */
            text-align: center; /* Center the text inside each box */
            cursor: pointer; /* Pointer cursor on hover */
            transition: all 0.3s ease; /* Smooth hover transitions */
        }

        .profile-box:hover {
            background: #f0f0f0; /* Slightly darker background on hover */
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2); /* Stronger shadow on hover */
            transform: scale(1.05); /* Slight scaling effect on hover */
        }

        .profile-box img {
            width: 60px; /* Width for the profile image */
            height: 60px; /* Height for the profile image */
            object-fit: cover; /* Ensures the image covers the area properly */
            border-radius: 50%; /* Makes the image circular */
            margin-bottom: 10px; /* Space below the image */
        }

        .profile-box .name {
            font-size: 16px; /* Font size for the name */
            font-weight: 600; /* Bold text for the name */
            margin-bottom: 8px; /* Space below the name */
        }

        .profile-box .info {
            font-size: 14px; /* Font size for the info text */
            color: #666; /* Grey color for the info text */
        }

        .profile-box .info.address {
            font-size: 14px; /* Font size for the address */
            color: #333; /* Darker color for the address */
        }

        .add-child-btn {
            background-color: #4CAF50;
            color: white;
            font-size: 16px; /* Adjusted size for the button text */
            border: none;
            border-radius: 5px; /* Slightly rounded corners */
            padding: 10px 15px; /* Padding for the button */
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            position: fixed; /* Fix position at the bottom right */
            bottom: 20px;
            right: 20px;
        }

        .add-child-btn:hover {
            background-color: green; /* Darker shade on hover */
            transform: scale(1.05); /* Slightly enlarges the button on hover */

        }

        .search-form {
            display: flex;
            align-items: center;
            margin-top: 20px; /* Space above the search bar */
        }
        
        .search-input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 1px;
        }
        
        .search-btn {
            padding: 10px;
            border: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-btn:hover {
        background-color: green; /* Darker green on hover */
        transform: scale(1.05); /* Slightly enlarges the button on hover */
        }
        .search-container {
        margin-bottom: 20px; /* Space below the search bar */
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
            <li>
                <a href="inventory.php">
                    <i class='bx bxs-package'></i>
                    <span class="text">Inventory</span>
                </a>
            </li>
            <li class="active">
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
        <h1></h1>
    </div>
    <form action="" method="GET" class="search-form">
    <div class="search-bar">
    <input type="text" placeholder="Search" class="search-input" required>
    <button class="search-btn">
        <i class="bx bx-search"></i>
    </button>
</div>

    </form>
</div>


            <!-- PROFILE BOXES -->
            <div class="profile-boxes">
                <?php foreach ($children as $child): ?>
                    <?php
                    // Generate initials for the profile image
                    $initials = strtoupper(substr($child['first_name'], 0, 1) . substr($child['last_name'], 0, 1));
                    $profileImageUrl = "https://ui-avatars.com/api/?name={$initials}&background=random&color=fff";
                    ?>
                    <div class="profile-box" onclick="window.location.href='children-data.php?id=<?php echo $child['id']; ?>'">
                        <img src="<?php echo htmlspecialchars($profileImageUrl); ?>" alt="Profile Image">
                        <div class="name"><?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?></div>
                        <div class="info">Parent: <?php echo htmlspecialchars($child['parent_name']); ?></div>
                        <div class="info address">Address: <?php echo htmlspecialchars($child['address']); ?></div>
                        <div class="info">
                            Age of Registration: 
                            <?php echo htmlspecialchars($child['age_of_registration']); ?> 
                            <?php echo ($child['age_of_registration'] == 1) ? 'month' : 'months'; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- PROFILE BOXES -->

            <!-- Add Child Box -->
            <button class="add-child-btn" onclick="window.location.href='add-child.php'">
                <i class="bx bxs-plus-circle" style="font-size: 15px; margin-right: 10px;"></i>
                Add New Child
            </button>
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
