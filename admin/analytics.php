
<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and is admin
if(!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
}

// Get analytics data
$analytics = [];

// Student growth over time
$query = "SELECT DATE(created_at) as date, COUNT(*) as count 
          FROM students 
          WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
          GROUP BY DATE(created_at) 
          ORDER BY date";
$student_growth = mysqli_query($conn, $query);

// Problem solving trends
$query = "SELECT DATE(submitted_at) as date, COUNT(*) as submissions,
                 SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
          FROM problem_submissions 
          WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
          GROUP BY DATE(submitted_at) 
          ORDER BY date";
$problem_trends = mysqli_query($conn, $query);

// Department wise statistics
$query = "SELECT s.department, 
                 COUNT(s.id) as student_count,
                 AVG(COALESCE(sp.total_points, 0)) as avg_points,
                 SUM(COALESCE(ps_count.problems_solved, 0)) as total_problems
          FROM students s
          LEFT JOIN (
              SELECT student_id, SUM(points) as total_points 
              FROM student_points 
              GROUP BY student_id
          ) sp ON s.id = sp.student_id
          LEFT JOIN (
              SELECT student_id, COUNT(*) as problems_solved 
              FROM problem_submissions 
              WHERE status = 'approved' 
              GROUP BY student_id
          ) ps_count ON s.id = ps_count.student_id
          WHERE s.is_active = 1
          GROUP BY s.department";
$dept_stats = mysqli_query($conn, $query);

// Platform preferences
$query = "SELECT dp.platform, COUNT(ps.id) as submissions
          FROM problem_submissions ps
          JOIN daily_problems dp ON ps.problem_id = dp.id
          GROUP BY dp.platform";
$platform_stats = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="analytics.php" class="active">
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
                    <input type="text" placeholder="Search analytics...">
                </div>
                
                <div class="header-profile">
                    <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <img src="../images/default-avatar.png" alt="Admin">
                </div>
            </header>

            <div class="admin-content">
                <!-- Charts Grid -->
                <div class="analytics-grid">
                    <!-- Student Growth Chart -->
                    <div class="chart-container">
                        <h3>Student Registration (Last 30 Days)</h3>
                        <canvas id="studentGrowthChart"></canvas>
                    </div>

                    <!-- Problem Solving Trends -->
                    <div class="chart-container">
                        <h3>Problem Solving Activity</h3>
                        <canvas id="problemTrendsChart"></canvas>
                    </div>

                    <!-- Platform Distribution -->
                    <div class="chart-container">
                        <h3>Platform Preferences</h3>
                        <canvas id="platformChart"></canvas>
                    </div>

                    <!-- Department Statistics -->
                    <div class="chart-container">
                        <h3>Department Performance</h3>
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>

                <!-- Department Stats Table -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Department Statistics</h2>
                    </div>
                    <div class="section-content">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Students</th>
                                        <th>Avg Points</th>
                                        <th>Total Problems Solved</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($dept = mysqli_fetch_assoc($dept_stats)): ?>
                                        <tr>
                                            <td><?php echo $departments[$dept['department']]; ?></td>
                                            <td><?php echo $dept['student_count']; ?></td>
                                            <td><?php echo round($dept['avg_points'], 1); ?></td>
                                            <td><?php echo $dept['total_problems']; ?></td>
                                            <td>
                                                <?php 
                                                $performance = $dept['avg_points'] > 50 ? 'excellent' : ($dept['avg_points'] > 25 ? 'good' : 'needs-improvement');
                                                $performance_text = $dept['avg_points'] > 50 ? 'Excellent' : ($dept['avg_points'] > 25 ? 'Good' : 'Needs Improvement');
                                                ?>
                                                <span class="performance-badge <?php echo $performance; ?>"><?php echo $performance_text; ?></span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../js/admin.js"></script>
    <script>
        // Student Growth Chart
        const studentCtx = document.getElementById('studentGrowthChart').getContext('2d');
        const studentData = [
            <?php 
            mysqli_data_seek($student_growth, 0);
            while($row = mysqli_fetch_assoc($student_growth)) {
                echo "{ x: '".$row['date']."', y: ".$row['count']." },";
            }
            ?>
        ];

        new Chart(studentCtx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'New Students',
                    data: studentData,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day'
                        }
                    }
                }
            }
        });

        // Platform Chart
        const platformCtx = document.getElementById('platformChart').getContext('2d');
        new Chart(platformCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php 
                    while($platform = mysqli_fetch_assoc($platform_stats)) {
                        echo "'".$platform['platform']."',";
                    }
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php 
                        mysqli_data_seek($platform_stats, 0);
                        while($platform = mysqli_fetch_assoc($platform_stats)) {
                            echo $platform['submissions'].",";
                        }
                        ?>
                    ],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
    
    <style>
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }
        
        .chart-container {
            background: var(--white);
            padding: var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .chart-container h3 {
            margin-bottom: var(--spacing-md);
            color: var(--grey-900);
            text-align: center;
        }
        
        .performance-badge {
            padding: 4px 8px;
            border-radius: var(--border-radius-md);
            font-size: var(--text-xs);
            font-weight: 500;
        }
        
        .performance-badge.excellent {
            background-color: var(--secondary-light);
            color: var(--secondary-dark);
        }
        
        .performance-badge.good {
            background-color: var(--accent-light);
            color: var(--accent-dark);
        }
        
        .performance-badge.needs-improvement {
            background-color: var(--danger-light);
            color: var(--danger-dark);
        }
        
        @media (max-width: 768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
