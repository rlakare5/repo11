
<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and is admin
if(!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
}

// Handle submission review
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['review_submission'])) {
        $submission_id = (int)$_POST['submission_id'];
        $action = $_POST['action'];
        $admin_comment = mysqli_real_escape_string($conn, $_POST['admin_comment']);
        $admin_id = $_SESSION['admin_id'];
        
        // Get submission details
        $query = "SELECT ps.*, dp.points 
                  FROM problem_submissions ps 
                  JOIN daily_problems dp ON ps.problem_id = dp.id 
                  WHERE ps.id = $submission_id";
        $result = mysqli_query($conn, $query);
        $submission = mysqli_fetch_assoc($result);
        
        if($submission) {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            $points_awarded = ($action === 'approve') ? $submission['points'] : 0;
            
            // Update submission
            $query = "UPDATE problem_submissions 
                      SET status = '$status', 
                          points_awarded = $points_awarded, 
                          admin_comment = '$admin_comment',
                          reviewed_at = NOW(),
                          reviewed_by = $admin_id
                      WHERE id = $submission_id";
            
            if(mysqli_query($conn, $query)) {
                // If approved, add points to student
                if($action === 'approve') {
                    $description = "Daily Problem Solution: " . $submission['points'] . " points";
                    $query = "INSERT INTO student_points (student_id, points, description) 
                              VALUES ({$submission['student_id']}, $points_awarded, '$description')";
                    mysqli_query($conn, $query);
                }
                $success_message = "Submission " . $status . " successfully!";
            } else {
                $error_message = "Error updating submission: " . mysqli_error($conn);
            }
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
        } else {
            $error_message = "Error adding problem: " . mysqli_error($conn);
        }
    }
}

// Get pending submissions (active students only by default)
$student_filter = isset($_GET['student_status']) ? $_GET['student_status'] : 'active';
$submission_filter = isset($_GET['submission_status']) ? $_GET['submission_status'] : 'pending';

$student_condition = '';
if ($student_filter === 'active') {
    $student_condition = 'AND s.is_active = 1';
} elseif ($student_filter === 'inactive') {
    $student_condition = 'AND s.is_active = 0';
}

$submission_condition = '';
if ($submission_filter !== 'all') {
    $submission_condition = "AND ps.status = '$submission_filter'";
} else {
    $submission_condition = "AND ps.status = 'pending'";
}

$query = "SELECT ps.*, dp.problem_title, dp.platform, dp.difficulty, dp.points,
                 s.first_name, s.last_name, s.prn
          FROM problem_submissions ps
          JOIN daily_problems dp ON ps.problem_id = dp.id
          JOIN students s ON ps.student_id = s.id
          WHERE 1=1 $student_condition $submission_condition
          ORDER BY ps.submitted_at DESC";
$pending_result = mysqli_query($conn, $query);

// Get recent submissions
$recent_query = "SELECT ps.*, dp.problem_title, dp.platform, dp.difficulty, dp.points,
                        s.first_name, s.last_name, s.prn,
                        a.name as reviewed_by_name
                 FROM problem_submissions ps
                 JOIN daily_problems dp ON ps.problem_id = dp.id
                 JOIN students s ON ps.student_id = s.id
                 LEFT JOIN admins a ON ps.reviewed_by = a.id
                 WHERE ps.status != 'pending' $student_condition
                 ORDER BY ps.reviewed_at DESC
                 LIMIT 20";
$recent_result = mysqli_query($conn, $recent_query);

