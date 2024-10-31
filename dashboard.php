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
$sql = "SELECT barangay_id, initials, first_name, last_name FROM usertable WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $barangayId = $row['barangay_id'];
    $userInitials = $row['initials'];
    $userName = $row['first_name'] . ' ' . $row['last_name']; // Full name of the user
    
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

$conn->close();
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
    <title>ImmuniTrack - <?php echo htmlspecialchars($barangayName); ?> Dashboard</title>
    <style>
        /* Center the brand text */
        #sidebar .brand {
            text-align: center;
        }
        #sidebar .brand .text {
            display: inline-block;
        }

        /* Style adjustments */
        .head-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .head-title .left h1 {
            margin: 0;
        }
        .head-title .left p {
            margin: 10px 0;
            color: #666;
        }
        .breadcrumb {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            gap: 5px;
        }
        .breadcrumb li {
            display: inline;
        }
        .breadcrumb li a {
            text-decoration: none;
            color: #007bff;
        }
        .breadcrumb li i {
            color: #666;
        }    
        .long-box {
        background-color: white;
        width: 100%; /* Full width of the parent container */
        height: 120px; /* Adjust height for the long box */
        border-radius: 5px; /* Rounded corners */
        margin: 30px 0; /* Optional margin for spacing */
        display: flex; /* Use flexbox for centering */
        align-items: center; /* Center vertically */
        justify-content: left; /* Center horizontally */
        text-align: left; /* Center text */
        padding: 20px; /* Add some padding */
        }
        .text-black {
    color: black; /* Define the black color */
}

.text-blue {
    color: blue; /* Define the blue color */
}
.new-box {
    background-color: #ffffff; /* White background */
    border: 1px solid #ddd; /* Light gray border */
    border-radius: 5px; /* Rounded corners */
    margin-top: 20px; /* Space above the box */
    padding: 10px; /* Padding inside the box */
    display: inline-block; /* Fits the box to its content */
    max-width: 300px; /* Adjust maximum width to make it smaller */
    width: fit-content; /* Box fits content size */
}
.box-container {
    display: flex; /* Use flexbox for layout */
    gap: 70px; /* Space between the boxes */
    margin-top: 20px; /* Space above the boxes */
}

.new-box, .side-box, .third-box {
    background-color: #ffffff; /* White background */
    border: 1px solid #ddd; /* Light gray border */
    border-radius: 5px; /* Rounded corners */
    padding: 20px; /* Padding inside the box */
    margin-top: 20px; /* Space above the box */
    flex: 1; /* Make all boxes take equal width */
    flex-basis: 200px; /* Preferred width for boxes (adjust as needed) */
    max-width: 300px; /* Optional: maximum width for boxes */
    display: flex; /* Flexbox for content alignment */
    flex-direction: column; /* Column direction for text */
    justify-content: center; /* Center content vertically */
    align-items: center; /* Center content horizontally */
    text-align: left; /* Center text alignment */
}

.side-box h3 i {
    font-size: 25px; /* Adjust size if needed */
    margin-right: 10px; /* Space between icon and text */
    vertical-align: middle; /* Align icon with the text */
}

/* Icon Styling for Both Boxes */
.third-box h3 i, .second-box h3 i {
    font-size: 25px; /* Adjust size if needed */
    margin-right: 10px; /* Space between icon and text */
    vertical-align: middle; /* Align icon with the text */
}
/* Feedback Button Styling */
#send-feedback-btn {
    margin-top: 10px;
    padding: 15px 15px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s;
}

#send-feedback-btn:hover {
    background-color: white;
    color: #007bff;
}


.modal {
    display: none; /* Hide modal by default */
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    text-align: center;
}

.close-btn {
    float: right;
    font-size: 24px;
    cursor: pointer;
}

.rating-stars i {
    font-size: 24px;
    color: #ccc;
    cursor: pointer;
}

.rating-stars i.active {
    color: #FF6600;
}

/* Feedback Form Styles */
.feedback-form {
    display: flex;
    flex-direction: column;
    gap: 10px; /* Adds space between form elements */
}

