<?php
session_start();
include 'includes/config.php';

// Check if user is logged in
if(!isLoggedIn()) {
    redirect('login.php');
}

$student_id = $_SESSION['user_id'];
$department = $_SESSION['department'];
$year = $_SESSION['year'];

// Mark notification as read
if(isset($_GET['read']) && is_numeric($_GET['read'])) {
    $notification_id = (int)$_GET['read'];
    
    $query = "UPDATE student_notifications 
              SET is_read = 1, read_at = NOW() 
              WHERE notification_id = $notification_id AND student_id = $student_id";
    mysqli_query($conn, $query);
    
    // Redirect to remove the query parameter
    redirect('notifications.php');
}

// Get notifications for the student
$query = "SELECT n.*, sn.is_read, sn.read_at,
          CASE 
              WHEN n.created_role = 'hod' THEN (SELECT name FROM hods WHERE id = n.created_by)
              WHEN n.created_role = 'dean' THEN (SELECT name FROM deans WHERE id = n.created_by)
              WHEN n.created_role = 'admin' THEN (SELECT name FROM admins WHERE id = n.created_by)
          END as creator_name
          FROM notifications n
          JOIN student_notifications sn ON n.id = sn.notification_id
          WHERE sn.student_id = $student_id
          ORDER BY n.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <section class="notifications-hero">
            <div class="container">
                <div class="section-header">
                    <h1>Notifications</h1>
                    <p>Stay updated with important announcements and opportunities</p>
                    <div class="underline"></div>
                </div>
            </div>
        </section>
        
        <section class="notifications-section">
            <div class="container">
                <div class="tabs">
                    <button class="tab-button active" data-tab="all">All</button>
                    <button class="tab-button" data-tab="unread">Unread</button>
                </div>
                
                <div class="notifications-wrapper">
                    <?php
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $is_read = $row['is_read'] ? 'read' : 'unread';
                            
                            echo '<div class="notification-item '.$is_read.'" data-status="'.$is_read.'">';
                            
                            if(!$row['is_read']) {
                                echo '<div class="unread-indicator"></div>';
                            }
                            
                            echo '<div class="notification-content">';
                            echo '<div class="notification-header">';
                            echo '<h3>'.$row['title'].'</h3>';
                            echo '<span class="notification-date">'.date('M d, Y', strtotime($row['created_at'])).'</span>';
                            echo '</div>';
                            
                            echo '<div class="notification-body">';
                            echo '<p>'.$row['message'].'</p>';
                            echo '</div>';
                            
                            echo '<div class="notification-footer">';
                            echo '<span class="notification-sender">From: '.$row['creator_name'].' ('.ucfirst($row['created_role']).')</span>';
                            
                            if(!$row['is_read']) {
                                echo '<a href="notifications.php?read='.$row['id'].'" class="mark-read-btn">Mark as Read</a>';
                            } else {
                                echo '<span class="read-status">Read '.date('M d, Y', strtotime($row['read_at'])).'</span>';
                            }
                            
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="no-notifications">';
                        echo '<div class="no-notifications-icon">';
                        echo '<i class="far fa-bell-slash"></i>';
                        echo '</div>';
                        echo '<h3>No Notifications</h3>';
                        echo '<p>You don\'t have any notifications yet. Check back later!</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Tab switching
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                button.classList.add('active');
                
                // Filter notifications
                const filter = button.dataset.tab;
                const notifications = document.querySelectorAll('.notification-item');
                
                notifications.forEach(notification => {
                    if(filter === 'all' || notification.dataset.status === filter) {
                        notification.style.display = 'flex';
                    } else {
                        notification.style.display = 'none';
                    }
                });
            });
        });
        
        // Mark all as read button
        document.addEventListener('DOMContentLoaded', function() {
            const unreadNotifications = document.querySelectorAll('.notification-item.unread');
            const markAllBtn = document.querySelector('.mark-all-btn');
            
            if(unreadNotifications.length === 0 && markAllBtn) {
                markAllBtn.style.display = 'none';
            }
        });
    </script>
</body>
</html>