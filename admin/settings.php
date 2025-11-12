
<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and is admin
if(!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
}

// Handle settings update
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['update_site_settings'])) {
        $site_name = mysqli_real_escape_string($conn, $_POST['site_name']);
        $site_email = mysqli_real_escape_string($conn, $_POST['site_email']);
        $registration_open = isset($_POST['registration_open']) ? 1 : 0;
        
        // Update or insert settings
        $settings = [
            'site_name' => $site_name,
            'site_email' => $site_email,
            'registration_open' => $registration_open
        ];
        
        foreach($settings as $key => $value) {
            $query = "INSERT INTO site_settings (setting_key, setting_value) 
                      VALUES ('$key', '$value') 
                      ON DUPLICATE KEY UPDATE setting_value = '$value'";
            mysqli_query($conn, $query);
        }
        
        $success_message = "Settings updated successfully!";
    }
    
    if(isset($_POST['update_points_settings'])) {
        $easy_points = (int)$_POST['easy_points'];
        $medium_points = (int)$_POST['medium_points'];
        $hard_points = (int)$_POST['hard_points'];
        $certificate_points = (int)$_POST['certificate_points'];
        
        $points_settings = [
            'easy_problem_points' => $easy_points,
            'medium_problem_points' => $medium_points,
            'hard_problem_points' => $hard_points,
            'certificate_points' => $certificate_points
        ];
        
        foreach($points_settings as $key => $value) {
            $query = "INSERT INTO site_settings (setting_key, setting_value) 
                      VALUES ('$key', '$value') 
                      ON DUPLICATE KEY UPDATE setting_value = '$value'";
            mysqli_query($conn, $query);
        }
        
        $success_message = "Points settings updated successfully!";
    }
}

// Create settings table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_table);

// Get current settings
function getSetting($conn, $key, $default = '') {
    $query = "SELECT setting_value FROM site_settings WHERE setting_key = '$key'";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result)['setting_value'];
    }
    return $default;
}

$site_name = getSetting($conn, 'site_name', SITE_NAME);
$site_email = getSetting($conn, 'site_email', 'admin@example.com');
$registration_open = getSetting($conn, 'registration_open', '1');
$easy_points = getSetting($conn, 'easy_problem_points', '10');
$medium_points = getSetting($conn, 'medium_problem_points', '20');
$hard_points = getSetting($conn, 'hard_problem_points', '30');
$certificate_points = getSetting($conn, 'certificate_points', '50');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo SITE_NAME; ?></title>
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
                <a href="contact-messages.php">
                    <i class="fas fa-envelope"></i>
                    <span>Contact Messages</span>
                </a>
                <a href="analytics.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
                <a href="settings.php" class="active">
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
                    <input type="text" placeholder="Search settings...">
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

                <div class="settings-grid">
                    <!-- Site Settings -->
                    <div class="settings-section">
                        <div class="section-header">
                            <h2><i class="fas fa-globe"></i> Site Settings</h2>
                        </div>
                        <div class="section-content">
                            <form method="POST" class="settings-form">
                                <div class="form-group">
                                    <label for="site_name">Site Name:</label>
                                    <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_name); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_email">Admin Email:</label>
                                    <input type="email" id="site_email" name="site_email" value="<?php echo htmlspecialchars($site_email); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="registration_open" <?php echo $registration_open ? 'checked' : ''; ?>>
                                        <span>Allow New Registrations</span>
                                    </label>
                                </div>
                                
                                <button type="submit" name="update_site_settings" class="btn btn-primary">Update Site Settings</button>
                            </form>
                        </div>
                    </div>

                    <!-- Points Settings -->
                    <div class="settings-section">
                        <div class="section-header">
                            <h2><i class="fas fa-star"></i> Points Settings</h2>
                        </div>
                        <div class="section-content">
                            <form method="POST" class="settings-form">
                                <div class="form-group">
                                    <label for="easy_points">Easy Problem Points:</label>
                                    <input type="number" id="easy_points" name="easy_points" value="<?php echo $easy_points; ?>" min="1" max="100" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="medium_points">Medium Problem Points:</label>
                                    <input type="number" id="medium_points" name="medium_points" value="<?php echo $medium_points; ?>" min="1" max="100" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="hard_points">Hard Problem Points:</label>
                                    <input type="number" id="hard_points" name="hard_points" value="<?php echo $hard_points; ?>" min="1" max="100" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="certificate_points">Certificate Points:</label>
                                    <input type="number" id="certificate_points" name="certificate_points" value="<?php echo $certificate_points; ?>" min="1" max="200" required>
                                </div>
                                
                                <button type="submit" name="update_points_settings" class="btn btn-primary">Update Points Settings</button>
                            </form>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="settings-section">
                        <div class="section-header">
                            <h2><i class="fas fa-info-circle"></i> System Information</h2>
                        </div>
                        <div class="section-content">
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>PHP Version:</label>
                                    <span><?php echo phpversion(); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>MySQL Version:</label>
                                    <span><?php echo mysqli_get_server_info($conn); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Server Software:</label>
                                    <span><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Upload Max Size:</label>
                                    <span><?php echo ini_get('upload_max_filesize'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Database Actions -->
                    <div class="settings-section">
                        <div class="section-header">
                            <h2><i class="fas fa-database"></i> Database Actions</h2>
                        </div>
                        <div class="section-content">
                            <div class="action-buttons">
                                <button class="btn btn-secondary" onclick="alert('Backup feature coming soon!')">
                                    <i class="fas fa-download"></i> Backup Database
                                </button>
                                <button class="btn btn-warning" onclick="alert('Maintenance mode coming soon!')">
                                    <i class="fas fa-tools"></i> Maintenance Mode
                                </button>
                                <button class="btn btn-info" onclick="clearCache()">
                                    <i class="fas fa-broom"></i> Clear Cache
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../js/admin.js"></script>
    <script>
        function clearCache() {
            if(confirm('Are you sure you want to clear the cache?')) {
                // Implement cache clearing logic
                alert('Cache cleared successfully!');
            }
        }
    </script>
    
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: var(--spacing-lg);
        }
        
        .settings-section {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .settings-section .section-header {
            background: var(--primary-light);
            padding: var(--spacing-md);
            border-bottom: 1px solid var(--grey-200);
        }
        
        .settings-section .section-header h2 {
            margin: 0;
            color: var(--primary);
            font-size: var(--text-lg);
        }
        
        .settings-form {
            padding: var(--spacing-lg);
        }
        
        .form-group {
            margin-bottom: var(--spacing-md);
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            cursor: pointer;
        }
        
        .info-grid {
            display: grid;
            gap: var(--spacing-md);
            padding: var(--spacing-lg);
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-sm);
            background: var(--grey-100);
            border-radius: var(--border-radius-md);
        }
        
        .info-item label {
            font-weight: 500;
            color: var(--grey-700);
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
            padding: var(--spacing-lg);
        }
        
        .action-buttons .btn {
            justify-content: flex-start;
        }
        
        .action-buttons .btn i {
            margin-right: var(--spacing-sm);
        }
        
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-xs);
            }
        }
    </style>
</body>
</html>
