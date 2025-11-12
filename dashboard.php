<?php
session_start();
include 'includes/config.php';

// Check if user is logged in and redirect appropriately
if(!isLoggedIn()) {
    redirect('login.php');
}

// Redirect students to main page
if($_SESSION['user_role'] === 'student') {
    redirect('index.php');
}

// Admin functionality - handle various POST actions
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle submission review
    if(isset($_POST['review_submission'])) {
        $submission_id = (int)$_POST['submission_id'];
        $action = $_POST['action'];
        $admin_comment = mysqli_real_escape_string($conn, $_POST['admin_comment']);
        $admin_id = $_SESSION['admin_id'];

        $query = "SELECT ps.*, dp.points 
                  FROM problem_submissions ps 
                  JOIN daily_problems dp ON ps.problem_id = dp.id 
                  WHERE ps.id = $submission_id";
        $result = mysqli_query($conn, $query);
        $submission = mysqli_fetch_assoc($result);

        if($submission) {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            $points_awarded = ($action === 'approve') ? $submission['points'] : 0;

            $query = "UPDATE problem_submissions 
                      SET status = '$status', 
                          points_awarded = $points_awarded, 
                          admin_comment = '$admin_comment',
                          reviewed_at = NOW(),
                          reviewed_by = $admin_id
                      WHERE id = $submission_id";

            if(mysqli_query($conn, $query)) {
                if($action === 'approve') {
                    $description = "Daily Problem Solution: " . $submission['points'] . " points";
                    $query = "INSERT INTO student_points (student_id, points, description) 
                              VALUES ({$submission['student_id']}, $points_awarded, '$description')";
                    mysqli_query($conn, $query);
                }
                $success_message = "Submission " . $status . " successfully!";
            }
        }
    }

    // Handle student status updates
    if(isset($_POST['update_student_status'])) {
        $student_id = (int)$_POST['student_id'];
        $new_status = $_POST['status'] === '1' ? 1 : 0;

        $query = "UPDATE students SET is_active = $new_status WHERE id = $student_id";
        if(mysqli_query($conn, $query)) {
            $success_message = "Student status updated successfully!";
        }
    }

    // Handle message status update
    if(isset($_POST['update_message_status'])) {
        $message_id = (int)$_POST['message_id'];
        $status = $_POST['status'];

        $query = "UPDATE contact_messages SET status = '$status', updated_at = NOW() WHERE id = $message_id";
        if(mysqli_query($conn, $query)) {
            $success_message = "Message status updated successfully!";
        }
    }

    // Handle adding new daily problems
    if(isset($_POST['add_problem'])) {
        $date = $_POST['problem_date'];
        $platform = $_POST['platform'];
        $difficulty = $_POST['difficulty'];
        $title = mysqli_real_escape_string($conn, $_POST['problem_title']);
        $url = mysqli_real_escape_string($conn, $_POST['problem_url']);
        $points = (int)$_POST['points'];

        $query = "INSERT INTO daily_problems (date, platform, difficulty, problem_title, problem_url, points) 
                  VALUES ('$date', '$platform', '$difficulty', '$title', '$url', $points)";

        if(mysqli_query($conn, $query)) {
            $success_message = "Problem added successfully!";
        }
    }

    // Handle settings update
    if(isset($_POST['update_site_settings'])) {
        $site_name = mysqli_real_escape_string($conn, $_POST['site_name']);
        $site_email = mysqli_real_escape_string($conn, $_POST['site_email']);
        $registration_open = isset($_POST['registration_open']) ? 1 : 0;

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
}

// Get current page/section
$current_section = isset($_GET['section']) ? $_GET['section'] : 'overview';

// Get statistics
$stats = [];

// Total students
$query = "SELECT COUNT(*) as count FROM students";
$result = mysqli_query($conn, $query);
$stats['total_students'] = mysqli_fetch_assoc($result)['count'];

// Active students
$query = "SELECT COUNT(*) as count FROM students WHERE is_active = 1";
$result = mysqli_query($conn, $query);
$stats['active_students'] = mysqli_fetch_assoc($result)['count'];

// Total events
$query = "SELECT COUNT(*) as count FROM events";
$result = mysqli_query($conn, $query);
$stats['total_events'] = mysqli_fetch_assoc($result)['count'];

// Total certifications
$query = "SELECT COUNT(*) as count FROM certifications";
$result = mysqli_query($conn, $query);
$stats['total_certifications'] = mysqli_fetch_assoc($result)['count'];

// Total opportunities
$query = "SELECT COUNT(*) as count FROM opportunities";
$result = mysqli_query($conn, $query);
$stats['total_opportunities'] = mysqli_fetch_assoc($result)['count'];

// Pending problem submissions
$query = "SELECT COUNT(*) as count FROM problem_submissions WHERE status = 'pending'";
$result = mysqli_query($conn, $query);
$stats['pending_submissions'] = mysqli_fetch_assoc($result)['count'];

// Today's problems count
$today = date('Y-m-d');
$query = "SELECT COUNT(*) as count FROM daily_problems WHERE date = '$today'";
$result = mysqli_query($conn, $query);
$stats['today_problems'] = mysqli_fetch_assoc($result)['count'];

