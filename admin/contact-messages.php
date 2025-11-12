
<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and is admin
if(!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
}

// Handle message status update
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $message_id = (int)$_POST['message_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE contact_messages SET status = '$status', updated_at = NOW() WHERE id = $message_id";
    if(mysqli_query($conn, $query)) {
        $success_message = "Message status updated successfully!";
    } else {
        $error_message = "Error updating message status.";
    }
}

// Get messages
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$where_clause = '';
if($status_filter !== 'all') {
    $where_clause = "WHERE status = '$status_filter'";
}

$query = "SELECT * FROM contact_messages $where_clause ORDER BY created_at DESC";
$messages_result = mysqli_query($conn, $query);

// Check if table exists, if not create it
if(!$messages_result) {
    $create_table = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $create_table);
    $messages_result = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="../images/dsc-logo.svg" alt="DSC Logo">
                <h2>Admin Panel</h2>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="students.php">
                    <i class="fas fa-user-graduate"></i>
                    <span>Students</span>
                </a>
                <a href="../leaderboard.php">
                    <i class="fas fa-trophy"></i>
                    <span>Leaderboard</span>
                </a>
                <a href="events.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Events</span>
                </a>
                <a href="opportunities.php">
                    <i class="fas fa-briefcase"></i>
                    <span>Opportunities</span>
                </a>
                <a href="certifications.php">
                    <i class="fas fa-certificate"></i>
                    <span>Certifications</span>
                </a>
                <a href="problems.php">
                    <i class="fas fa-code"></i>
                    <span>Daily Problems</span>
                </a>
                <a href="auto-add-problems.php">
                    <i class="fas fa-robot"></i>
                    <span>Auto Add Problems</span>
                </a>
                <a href="download-progress.php">
                    <i class="fas fa-download"></i>
                    <span>Download Reports</span>
                </a>
                <a href="notifications.php">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
                <a href="team.php">
                    <i class="fas fa-users"></i>
                    <span>Team Management</span>
                </a>
                <a href="contact-messages.php" class="active">
                    <i class="fas fa-envelope"></i>
                    <span>Contact Messages</span>
                </a>
                <a href="analytics.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="header-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search messages...">
                </div>
                
                <div class="header-profile">
                    <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <img src="../images/default-avatar.png" alt="Admin">
                </div>
            </header>

            <div class="admin-content">
                <?php if(isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <!-- Filter -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Filter Messages</h2>
                    </div>
                    <div class="section-content">
                        <div class="filter-controls">
                            <div class="filter-group">
                                <label for="status-filter">Status:</label>
                                <select id="status-filter" onchange="filterMessages(this.value)">
                                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Messages</option>
                                    <option value="unread" <?php echo $status_filter === 'unread' ? 'selected' : ''; ?>>Unread</option>
                                    <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Read</option>
                                    <option value="replied" <?php echo $status_filter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Contact Messages (<?php echo mysqli_num_rows($messages_result); ?>)</h2>
                    </div>
                    <div class="section-content">
                        <?php if(mysqli_num_rows($messages_result) > 0): ?>
                            <div class="messages-container">
                                <?php while($message = mysqli_fetch_assoc($messages_result)): ?>
                                    <div class="message-card <?php echo $message['status']; ?>">
                                        <div class="message-header">
                                            <div class="message-sender">
                                                <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                                <span><?php echo htmlspecialchars($message['email']); ?></span>
                                            </div>
                                            <div class="message-meta">
                                                <span class="status-<?php echo $message['status']; ?>"><?php echo ucfirst($message['status']); ?></span>
                                                <span class="message-date"><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="message-subject">
                                            <h4><?php echo htmlspecialchars($message['subject']); ?></h4>
                                        </div>
                                        
                                        <div class="message-content">
                                            <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                        </div>
                                        
                                        <div class="message-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                <?php if($message['status'] === 'unread'): ?>
                                                    <button type="submit" name="update_status" value="read" class="btn btn-small btn-primary">Mark as Read</button>
                                                <?php endif; ?>
                                                <?php if($message['status'] !== 'replied'): ?>
                                                    <button type="submit" name="update_status" value="replied" class="btn btn-small btn-success">Mark as Replied</button>
                                                <?php endif; ?>
                                            </form>
                                            <a href="mailto:<?php echo $message['email']; ?>?subject=Re: <?php echo urlencode($message['subject']); ?>" class="btn btn-small btn-secondary">
                                                <i class="fas fa-reply"></i> Reply
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="no-data">No contact messages found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../js/admin.js"></script>
    <script>
        function filterMessages(status) {
            window.location.href = '?status=' + status;
        }
    </script>
    
    <style>
        .messages-container {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .message-card {
            background: var(--white);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-lg);
            border-left: 4px solid var(--grey-300);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .message-card.unread {
            border-left-color: var(--primary);
            background: var(--primary-light);
        }
        
        .message-card.read {
            border-left-color: var(--secondary);
        }
        
        .message-card.replied {
            border-left-color: var(--accent);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-sm);
            padding-bottom: var(--spacing-sm);
            border-bottom: 1px solid var(--grey-200);
        }
        
        .message-sender strong {
            display: block;
            color: var(--grey-900);
        }
        
        .message-sender span {
            color: var(--grey-600);
            font-size: var(--text-sm);
        }
        
        .message-meta {
            text-align: right;
        }
        
        .message-date {
            display: block;
            color: var(--grey-600);
            font-size: var(--text-sm);
            margin-top: var(--spacing-xs);
        }
        
        .message-subject h4 {
            margin: var(--spacing-sm) 0;
            color: var(--grey-900);
        }
        
        .message-content {
            margin: var(--spacing-md) 0;
            color: var(--grey-700);
            line-height: 1.6;
        }
        
        .message-actions {
            display: flex;
            gap: var(--spacing-sm);
            flex-wrap: wrap;
            margin-top: var(--spacing-md);
            padding-top: var(--spacing-md);
            border-top: 1px solid var(--grey-200);
        }
        
        @media (max-width: 768px) {
            .message-header {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-sm);
            }
            
            .message-meta {
                text-align: left;
            }
            
            .message-actions {
                justify-content: center;
            }
        }
    </style>
</body>
</html>
