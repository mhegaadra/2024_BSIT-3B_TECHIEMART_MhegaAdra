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
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}

// Get logged-in user's email
$userEmail = $_SESSION['email'];

// Retrieve the user's associated barangay
$query = "SELECT barangay_id FROM barangay WHERE user_id = (SELECT id FROM usertable WHERE email = ?)";
$stmt = $pdo->prepare($query);
$stmt->execute([$userEmail]);
$barangay = $stmt->fetch(PDO::FETCH_ASSOC);
$barangayId = $barangay['barangay_id'] ?? null; // Use null coalescing operator for safety

// Handle form submission
$successMessage = $errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize form inputs
    $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $ageOfRegistration = filter_input(INPUT_POST, 'age_of_registration', FILTER_SANITIZE_STRING);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
    $parentName = filter_input(INPUT_POST, 'parent_name', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $phoneNumber = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $dateOfBirth = filter_input(INPUT_POST, 'date_of_birth', FILTER_SANITIZE_STRING);

    // Check if form data is complete
    if (!$firstName || !$lastName || !$ageOfRegistration || !$gender || !$parentName || !$address || !$phoneNumber || !$dateOfBirth) {
        $errorMessage = "Please fill out all fields.";
    } else {
        // Check if the parent already exists
        $query = "SELECT id FROM parents WHERE parent_name = ? AND address = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$parentName, $address]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        // If parent does not exist, insert into parents table
        if (!$parent) {
            $insertParentQuery = "INSERT INTO parents (parent_name, address, phone_number) VALUES (?, ?, ?)";
            $insertParentStmt = $pdo->prepare($insertParentQuery);
            $insertParentStmt->execute([$parentName, $address, $phoneNumber]);
            $parentId = $pdo->lastInsertId(); // Get the last inserted parent's ID
        } else {
            $parentId = $parent['id']; // Use existing parent's ID
        }

        // Insert new child record into the database, including date of birth
        $query = "
            INSERT INTO children (first_name, last_name, date_of_birth, age_of_registration, gender, parent_id, barangay_id, registration_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute([$firstName, $lastName, $dateOfBirth, $ageOfRegistration, $gender, $parentId, $barangayId]);

            // Check if data was inserted successfully
            if ($stmt->rowCount() > 0) {
                // Send welcome message using Infobip API
                sendWelcomeMessage($phoneNumber, $parentName, $firstName); // Updated call with parent and child name

                $successMessage = "Child added successfully and a welcome message has been sent!";
                header('Location: children.php'); // Redirect after successful insertion
                exit();
            } else {
                $errorMessage = "Failed to add child. Please try again.";
            }
        } catch (PDOException $e) {
            $errorMessage = 'Database error: ' . $e->getMessage();
        }
    }
}
// Function to send SMS using Infobip API
function sendWelcomeMessage($phoneNumber, $parentName, $childFirstName) {
    $url = 'https://e51xeq.api.infobip.com/sms/2/text/advanced'; // Infobip API endpoint
    $apiKey = 'd22ebfa85a991b5facafe4166570de5a-5951565d-b0cc-4f36-a488-e16d5ca72eb0'; // Replace with your actual API key

    $data = [
        'messages' => [
            [
                'from' => 'ImmuniTrack',
                'destinations' => [
                    ['to' => $phoneNumber]
                ],
                'text' => "Hello $parentName, welcome to ImmuniTrack! Your child $childFirstName has been successfully registered."
            ]
        ]
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n" .
                         "Authorization: App $apiKey\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        // Handle errors if needed
        error_log("Failed to send SMS to $phoneNumber");
    }
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
    <title>ImmuniTrack - Add Child</title>
    <style>
.small-form {
    max-width: 800px; /* Increased width for two columns */
    margin: 30px auto;
    padding: 20px;
    border-radius: 5px;
    background-color: white; /* Set to white background */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    position: relative;
}

.form-row {
    display: flex;
    flex-wrap: wrap; /* Allows the columns to stack on smaller screens */
    margin: -10px; /* Remove space between columns */
}

.form-column {
    flex: 1; /* Make columns take equal width */
    padding: 10px; /* Add space inside columns */
    min-width: 300px; /* Minimum width for each column */
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
}

.form-group input, .form-group select {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
    border: 1px solid #ccc; /* Light border for inputs */
    border-radius: 4px; /* Rounded edges */
}

.form-group button {
    padding: 10px 20px;
    background-color: #4CAF50; /* Button color */
    color: #fff;
    border: none;
    border-radius: 5px; /* Box-like edges (slightly rounded) */
    cursor: pointer;
    font-size: 16px;
}

.alert {
    margin-bottom: 20px;
    padding: 15px;
    border-radius: 5px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
}

button[type="submit"], .add-btn {
    background-color: #4CAF50; /* Button color */
    color: white;
    border: none;
    border-radius: 5px; /* Box-like edges (slightly rounded) */
    padding: 11px 40px; /* Larger padding for a box feel */
    cursor: pointer;
    font-size: 17px; /* Slightly larger font size */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Box shadow for depth */
    position: fixed; /* Fixes the button to the screen */
    bottom: 40px; /* Distance from the bottom of the screen */
    right: 20px; /* Distance from the right of the screen */
    transition: background-color 0.3s, transform 0.3s, box-shadow 0.3s; /* Smooth transition effects */
}

button[type="submit"]:hover, .add-btn:hover {
    background-color: #45a049; /* Darken on hover */
    transform: scale(1.05); /* Slightly enlarges the button on hover */
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.25); /* Increase shadow on hover */
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
        <div class="small-form">
    <form action="add-child.php" method="post">
        <div class="form-row">
            <div class="form-column">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required="">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required="">
                </div>
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth:</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required="">
                </div>
                <div class="form-group">
                    <label for="age_of_registration">Age of Registration (in month/s):</label>
                    <input type="number" id="age_of_registration" name="age_of_registration" required="">
                </div>
            </div>
            <div class="form-column">
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required="">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="parent_name">Parent Name:</label>
                    <input type="text" id="parent_name" name="parent_name" required="">
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" required="">
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number:</label>
                    <input type="text" id="phone_number" name="phone_number" required="">
                </div>
            </div>
        </div>
        <div class="form-group" style="display: flex; justify-content: center; margin-top: 20px;">
    <button type="submit" class="add-btn">
        <i class="bx bxs-plus-circle" style="font-size: 15px; margin-right: 10px;"></i>
        Add
    </button>
</div>

    </form>
</div>

        </main>
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
    </script></body>
</html>
