<?php
// Database connection
$servername = "localhost"; // Change if necessary
$username = "root"; // Change to your database username
$password = "12345"; // Change to your database password
$dbname = "immuni_track"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch child details and vaccination records
$sql = "
    SELECT 
        p.id AS parent_id,
        p.parent_name,
        p.address,
        p.phone_number,
        c.id AS child_id,
        c.first_name,
        c.last_name,
        v.vaccine_name,
        v.vaccination_date
    FROM 
        parents AS p
    JOIN 
        children AS c ON p.id = c.parent_id
    LEFT JOIN 
        vaccination_records AS v ON c.id = v.child_id
    WHERE 
        p.id = ?"; // Replace this with a parameter if needed

$stmt = $conn->prepare($sql);

// Replace 1 with the actual parent ID you want to fetch for testing
$parent_id = 1; 
$stmt->bind_param("i", $parent_id);
$stmt->execute();

$result = $stmt->get_result();

$children = array();
while ($row = $result->fetch_assoc()) {
    $children[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($children);
?>
