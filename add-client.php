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
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['full_name'], $_POST['barangay_name'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = password_hash("ImmuniTrack2024", PASSWORD_DEFAULT);

    // Split the full name into first and last names
    $full_name = trim($_POST['full_name']);
    $name_parts = explode(' ', $full_name, 2);
    $first_name = filter_var($name_parts[0], FILTER_SANITIZE_STRING);
    $last_name = isset($name_parts[1]) ? filter_var($name_parts[1], FILTER_SANITIZE_STRING) : '';

    $barangay_name = filter_var(trim($_POST['barangay_name']), FILTER_SANITIZE_STRING);

    // Create initials from first and last names
    $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
    $role = 'user'; // Set role as 'user'

    // Check if the email already exists
    $emailCheckStmt = $pdo->prepare("SELECT * FROM usertable WHERE email = ?");
    $emailCheckStmt->execute([$email]);
    if ($emailCheckStmt->fetch()) {
        $_SESSION['message'] = 'Email already exists. Please use a different email.';
        header('Location: admin-barangay.php');
        exit();
    }

    // Retrieve barangay_id or insert if not found
    $barangay_stmt = $pdo->prepare("SELECT barangay_id FROM barangay WHERE barangay_name = ?");
    $barangay_stmt->execute([$barangay_name]);
    $barangay = $barangay_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$barangay) {
        // Insert barangay if it doesn't exist
        $insertBarangayStmt = $pdo->prepare("INSERT INTO barangay (barangay_name) VALUES (?)");
        $insertBarangayStmt->execute([$barangay_name]);
        $barangay_id = $pdo->lastInsertId();
    } else {
        $barangay_id = $barangay['barangay_id'];
    }

    try {
        // Insert into usertable
        $insertUserQuery = "INSERT INTO usertable (first_name, last_name, email, barangay_id, password, status, role, initials, last_active) 
                            VALUES (:first_name, :last_name, :email, :barangay_id, :password, 'active', :role, :initials, NOW())";

        $userStmt = $pdo->prepare($insertUserQuery);
        $userStmt->bindParam(':first_name', $first_name);
        $userStmt->bindParam(':last_name', $last_name);
        $userStmt->bindParam(':email', $email);
        $userStmt->bindParam(':barangay_id', $barangay_id); // Ensure barangay_id is set
        $userStmt->bindParam(':password', $password);
        $userStmt->bindParam(':role', $role);
        $userStmt->bindParam(':initials', $initials);

        if ($userStmt->execute()) {
            $user_id = $pdo->lastInsertId(); // Get the ID of the newly inserted user

            // Update barangay with user_id
            $updateBarangayStmt = $pdo->prepare("UPDATE barangay SET user_id = ? WHERE barangay_id = ?");
            $updateBarangayStmt->execute([$user_id, $barangay_id]);

            // Automatically add vaccines to the inventory for this barangay if not already present
            $vaccines = [
                ['id' => 1, 'name' => 'Bacille Calmette-Guérin vaccine (BCG)'],
                ['id' => 2, 'name' => 'Hepatitis B vaccine (HBV)'],
                ['id' => 3, 'name' => 'DTwP-Hib-Hep B vaccine'],
                ['id' => 4, 'name' => 'Polio vaccine'],
                ['id' => 5, 'name' => 'Pneumococcal conjugate vaccine (PCV)'],
                ['id' => 6, 'name' => 'Measles-Mumps-Rubella vaccine (MMR)'],
                ['id' => 7, 'name' => 'Tetanus-Diptheria vaccine (Td)'],
                ['id' => 8, 'name' => 'Human Papillomavirus vaccine (HPV)']
            ];

                foreach ($vaccines as $vaccine) {
                    // Check if the vaccine already exists for this barangay
                    $checkVaccineStmt = $pdo->prepare("SELECT 1 FROM inventory WHERE vaccine_id = ? AND barangay_id = ?");
                    $checkVaccineStmt->execute([$vaccine['id'], $barangay_id]);
                    
                    if (!$checkVaccineStmt->fetch()) {
                        // Insert only if the vaccine is not already present
                        $vaccineQuery = "INSERT INTO inventory (vaccine_id, barangay_id, vaccine_name, stock) VALUES (?, ?, ?, 0)";
                        $inventory_stmt = $pdo->prepare($vaccineQuery);
                        $inventory_stmt->execute([$vaccine['id'], $barangay_id, $vaccine['name']]);
                    }
                }


            // Send welcome email
            $subject = "Welcome to ImmuniTrack!";
            $message = "Hello $first_name,\n\nThank you for joining ImmuniTrack!\n\nBest regards,\nImmuniTrack Team";
            $headers = "From: no-reply@immunitrack.com";
            mail($email, $subject, $message, $headers);

            $_SESSION['message'] = "Client added successfully!";
            header('Location: admin-barangay.php');
            exit();
        } else {
            error_log('Error executing user insert statement: ' . implode(", ", $userStmt->errorInfo()));
            $_SESSION['message'] = 'Error adding client. Please try again later.';
        }
    } catch (PDOException $e) {
        error_log('Error adding client: ' . $e->getMessage());
        $_SESSION['message'] = 'Error adding client. Please try again later.';
    }
}

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
    <title>Add Client - ImmuniTrack</title>
    <style>
        .form-container {
            max-width: 400px;
            margin: auto;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        .form-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: normal;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .form-container button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .password-container {
            position: relative; /* Position for absolute elements */
        }
        .toggle-password {
            position: absolute;
            right: 20px; /* Positioning the icon */
            top: 37%; /* Centering vertically */
            transform: translateY(-50%); /* Adjust vertical alignment */
            cursor: pointer; /* Pointer cursor for clickable icon */
            color: #007bff; /* Color for the icon */
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
            <span id="date_now"><?php echo $currentDate; ?></span>
            <span id="current-time" class="ps-2 text-muted"></span>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="form-container">
            <form method="POST" action="">
    <label for="full_name">Full Name:</label>
    <input type="text" name="full_name" required>

    <label for="barangay_name">Barangay Name:</label>
    <input type="text" name="barangay_name" required>

    <label for="email">Email:</label>
    <input type="email" name="email" required>
    
    <label for="password">Password:</label>
    <div class="password-container">
        <input type="password" id="password" name="password" value="ImmuniTrack2024" required readonly>
        <i class='bx bx-show toggle-password' id="togglePassword"></i>
    </div>
    <button type="submit">Add Client</button>
</form>



            </div>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script>
        // Toggle password visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            // Toggle the icon class
            this.classList.toggle('bx-show');
            this.classList.toggle('bx-hide');
        });

        // Display current time
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
