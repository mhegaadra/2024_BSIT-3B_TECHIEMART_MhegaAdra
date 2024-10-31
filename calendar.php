<?php 
session_start(); // Start the session

// Database connection settings
$host = 'localhost';        // Database host
$db = 'immuni_track';       // Database name
$user = 'root';             // Database username
$pass = '12345';            // Database password

// Create database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login-user.php");
    exit();
}

// Get the user's email and fetch the user's barangay ID and initials from the database
$email = $_SESSION['email'];
$sql = "SELECT barangay_id, initials FROM usertable WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $barangayId = $row['barangay_id'];
    $userInitials = $row['initials'];
    
    // Use only the first letter of the initials for the profile image
    $profileInitial = substr($userInitials, 0, 1);
} else {
    echo "User not found.";
    exit();
}

// Fetch barangay details
$sql = "SELECT barangay_name FROM barangay WHERE barangay_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $barangayId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $barangayName = $row['barangay_name'];
} else {
    $barangayName = "Unknown Barangay";
}

$stmt->close();

// Generate profile image URL from the first letter of the initials
$profileImageUrl = "https://ui-avatars.com/api/?name=" . urlencode($profileInitial) . "&background=random&color=fff";

$sql = "SELECT vr.vaccine_name, vr.next_vaccine_name, vr.next_vaccination_date 
        FROM vaccination_records vr
        JOIN children c ON vr.child_id = c.id
        WHERE c.barangay_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $barangayId);
$stmt->execute();
$result = $stmt->get_result();

$vaccinationSchedules = [];
while ($row = $result->fetch_assoc()) {
    $vaccinationSchedules[] = $row;
}

// Get today's date
$today = new DateTime();

// Filter vaccination schedules for upcoming and missed dates
$filteredVaccinations = array_filter($vaccinationSchedules, function($schedule) use ($today) {
    $vaccinationDate = !empty($schedule['next_vaccination_date']) ? new DateTime($schedule['next_vaccination_date']) : null;

    if ($vaccinationDate) {
        // Track upcoming vaccinations (current or future dates)
        if ($vaccinationDate >= $today) {
            return 'upcoming';
        }
        // Track missed vaccinations (past dates)
        elseif ($vaccinationDate < $today) {
            return 'missed';
        }
    }
    return false;
});

// Separate missed and upcoming vaccinations
$upcomingVaccinations = [];
$missedVaccinations = [];

foreach ($filteredVaccinations as $schedule) {
    $vaccinationDate = new DateTime($schedule['next_vaccination_date']);
    
    if ($vaccinationDate >= $today) {
        $upcomingVaccinations[] = $schedule;
    } else {
        $missedVaccinations[] = $schedule;
    }
}

// Merge existing vaccination schedules
$allVaccinations = $filteredVaccinations; // Just the filtered vaccinations now




$conn->close();
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
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.css' rel='stylesheet' />
    <title>ImmuniTrack - Calendar</title>
    <style>
        /* Center the brand text */
#sidebar .brand {
    text-align: center;
}

#sidebar .brand .text {
    display: inline-block;
}

.calendar-container {
    display: flex; /* Enable flexbox for centering */
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically if height allows */
    max-width: 100%; /* Ensure it takes the full width of the parent */
    margin: 20px auto; /* Center the calendar container */
}

#calendar {
    width: 100%;
    max-width: 600px;
    min-width: 810px;
    background-color: #ffffff;
    border-radius: 5px;
    padding: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    height: 550px;
    overflow: hidden;
}

/* Day grid styling */
.fc-daygrid-day {
    border: 1px solid #ddd;
    background-color: #fafafa;
}

.fc-day-today {
    background-color: #B7B7B7 !important;
}

/* Vaccination containers */
.vaccination-container {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 30px;
    margin-bottom: 70px;

}

.upcoming-vaccinations, .missed-vaccinations {
    width: 100%;
    max-width: 400px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    max-height: 200px;
    overflow-y: auto;
}

.upcoming-vaccinations ul, .missed-vaccinations ul {
    list-style-type: none;
    padding: 1px;
}

.upcoming-vaccinations ul li, .missed-vaccinations ul li {
    background-color: #ffffff;
    padding: 5px;
    margin-bottom: 8px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

h2 {
    font-size: 18px;
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
            <li class="active">
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

        <main>
            <div class="vaccination-container">
            <div class="upcoming-vaccinations">
            <h2>Upcoming Vaccinations</h2>
            <ul>
                <?php if (!empty($allVaccinations)) : ?>
                    <?php foreach ($allVaccinations as $vaccination) : ?>
                        <li><?php echo htmlspecialchars($vaccination['vaccine_name']) . " - " . htmlspecialchars($vaccination['next_vaccination_date']); ?></li>
                    <?php endforeach; ?>
                <?php else : ?>
                    <li>No upcoming vaccinations scheduled.</li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="missed-vaccinations">
            <h2>Missed Vaccinations</h2>
            <ul>
                <li>No missed vaccinations recorded.</li>
            </ul>
        </div>
    </div>
            <div class="calendar-container">
                <div id="calendar"></div>
            </div>
        </main>
    </section>

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    <?php foreach ($allVaccinations as $vaccination) : ?>
                        {
                            title: '<?php echo htmlspecialchars($vaccination['vaccine_name']); ?>',
                            start: '<?php echo htmlspecialchars($vaccination['next_vaccination_date']); ?>',
                            allDay: true
                        },
                    <?php endforeach; ?>
                ]
            });
            calendar.render();
        });
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
