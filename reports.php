<?php
include 'config.php'; // Include your database configuration file

// Function to fetch sales report for today
function getSalesReportToday($conn) {
    $sql = "SELECT SUM(total_price) AS total_sales FROM `order` WHERE DATE(date_added) = CURDATE() AND status = 'confirmed'";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total_sales'] ?? 0;
    } else {
        return 0;
    }
}

// Function to fetch sales report for yesterday
function getSalesReportYesterday($conn) {
    $sql = "SELECT SUM(total_price) AS total_sales FROM `order` WHERE DATE(date_added) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND status = 'confirmed'";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total_sales'] ?? 0;
    } else {
        return 0;
    }
}

// Function to fetch sales report for this year
function getSalesReportThisYear($conn) {
    $sql = "SELECT SUM(total_price) AS total_sales FROM `order` WHERE YEAR(date_added) = YEAR(CURDATE()) AND status = 'confirmed'";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total_sales'] ?? 0;
    } else {
        return 0;
    }
}

// Function to fetch sales report for last year
function getSalesReportLastYear($conn) {
    $sql = "SELECT SUM(total_price) AS total_sales FROM `order` WHERE YEAR(date_added) = YEAR(CURDATE()) - 1 AND status = 'confirmed'";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total_sales'] ?? 0;
    } else {
        return 0;
    }
}

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch sales reports
$salesToday = getSalesReportToday($conn);
$salesYesterday = getSalesReportYesterday($conn);
$salesThisYear = getSalesReportThisYear($conn);
$salesLastYear = getSalesReportLastYear($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Business Summary</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reset some default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        h2 {
            margin-bottom: 10px;
        }

        .report-section {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .sales-reports {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .report-item {
            flex: 1 1 calc(50% - 40px);
            text-align: center;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-width: 150px;
        }

        .report-item p {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-item span {
            font-size: 18px;
            color: #4CAF50;
        }

        @media (max-width: 600px) {
            .report-item {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Daily Business Summary</h1>
        <div class="report-section">
            <h2>Sales Reports</h2>
            <div class="sales-reports">
                <div class="report-item">
                    <p>Sales Today:</p>
                    <span>$<?php echo number_format($salesToday, 2); ?></span>
                </div>
                <div class="report-item">
                    <p>Sales Yesterday:</p>
                    <span>$<?php echo number_format($salesYesterday, 2); ?></span>
                </div>
                <div class="report-item">
                    <p>Sales This Year:</p>
                    <span>$<?php echo number_format($salesThisYear, 2); ?></span>
                </div>
                <div class="report-item">
                    <p>Sales Last Year:</p>
                    <span>$<?php echo number_format($salesLastYear, 2); ?></span>
                </div>
            </div>
        </div>
        <!-- Add more sections for other reports if needed -->
    </div>
</body>
</html>
