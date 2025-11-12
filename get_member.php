<?php
session_start();
include 'includes/config.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $query = "SELECT * FROM team_members WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 1) {
        $member = mysqli_fetch_assoc($result);
        
        echo json_encode([
            'success' => true,
            'member' => $member
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Member not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Missing member ID'
    ]);
}
?>