// Get today's problems
$today = date('Y-m-d');
$query = "SELECT * FROM daily_problems WHERE date = '$today' ORDER BY platform, difficulty";
$today_problems = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Problem Management - <?php echo SITE_NAME; ?></title>
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
                <a href="problems.php" class="active">
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
                <?php if(isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <!-- Filter Controls -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Filters</h2>
                    </div>
                    <div class="section-content">
                        <div class="filter-controls">
                            <div class="filter-group">
                                <label for="student-status-filter">Student Status:</label>
                                <select id="student-status-filter" class="filter-select">
                                    <option value="all">All Students</option>
                                    <option value="active" selected>Active Students Only</option>
                                    <option value="inactive">Inactive Students Only</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="submission-status-filter">Submission Status:</label>
                                <select id="submission-status-filter" class="filter-select">
                                    <option value="all">All Submissions</option>
                                    <option value="pending">Pending Only</option>
                                    <option value="approved">Approved Only</option>
                                    <option value="rejected">Rejected Only</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add New Problem Section -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Add Daily Problem</h2>
                    </div>
                    <div class="section-content">
                        <form method="POST" class="problem-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="problem_date">Date:</label>
                                    <input type="date" id="problem_date" name="problem_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="platform">Platform:</label>
                                    <select id="platform" name="platform" required>
                                        <option value="leetcode">LeetCode</option>
                                        <option value="hackerrank">HackerRank</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="difficulty">Difficulty:</label>
                                    <select id="difficulty" name="difficulty" required>
                                        <option value="easy">Easy</option>
                                        <option value="medium">Medium</option>
                                        <option value="hard">Hard</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="problem_title">Problem Title:</label>
                                    <input type="text" id="problem_title" name="problem_title" required>
                                </div>
                                <div class="form-group">
                                    <label for="points">Points:</label>
                                    <input type="number" id="points" name="points" min="1" max="100" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="problem_url">Problem URL:</label>
                                <input type="url" id="problem_url" name="problem_url" required>
                            </div>
                            
                            <button type="submit" name="add_problem" class="btn btn-primary">Add Problem</button>
                        </form>
                    </div>
                </div>

                <!-- Today's Problems -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Today's Problems</h2>
                    </div>
                    <div class="section-content">
                        <div class="today-problems-grid">
                            <?php while($problem = mysqli_fetch_assoc($today_problems)): ?>
                                <div class="problem-card <?php echo $problem['difficulty']; ?>">
                                    <div class="platform-badge <?php echo $problem['platform']; ?>">
                                        <?php echo ucfirst($problem['platform']); ?>
                                    </div>
                                    <h4><?php echo htmlspecialchars($problem['problem_title']); ?></h4>
                                    <div class="problem-meta">
                                        <span class="difficulty"><?php echo ucfirst($problem['difficulty']); ?></span>
                                        <span class="points"><?php echo $problem['points']; ?> pts</span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- Pending Submissions -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Pending Submissions (<?php echo mysqli_num_rows($pending_result); ?>)</h2>
                    </div>
                    <div class="section-content">
                        <?php if(mysqli_num_rows($pending_result) > 0): ?>
                            <div class="table-container">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Problem</th>
                                            <th>Platform</th>
                                            <th>Difficulty</th>
                                            <th>Points</th>
                                            <th>Submitted</th>
                                            <th>File</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($submission = mysqli_fetch_assoc($pending_result)): ?>
                                            <tr>
                                                <td><?php echo $submission['first_name'] . ' ' . $submission['last_name']; ?><br>
                                                    <small><?php echo $submission['prn']; ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($submission['problem_title']); ?></td>
                                                <td><span class="platform-badge <?php echo $submission['platform']; ?>"><?php echo ucfirst($submission['platform']); ?></span></td>
                                                <td><span class="difficulty-badge <?php echo $submission['difficulty']; ?>"><?php echo ucfirst($submission['difficulty']); ?></span></td>
                                                <td><?php echo $submission['points']; ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($submission['submitted_at'])); ?></td>
                                                <td>
                                                    <a href="../uploads/solutions/<?php echo $submission['submission_file']; ?>" target="_blank" class="file-link">
                                                        <i class="fas fa-file-code"></i> View File
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button onclick="reviewSubmission(<?php echo $submission['id']; ?>, 'approve')" class="btn btn-small btn-success">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                        <button onclick="reviewSubmission(<?php echo $submission['id']; ?>, 'reject')" class="btn btn-small btn-danger">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="no-data">No pending submissions.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Reviews -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Recent Reviews</h2>
                    </div>
                    <div class="section-content">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Problem</th>
                                        <th>Status</th>
                                        <th>Points</th>
                                        <th>Reviewed By</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($submission = mysqli_fetch_assoc($recent_result)): ?>
                                        <tr>
                                            <td><?php echo $submission['first_name'] . ' ' . $submission['last_name']; ?></td>
                                            <td><?php echo htmlspecialchars($submission['problem_title']); ?></td>
                                            <td><span class="status-<?php echo $submission['status']; ?>"><?php echo ucfirst($submission['status']); ?></span></td>
                                            <td><?php echo $submission['points_awarded']; ?></td>
                                            <td><?php echo $submission['reviewed_by_name'] ?? 'Unknown'; ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($submission['reviewed_at'])); ?></td>
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

    <!-- Review Modal -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="modalTitle">Review Submission</h3>
            <form method="POST" id="reviewForm">
                <input type="hidden" id="submission_id" name="submission_id">
                <input type="hidden" id="action" name="action">
                
                <div class="form-group">
                    <label for="admin_comment">Comment (optional):</label>
                    <textarea id="admin_comment" name="admin_comment" rows="4"></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" name="review_submission" class="btn btn-primary" id="confirmBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/admin.js"></script>
    <script>
        function reviewSubmission(submissionId, action) {
            document.getElementById('submission_id').value = submissionId;
            document.getElementById('action').value = action;
            document.getElementById('modalTitle').textContent = action === 'approve' ? 'Approve Submission' : 'Reject Submission';
            document.getElementById('confirmBtn').textContent = action === 'approve' ? 'Approve' : 'Reject';
            document.getElementById('confirmBtn').className = 'btn ' + (action === 'approve' ? 'btn-success' : 'btn-danger');
            document.getElementById('reviewModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('reviewModal').style.display = 'none';
            document.getElementById('admin_comment').value = '';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reviewModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Close modal with X button
        document.querySelector('.close').onclick = closeModal;

        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const studentFilter = document.getElementById('student-status-filter');
            const submissionFilter = document.getElementById('submission-status-filter');
            
            function applyFilters() {
                const studentStatus = studentFilter.value;
                const submissionStatus = submissionFilter.value;
                
                // Reload page with filters
                const url = new URL(window.location);
                url.searchParams.set('student_status', studentStatus);
                url.searchParams.set('submission_status', submissionStatus);
                window.location = url;
            }
            
            studentFilter.addEventListener('change', applyFilters);
            submissionFilter.addEventListener('change', applyFilters);
            
            // Set filter values from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('student_status')) {
                studentFilter.value = urlParams.get('student_status');
            }
            if (urlParams.get('submission_status')) {
                submissionFilter.value = urlParams.get('submission_status');
            }
        });
    </script>
    
    <style>
        .problem-form {
            background: var(--white);
            padding: var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .today-problems-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-md);
        }
        
        .problem-card {
            background: var(--white);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-md);
            border-left: 4px solid var(--primary);
            position: relative;
        }
        
        .problem-card.easy { border-left-color: var(--secondary); }
        .problem-card.medium { border-left-color: var(--accent); }
        .problem-card.hard { border-left-color: var(--danger); }
        
        .platform-badge {
            position: absolute;
            top: var(--spacing-sm);
            right: var(--spacing-sm);
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
        
        .problem-meta {
            display: flex;
            justify-content: space-between;
            margin-top: var(--spacing-sm);
        }
        
        .difficulty-badge {
            padding: 4px 8px;
            border-radius: var(--border-radius-md);
            font-size: var(--text-xs);
            font-weight: 500;
        }
        
        .difficulty-badge.easy {
            background-color: var(--secondary-light);
            color: var(--secondary-dark);
        }
        
        .difficulty-badge.medium {
            background-color: var(--accent-light);
            color: var(--accent-dark);
        }
        
        .difficulty-badge.hard {
            background-color: var(--danger-light);
            color: var(--danger-dark);
        }
        
        .action-buttons {
            display: flex;
            gap: var(--spacing-xs);
        }
        
        .file-link {
            color: var(--primary);
            text-decoration: none;
        }
        
        .file-link:hover {
            text-decoration: underline;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: var(--white);
            margin: 15% auto;
            padding: var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            width: 80%;
            max-width: 500px;
            position: relative;
        }
        
        .close {
            position: absolute;
            right: var(--spacing-md);
            top: var(--spacing-md);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-md);
            margin-top: var(--spacing-lg);
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
