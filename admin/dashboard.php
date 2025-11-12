<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and is admin
if(!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
}

// Get statistics
$stats = [];

// Total students
$query = "SELECT COUNT(*) as count FROM students";
$result = mysqli_query($conn, $query);
$stats['total_students'] = mysqli_fetch_assoc($result)['count'];

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

// Total contact messages
$query = "SELECT COUNT(*) as count FROM contact_messages";
$result = mysqli_query($conn, $query);
$stats['total_messages'] = mysqli_fetch_assoc($result)['count'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Developer Student Club</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="../images/logo.png" alt="Developer Student Club Logo" style="height: 40px;">
                <h2>DSC Admin</h2>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="students.php">
                    <i class="fas fa-user-graduate"></i>
                    <span>Students</span>
                </a>
                <a href="../leaderboard.php" target="_blank">
                    <i class="fas fa-trophy"></i>
                    <span>Leaderboard</span>
                </a>
                <a href="../events.php" target="_blank">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Events</span>
                </a>
                <a href="../opportunities.php" target="_blank">
                    <i class="fas fa-briefcase"></i>
                    <span>Opportunities</span>
                </a>
                <a href="../upload.php" target="_blank">
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
                <a href="../daily-problems.php" target="_blank">
                    <i class="fas fa-tasks"></i>
                    <span>Problem Submissions</span>
                </a>
                <a href="../team.php" target="_blank">
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
                    <input type="text" placeholder="Search...">
                </div>
                
                <div class="header-profile">
                    <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <img src="../images/default-avatar.png" alt="Admin">
                </div>
            </header>

            <div class="admin-content">
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
                            <i class="fas fa-calendar-alt" style="color: var(--secondary);"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_events']; ?></h3>
                            <p>Total Events</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: var(--accent-light);">
                            <i class="fas fa-certificate" style="color: var(--accent-dark);"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_certifications']; ?></h3>
                            <p>Total Certifications</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: var(--danger-light);">
                            <i class="fas fa-briefcase" style="color: var(--danger);"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_opportunities']; ?></h3>
                            <p>Total Opportunities</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: var(--primary-light);">
                            <i class="fas fa-code" style="color: var(--primary);"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['today_problems']; ?></h3>
                            <p>Today's Problems</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: var(--accent-light);">
                            <i class="fas fa-clock" style="color: var(--accent-dark);"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['pending_submissions']; ?></h3>
                            <p>Pending Submissions</p>
                        </div>
                    </div>
                </div>

                <!-- Filter Controls -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Filters</h2>
                    </div>
                    <div class="section-content">
                        <div class="filter-controls">
                            <div class="filter-group">
                                <label for="status-filter">Status Filter:</label>
                                <select id="status-filter" class="filter-select">
                                    <option value="all">All Records</option>
                                    <option value="active" selected>Active Only</option>
                                    <option value="inactive">Inactive Only</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-sections">
                    <!-- Recent Events -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>Recent Events</h2>
                            <a href="events.php" class="btn btn-small">View All</a>
                        </div>
                        <div class="section-content">
                            <div class="table-container">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Date</th>
                                            <th>Location</th>
                                            <th>Participants</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT e.*, COUNT(ep.id) as participant_count 
                                                  FROM events e 
                                                  LEFT JOIN event_participants ep ON e.id = ep.event_id 
                                                  GROUP BY e.id 
                                                  ORDER BY e.event_date DESC 
                                                  LIMIT 5";
                                        $result = mysqli_query($conn, $query);
                                        
                                        while($row = mysqli_fetch_assoc($result)) {
                                            echo '<tr>';
                                            echo '<td>'.$row['title'].'</td>';
                                            echo '<td>'.date('M d, Y', strtotime($row['event_date'])).'</td>';
                                            echo '<td>'.$row['location'].'</td>';
                                            echo '<td>'.$row['participant_count'].'</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Certifications -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>Recent Certifications</h2>
                            <a href="certifications.php" class="btn btn-small">View All</a>
                        </div>
                        <div class="section-content">
                            <div class="table-container">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Certificate</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT c.*, s.first_name, s.last_name 
                                                  FROM certifications c 
                                                  JOIN students s ON c.student_id = s.id 
                                                  ORDER BY c.created_at DESC 
                                                  LIMIT 5";
                                        $result = mysqli_query($conn, $query);
                                        
                                        while($row = mysqli_fetch_assoc($result)) {
                                            echo '<tr>';
                                            echo '<td>'.$row['first_name'].' '.$row['last_name'].'</td>';
                                            echo '<td>'.$row['title'].'</td>';
                                            echo '<td><span class="status-'.$row['status'].'">'.$row['status'].'</span></td>';
                                            echo '<td>'.date('M d, Y', strtotime($row['created_at'])).'</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Problem Submissions -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>Problem Submissions</h2>
                            <a href="problems.php" class="btn btn-small">View All</a>
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
                                            <th>Points</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT ps.*, dp.problem_title, dp.platform, dp.points,
                                                         s.first_name, s.last_name
                                                  FROM problem_submissions ps
                                                  JOIN daily_problems dp ON ps.problem_id = dp.id
                                                  JOIN students s ON ps.student_id = s.id
                                                  ORDER BY ps.submitted_at DESC 
                                                  LIMIT 5";
                                        $result = mysqli_query($conn, $query);
                                        
                                        while($row = mysqli_fetch_assoc($result)) {
                                            echo '<tr>';
                                            echo '<td>'.$row['first_name'].' '.$row['last_name'].'</td>';
                                            echo '<td>'.htmlspecialchars($row['problem_title']).'</td>';
                                            echo '<td><span class="platform-badge '.strtolower($row['platform']).'">'.ucfirst($row['platform']).'</span></td>';
                                            echo '<td><span class="status-'.$row['status'].'">'.$row['status'].'</span></td>';
                                            echo '<td>'.$row['points_awarded'].'</td>';
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

                <!-- Download Progress Section -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Download Progress Reports</h2>
                    </div>
                    <div class="section-content">
                        <div class="download-controls">
                            <div class="download-group">
                                <h3>Class-wise Progress</h3>
                                <div class="download-options">
                                    <?php
                                    // Get available class combinations
                                    $query = "SELECT DISTINCT department, year FROM students ORDER BY department, year";
                                    $result = mysqli_query($conn, $query);
                                    
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $dept_name = $departments[$row['department']];
                                        $year_name = $years[$row['year']];
                                        echo '<button class="btn btn-secondary download-btn" onclick="downloadClassProgress(\''.$row['department'].'\', \''.$row['year'].'\')">';
                                        echo '<i class="fas fa-download"></i> '.$dept_name.' '.$year_name;
                                        echo '</button>';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="download-group">
                                <h3>Department-wise Progress</h3>
                                <div class="download-options">
                                    <?php
                                    foreach($departments as $key => $value) {
                                        echo '<button class="btn btn-secondary download-btn" onclick="downloadDeptProgress(\''.$key.'\')">';
                                        echo '<i class="fas fa-download"></i> '.$value.' Department';
                                        echo '</button>';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="download-group">
                                <h3>Complete Reports</h3>
                                <div class="download-options">
                                    <button class="btn btn-primary download-btn" onclick="downloadAllProgress()">
                                        <i class="fas fa-download"></i> All Students Progress
                                    </button>
                                    <button class="btn btn-accent download-btn" onclick="downloadProblemStats()">
                                        <i class="fas fa-chart-bar"></i> Problem Solving Statistics
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../js/admin.js"></script>
    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('status-filter');
            const sections = document.querySelectorAll('.dashboard-section');
            
            statusFilter.addEventListener('change', function() {
                const filterValue = this.value;
                
                // Here you can add AJAX calls to reload sections with filtered data
                // For now, we'll just show a loading indicator
                sections.forEach(section => {
                    if (section.querySelector('.data-table')) {
                        section.style.opacity = '0.6';
                        setTimeout(() => {
                            section.style.opacity = '1';
                        }, 500);
                    }
                });
                
                // You can implement AJAX here to reload data based on filter
                console.log('Filter changed to:', filterValue);
            });
        });

        function downloadClassProgress(department, year) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'download-progress.php';
            
            const deptInput = document.createElement('input');
            deptInput.type = 'hidden';
            deptInput.name = 'department';
            deptInput.value = department;
            
            const yearInput = document.createElement('input');
            yearInput.type = 'hidden';
            yearInput.name = 'year';
            yearInput.value = year;
            
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'download_type';
            typeInput.value = 'class';
            
            form.appendChild(deptInput);
            form.appendChild(yearInput);
            form.appendChild(typeInput);
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
        
        function downloadDeptProgress(department) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'download-progress.php';
            
            const deptInput = document.createElement('input');
            deptInput.type = 'hidden';
            deptInput.name = 'department';
            deptInput.value = department;
            
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'download_type';
            typeInput.value = 'department';
            
            form.appendChild(deptInput);
            form.appendChild(typeInput);
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
        
        function downloadAllProgress() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'download-progress.php';
            
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'download_type';
            typeInput.value = 'all';
            
            form.appendChild(typeInput);
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
        
        function downloadProblemStats() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'download-progress.php';
            
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'download_type';
            typeInput.value = 'problem_stats';
            
            form.appendChild(typeInput);
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    </script>
    
    <style>
        .download-controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-xl);
        }
        
        .download-group {
            background: var(--white);
            padding: var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .download-group h3 {
            margin-bottom: var(--spacing-md);
            color: var(--grey-900);
            font-size: var(--text-lg);
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: var(--spacing-sm);
        }
        
        .download-options {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }
        
        .download-btn {
            justify-content: flex-start;
            text-align: left;
            transition: all 0.3s ease;
        }
        
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .download-btn i {
            margin-right: var(--spacing-sm);
            width: 16px;
        }
        
        .filter-controls {
            display: flex;
            gap: var(--spacing-lg);
            align-items: center;
            background: var(--white);
            padding: var(--spacing-md);
            border-radius: var(--border-radius-md);
            border: 1px solid var(--grey-300);
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .filter-group label {
            font-weight: 500;
            color: var(--grey-700);
            white-space: nowrap;
        }
        
        .filter-select {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--grey-300);
            border-radius: var(--border-radius-md);
            background: var(--white);
            color: var(--grey-900);
            min-width: 150px;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        @media (max-width: 768px) {
            .download-controls {
                grid-template-columns: 1fr;
            }
            
            .filter-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-select {
                min-width: auto;
            }
        }
    </style>
</body>
</html>