// Unread messages
$query = "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'";
$result = mysqli_query($conn, $query);
$stats['unread_messages'] = mysqli_fetch_assoc($result)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="images/dsc-logo.svg" alt="DSC Logo">
                <h2>Admin Panel</h2>
            </div>

            <nav class="sidebar-nav">
                <a href="?section=overview" class="<?php echo $current_section === 'overview' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="?section=students" class="<?php echo $current_section === 'students' ? 'active' : ''; ?>">
                    <i class="fas fa-user-graduate"></i>
                    <span>Students</span>
                </a>
                <a href="leaderboard.php">
                    <i class="fas fa-trophy"></i>
                    <span>Leaderboard</span>
                </a>
                <a href="?section=problems" class="<?php echo $current_section === 'problems' ? 'active' : ''; ?>">
                    <i class="fas fa-code"></i>
                    <span>Daily Problems</span>
                </a>
                <a href="?section=messages" class="<?php echo $current_section === 'messages' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Contact Messages</span>
                </a>
                <a href="?section=analytics" class="<?php echo $current_section === 'analytics' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
                <a href="?section=settings" class="<?php echo $current_section === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="?section=downloads" class="<?php echo $current_section === 'downloads' ? 'active' : ''; ?>">
                    <i class="fas fa-download"></i>
                    <span>Download Reports</span>
                </a>
                <a href="logout.php">
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
                    <input type="text" placeholder="Search...">
                </div>

                <div class="header-profile">
                    <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <img src="images/default-avatar.png" alt="Admin">
                </div>
            </header>

            <div class="admin-content">
                <?php if(isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if($current_section === 'overview'): ?>
                    <!-- Dashboard Overview -->
                    <div class="dashboard-stats">
                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: var(--primary-light);">
                                <i class="fas fa-user-graduate" style="color: var(--primary);"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_students']; ?></h3>
                                <p>Total Students</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: var(--secondary-light);">
                                <i class="fas fa-users" style="color: var(--secondary);"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['active_students']; ?></h3>
                                <p>Active Students</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: var(--accent-light);">
                                <i class="fas fa-code" style="color: var(--accent-dark);"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['today_problems']; ?></h3>
                                <p>Today's Problems</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: var(--danger-light);">
                                <i class="fas fa-clock" style="color: var(--danger);"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['pending_submissions']; ?></h3>
                                <p>Pending Submissions</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: var(--primary-light);">
                                <i class="fas fa-envelope" style="color: var(--primary);"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['unread_messages']; ?></h3>
                                <p>Unread Messages</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon" style="background-color: var(--secondary-light);">
                                <i class="fas fa-certificate" style="color: var(--secondary);"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_certifications']; ?></h3>
                                <p>Total Certifications</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="dashboard-sections">
                        <div class="dashboard-section">
                            <div class="section-header">
                                <h2>Recent Problem Submissions</h2>
                                <a href="?section=problems" class="btn btn-small">View All</a>
                            </div>
                            <div class="section-content">
                                <div class="table-container">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Problem</th>
                                                <th>Platform</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT ps.*, dp.problem_title, dp.platform, 
                                                             s.first_name, s.last_name
                                                      FROM problem_submissions ps
                                                      JOIN daily_problems dp ON ps.problem_id = dp.id
                                                      JOIN students s ON ps.student_id = s.id
                                                      WHERE s.is_active = 1
                                                      ORDER BY ps.submitted_at DESC 
                                                      LIMIT 5";
                                            $result = mysqli_query($conn, $query);

                                            while($row = mysqli_fetch_assoc($result)) {
                                                echo '<tr>';
                                                echo '<td>'.$row['first_name'].' '.$row['last_name'].'</td>';
                                                echo '<td>'.htmlspecialchars($row['problem_title']).'</td>';
                                                echo '<td><span class="platform-badge '.strtolower($row['platform']).'">'.ucfirst($row['platform']).'</span></td>';
                                                echo '<td><span class="status-'.$row['status'].'">'.$row['status'].'</span></td>';
                                                echo '<td>'.date('M d, Y', strtotime($row['submitted_at'])).'</td>';
                                                echo '</tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif($current_section === 'students'): ?>
                    <!-- Students Management -->
                    <?php include 'admin/students.php'; ?>

                <?php elseif($current_section === 'problems'): ?>
                    <!-- Problems Management -->
                    <?php include 'admin/problems.php'; ?>

                <?php elseif($current_section === 'messages'): ?>
                    <!-- Contact Messages -->
                    <?php include 'admin/contact-messages.php'; ?>

                <?php elseif($current_section === 'analytics'): ?>
                    <!-- Analytics -->
                    <?php include 'admin/analytics.php'; ?>

                <?php elseif($current_section === 'settings'): ?>
                    <!-- Settings -->
                    <?php include 'admin/settings.php'; ?>

                <?php elseif($current_section === 'downloads'): ?>
                    <!-- Download Reports -->
                    <?php include 'admin/download-progress.php'; ?>

                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="js/admin.js"></script>
    <script>
        // Handle section navigation
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar-nav a[href*="section="]');

            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Remove active class from all links
                    sidebarLinks.forEach(l => l.classList.remove('active'));
                    // Add active class to clicked link
                    this.classList.add('active');
                });
            });
        });
    </script>

    <style>
        .dashboard-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: var(--spacing-lg);
        }

        .dashboard-section {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-lg);
            background: var(--grey-100);
            border-bottom: 1px solid var(--grey-200);
        }

        .section-header h2 {
            margin: 0;
            color: var(--grey-900);
            font-size: var(--text-lg);
        }

        .section-content {
            padding: var(--spacing-lg);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--border-radius-circle);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--text-xl);
        }

        .stat-info h3 {
            font-size: var(--text-xxl);
            margin: 0;
            color: var(--grey-900);
        }

        .stat-info p {
            margin: 0;
            color: var(--grey-600);
            font-size: var(--text-sm);
        }

        .platform-badge {
            padding: 4px 8px;
            border-radius: var(--border-radius-sm);
            font-size: var(--text-xs);
            font-weight: 500;
        }

        .platform-badge.leetcode {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .platform-badge.hackerrank {
            background-color: var(--secondary-light);
            color: var(--secondary);
        }

        @media (max-width: 768px) {
            .dashboard-sections {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-sm);
            }
        }
    </style>
</body>
</html>