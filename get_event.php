
<?php
session_start();
include 'includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in and has admin role
if(!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$id = (int)$_GET['id'];
$query = "SELECT * FROM events WHERE id = $id";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) === 1) {
    $event = mysqli_fetch_assoc($result);
    echo json_encode(['success' => true, 'event' => $event]);
} else {
    echo json_encode(['success' => false, 'message' => 'Event not found']);
}
?>
