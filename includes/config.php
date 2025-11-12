<?php
// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "dsc_db1";

// Create database connection
$conn= mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Constants
define('SITE_NAME', 'Developer Student Club - Sanjivani University');
define('SITE_URL', 'http://localhost/dsc');

// Department list
$departments = [
    'CSE' => 'Computer Science Engineering',
    'CY' => 'Cyber Security',
    'AIML' => 'Artificial Intelligence & Machine Learning',
    'ALDS' => 'AI & Data Science'
];

// Year list
$years = [
    'FY' => 'First Year',
    'SY' => 'Second Year',
    'TY' => 'Third Year',
    'FINAL' => 'Final Year'
];

// Points system
$points = [
    'event_participation' => 5,
    'hackathon_participation' => 10,
    'competition_winner' => 20,
    'certification_basic' => 5,
    'certification_intermediate' => 10,
    'certification_advanced' => 15,
    'project_completion' => 15
];

// Function to sanitize user input
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check user role
function hasRole($role) {
    if(!isLoggedIn()) return false;
    return $_SESSION['user_role'] === $role;
}

// Function to redirect user
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to display alert messages
function setAlert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Function to display alert messages
function displayAlert() {
    if(isset($_SESSION['alert'])) {
        $type = $_SESSION['alert']['type'];
        $message = $_SESSION['alert']['message'];

        echo "<div class='alert alert-$type'>$message</div>";

        // Clear the alert
        unset($_SESSION['alert']);
    }
}
?>