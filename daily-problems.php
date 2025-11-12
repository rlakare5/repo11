
<?php
session_start();
include 'includes/config.php';

// Check if user is logged in and is a student
if(!isLoggedIn() || $_SESSION['user_role'] !== 'student') {
    redirect('login.php');
}

$student_id = $_SESSION['student_id'];

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_solution'])) {
    $problem_id = (int)$_POST['problem_id'];
    
    // Check if file was uploaded
    if(isset($_FILES['solution_file']) && $_FILES['solution_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/solutions/';
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['solution_file']['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . $student_id . '_' . $problem_id . '.' . $file_extension;
        $filepath = $upload_dir . $filename;
        
        if(move_uploaded_file($_FILES['solution_file']['tmp_name'], $filepath)) {
            // Insert submission
            $query = "INSERT INTO problem_submissions (student_id, problem_id, submission_file) 
                      VALUES ($student_id, $problem_id, '$filename')";
            if(mysqli_query($conn, $query)) {
                $success_message = "Solution submitted successfully! It will be reviewed by admin.";
            } else {
                $error_message = "Error submitting solution: " . mysqli_error($conn);
            }
        } else {
            $error_message = "Error uploading file.";
        }
    } else {
        $error_message = "Please select a file to upload.";
    }
}

// Get today's problems
$today = date('Y-m-d');
$query = "SELECT * FROM daily_problems WHERE date = '$today' ORDER BY platform, difficulty";
$problems_result = mysqli_query($conn, $query);

// Get student's submissions for today
$query = "SELECT ps.*, dp.platform, dp.difficulty 
          FROM problem_submissions ps 
          JOIN daily_problems dp ON ps.problem_id = dp.id 
          WHERE ps.student_id = $student_id AND dp.date = '$today'";
$submissions_result = mysqli_query($conn, $query);
$submitted_problems = [];
while($row = mysqli_fetch_assoc($submissions_result)) {
    $submitted_problems[$row['problem_id']] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Problems - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/upload.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <section class="upload-hero">
            <div class="container">
                <h1>Daily Coding Problems</h1>
                <p>Solve today's problems and earn points!</p>
            </div>
        </section>

        <section class="upload-section">
            <div class="container">
                <?php if(isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="problems-grid">
                    <div class="platform-section">
                        <h2><i class="fab fa-linux"></i> LeetCode Problems</h2>
                        <div class="problems-list">
                            <?php 
                            mysqli_data_seek($problems_result, 0);
                            while($problem = mysqli_fetch_assoc($problems_result)): 
                                if($problem['platform'] !== 'leetcode') continue;
                                $is_submitted = isset($submitted_problems[$problem['id']]);
                                $submission = $is_submitted ? $submitted_problems[$problem['id']] : null;
                            ?>
                                <div class="problem-card <?php echo $problem['difficulty']; ?>">
                                    <div class="problem-header">
                                        <h3><?php echo htmlspecialchars($problem['problem_title']); ?></h3>
                                        <span class="difficulty-badge <?php echo $problem['difficulty']; ?>">
                                            <?php echo ucfirst($problem['difficulty']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="problem-details">
                                        <p><strong>Points:</strong> <?php echo $problem['points']; ?></p>
                                        <a href="<?php echo $problem['problem_url']; ?>" target="_blank" class="problem-link">
                                            <i class="fas fa-external-link-alt"></i> View Problem
                                        </a>
                                    </div>

                                    <?php if($is_submitted): ?>
                                        <div class="submission-status">
                                            <span class="status-<?php echo $submission['status']; ?>">
                                                <?php echo ucfirst($submission['status']); ?>
                                            </span>
                                            <?php if($submission['status'] === 'approved'): ?>
                                                <span class="points-earned">+<?php echo $submission['points_awarded']; ?> points</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <form method="POST" enctype="multipart/form-data" class="solution-form">
                                            <input type="hidden" name="problem_id" value="<?php echo $problem['id']; ?>">
                                            <div class="file-upload">
                                                <input type="file" id="solution_<?php echo $problem['id']; ?>" name="solution_file" accept=".cpp,.java,.py,.js,.c" required>
                                                <label for="solution_<?php echo $problem['id']; ?>" class="file-upload-btn">
                                                    <i class="fas fa-upload"></i> Choose Solution File
                                                </label>
                                            </div>
                                            <button type="submit" name="submit_solution" class="btn btn-primary">
                                                Submit Solution
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <div class="platform-section">
                        <h2><i class="fab fa-hackerrank"></i> HackerRank Problems</h2>
                        <div class="problems-list">
                            <?php 
                            mysqli_data_seek($problems_result, 0);
                            while($problem = mysqli_fetch_assoc($problems_result)): 
                                if($problem['platform'] !== 'hackerrank') continue;
                                $is_submitted = isset($submitted_problems[$problem['id']]);
                                $submission = $is_submitted ? $submitted_problems[$problem['id']] : null;
                            ?>
                                <div class="problem-card <?php echo $problem['difficulty']; ?>">
                                    <div class="problem-header">
                                        <h3><?php echo htmlspecialchars($problem['problem_title']); ?></h3>
                                        <span class="difficulty-badge <?php echo $problem['difficulty']; ?>">
                                            <?php echo ucfirst($problem['difficulty']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="problem-details">
                                        <p><strong>Points:</strong> <?php echo $problem['points']; ?></p>
                                        <a href="<?php echo $problem['problem_url']; ?>" target="_blank" class="problem-link">
                                            <i class="fas fa-external-link-alt"></i> View Problem
                                        </a>
                                    </div>

                                    <?php if($is_submitted): ?>
                                        <div class="submission-status">
                                            <span class="status-<?php echo $submission['status']; ?>">
                                                <?php echo ucfirst($submission['status']); ?>
                                            </span>
                                            <?php if($submission['status'] === 'approved'): ?>
                                                <span class="points-earned">+<?php echo $submission['points_awarded']; ?> points</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <form method="POST" enctype="multipart/form-data" class="solution-form">
                                            <input type="hidden" name="problem_id" value="<?php echo $problem['id']; ?>">
                                            <div class="file-upload">
                                                <input type="file" id="solution_hr_<?php echo $problem['id']; ?>" name="solution_file" accept=".cpp,.java,.py,.js,.c" required>
                                                <label for="solution_hr_<?php echo $problem['id']; ?>" class="file-upload-btn">
                                                    <i class="fas fa-upload"></i> Choose Solution File
                                                </label>
                                            </div>
                                            <button type="submit" name="submit_solution" class="btn btn-primary">
                                                Submit Solution
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <style>
        .problems-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-xxl);
        }
        
        .platform-section h2 {
            margin-bottom: var(--spacing-lg);
            color: var(--grey-900);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .problems-list {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }
        
        .problem-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--primary);
        }
        
        .problem-card.easy { border-left-color: var(--secondary); }
        .problem-card.medium { border-left-color: var(--accent); }
        .problem-card.hard { border-left-color: var(--danger); }
        
        .problem-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--spacing-md);
        }
        
        .problem-header h3 {
            margin: 0;
            color: var(--grey-900);
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
        
        .problem-details {
            margin-bottom: var(--spacing-md);
        }
        
        .problem-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .problem-link:hover {
            text-decoration: underline;
        }
        
        .submission-status {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .points-earned {
            background-color: var(--secondary-light);
            color: var(--secondary-dark);
            padding: 4px 8px;
            border-radius: var(--border-radius-md);
            font-size: var(--text-xs);
            font-weight: 500;
        }
        
        .solution-form {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        @media (max-width: 768px) {
            .problems-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
