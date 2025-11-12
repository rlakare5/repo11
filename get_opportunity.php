
<?php
session_start();
include 'includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in and has appropriate role
if(!isLoggedIn() || $_SESSION['user_role'] === 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$id = (int)$_GET['id'];
$query = "SELECT * FROM opportunities WHERE id = $id";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) === 1) {
    $opportunity = mysqli_fetch_assoc($result);
    echo json_encode(['success' => true, 'opportunity' => $opportunity]);
} else {
    echo json_encode(['success' => false, 'message' => 'Opportunity not found']);
}
?>