.feedback-form select,
.feedback-form textarea,
.feedback-form button {
    width: 100%; /* Full width for each element */
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.feedback-form button {
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.feedback-form button:hover {
    background-color: #0056b3;
}








    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
    <a href="#" class="brand">
    <i class='bx bxs-injection'></i> <!-- Static icon -->
    <span class="text pulse-text">ImmuniTrack</span> <!-- Pulsing text -->
</a>

        <ul class="side-menu top">
            <li class="active">
                <a href="dashboard.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="calendar.php"> <!-- New link for Calendar -->
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

       <!-- MAIN CONTENT -->
<main>
    <div class="left">
        <div class="long-box">
            <p class="greeting-text" style="margin: 0;"> 
                Welcome, <strong class="text-black"><?php echo htmlspecialchars($barangayName); ?></strong> Dashboard!<br><br>
                Stay informed about upcoming events, access health tips, and collaborate with fellow health workers to make a positive impact in our community. 
            </p>
        </div>
    </div>

    <div class="box-container">
    <!-- Box 1: Important Updates -->
    <div class="side-box new-box" style="display: flex; align-items: center; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #ffffff; width: fit-content;">
        <div>
        <h3 style="margin: 0; color: #333;">
    <i class='bx bxs-bell-ring pulse-icon' style="font-size: 25px; color: #FF6F61;"></i> <!-- Icon color changed to light red -->
    Important Updates
</h3>

            <p style="margin: 10px 0; color: #555;">Check back regularly for updates!</p>
        </div>
    </div>
    
    <!-- Box 2: Upcoming Events -->
    <div class="side-box second-box" style="display: flex; align-items: center; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #ffffff; width: fit-content;">
        <div>
        <h3 style="margin: 0; color: #333;">
    <i class='bx bxs-calendar-event pulse-icon' style="color: #77CDFF;"></i> <!-- Icon color changed to light blue -->
    Upcoming Events
</h3>

            <p style="margin: 10px 0; color: #555;">Stay tuned for dates and more information.</p>
        </div>
    </div>

    <!-- Box 3: Health Tips -->
    <div class="side-box third-box" style="display: flex; align-items: center; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #ffffff; width: fit-content;">
        <div>
            <h3 style="margin: 0; color: #333;">
                <i class='bx bxs-first-aid pulse-icon' style="color: #72BF78;"></i> <!-- Pulsing icon -->
                Health Tips
            </h3>
            <p style="margin: 10px 0; color: #555;">Discover valuable health tips for your community!</p>
        </div>
    </div>
</div>


<div style="background-color: #ffffff; padding: 20px; border-radius: 5px; margin-top: 50px;">

    <!-- Emergency Hotlines Title with Icon -->
    <h2 style="text-align: left; color: black; margin-top: 0; font-size: 20px;">
        <i class='bx bxs-phone' style="font-size: 24px; color: red; vertical-align: middle; margin-right: 8px;"></i> <!-- Added margin-right -->
        Emergency Hotlines
    </h2>

    <!-- Four Small Clickable Boxes in each row -->
    <div class="box-container" style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 10px;">
        <!-- Small Box 1 - Municipal Disaster Risk Reduction -->
        <a href="tel:09214141927" style="flex-basis: 23%; text-decoration: none;">
            <div class="small-box" style="padding: 15px;">
                <h4 style="margin: 0; color: #333;">Municipal Disaster Risk Reduction</h4>
                <p style="margin: 0; color: #555;">Call 0921 414 1927 or 0927 490 1545 for disaster assistance.</p>
            </div>
        </a>

        <!-- Small Box 2 - Fire Department -->
        <a href="tel:09065648793" style="flex-basis: 23%; text-decoration: none;">
            <div class="small-box" style="padding: 15px;">
                <h4 style="margin: 0; color: #333;">Fire Department</h4>
                <p style="margin: 0; color: #555;">Call 0906 564 8793 or 0907 691 6182 for fire emergencies.</p>
            </div>
        </a>

        <!-- Small Box 3 - Municipal Social Welfare -->
        <a href="tel:09074803972" style="flex-basis: 23%; text-decoration: none;">
            <div class="small-box" style="padding: 15px;">
                <h4 style="margin: 0; color: #333;">Social Welfare Office</h4>
                <p style="margin: 0; color: #555;">Contact 0907 480 3972 or 0936 573 8653 for social services.</p>
            </div>
        </a>

        <!-- Small Box 4 - Mayor's Office -->
        <a href="tel:09973921090" style="flex-basis: 23%; text-decoration: none;">
            <div class="small-box" style="padding: 15px;">
                <h4 style="margin: 0; color: #333;">Mayor's Office</h4>
                <p style="margin: 0; color: #555;">Call 0997 392 1090 or 0948 970 7469 for direct assistance.</p>
            </div>
        </a>

        <!-- Small Box 5 - Daraga Municipal Police Station -->
        <a href="tel:09185938337" style="flex-basis: 23%; text-decoration: none;">
            <div class="small-box" style="padding: 15px;">
                <h4 style="margin: 0; color: #333;">Daraga Municipal Police Station</h4>
                <p style="margin: 0; color: #555;">Call 0918 593 8337 or 0998 598 3937 for police assistance.</p>
            </div>
        </a>

        <!-- Small Box 6 - Municipal Health Office -->
        <a href="tel:0528260405" style="flex-basis: 23%; text-decoration: none;">
            <div class="small-box" style="padding: 15px;">
                <h4 style="margin: 0; color: #333;">Municipal Health Office</h4>
                <p style="margin: 0; color: #555;">Call 052 826 0405 for health concerns.</p>
            </div>
        </a>

        <!-- Small Box 7 - Daraga Public Safety Office -->
        <a href="tel:09178820392" style="flex-basis: 23%; text-decoration: none;">
            <div class="small-box" style="padding: 15px;">
                <h4 style="margin: 0; color: #333;">Daraga Public Safety Office</h4>
                <p style="margin: 0; color: #555;">Call 0917 882 0392 or 0935 583 8331 for safety concerns.</p>
            </div>
        </a>

        <!-- Small Box 8 - Daraga Public Information Office -->
        <a href="tel:09178820434" style="flex-basis: 23%; text-decoration: none;">
            <div class="small-box" style="padding: 15px;">
                <h4 style="margin: 0; color: #333;">Daraga Public Information Office</h4>
                <p style="margin: 0; color: #555;">Call 0917 882 0434 or 0912 777 4700 for information assistance.</p>
            </div>
        </a>
    </div>
</div>
<!-- Container for the Boxes -->
<div style="display: flex; justify-content: flex-start; gap: 200px;">

<!-- Long Box 1 -->
<div style="padding: 15px; border-radius: 5px; width: 40%; max-width: 400px; margin: 30px 0;"> <!-- Added margin -->
    <h3 style="margin-top: 0; color: #333; margin-bottom: 15px; font-size: 23px; margin-top: 10px;">Connect with Us on Facebook</h3>
    <p style="color: #555; font-size: 14px;  margin-bottom: 15px;">Stay informed about the latest announcements by checking out our official page for Rural Health Daraga.</p>
    <p style="color: #555; font-size: 14px;">
        <a href="https://web.facebook.com/profile.php?id=61552214442726" style="color: #007bff; text-decoration: none;">Click here to visit our page!</a>
    </p>
</div>






<!-- Long Box 2 -->
<div style="padding: 15px; border-radius: 5px; width: 40%; max-width: 400px; margin-top: 20px;">
    <h3 style="margin-top: 0; color: #333; font-size: 23px;">Need Feedback?</h3>
    
    <p style="color: #555; margin-bottom: 15px; font-size: 14px; margin-top: 10px;">
    Let us know if you encounter any problems or have concerns regarding the Immunization Tracking System.    </p>
    
<!-- Feedback Button -->
<button id="send-feedback-btn">
    <i class='bx bxs-star' style="margin-right: 5px; color: #FF6600;"></i> Send Feedback
</button>

<!-- Feedback Modal -->
<div id="feedback-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" id="close-modal">&times;</span>
        <div class="modal-header">Send Feedback</div>
        <div class="rating-stars" id="rating-stars">
            <i class='bx bxs-star'></i>
            <i class='bx bxs-star'></i>
            <i class='bx bxs-star'></i>
            <i class='bx bxs-star'></i>
            <i class='bx bxs-star'></i>
        </div>
        <p>If you have further suggestions, don't hesitate to tell us!</p>
        <form class="feedback-form">
            <select required>
                <option value="">Other</option>
                <option value="issue">Issue</option>
                <option value="suggestion">Suggestion</option>
            </select>
            <textarea placeholder="Describe your issue or idea..." required></textarea>
            <button type="submit">Submit</button>
        </form>
    </div>
</div>


</main>







        <!-- MAIN CONTENT -->
    </section>
    <!-- CONTENT -->
    <script>
        // JavaScript to handle modal display and star rating

// Elements
const feedbackBtn = document.getElementById('send-feedback-btn');
const modal = document.getElementById('feedback-modal');
const closeModal = document.getElementById('close-modal');
const stars = document.querySelectorAll('#rating-stars i');

// Open the modal on button click
feedbackBtn.addEventListener('click', () => {
    modal.style.display = 'flex';
});

// Close the modal when clicking the "x" button
closeModal.addEventListener('click', () => {
    modal.style.display = 'none';
});

// Close the modal when clicking outside the modal content
window.addEventListener('click', (event) => {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
});

// Star rating logic
stars.forEach((star, index) => {
    star.addEventListener('click', () => {
        stars.forEach((s, i) => {
            s.classList.toggle('active', i <= index);
        });
    });
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
