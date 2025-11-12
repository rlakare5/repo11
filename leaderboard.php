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

// Get student's rank
$query = "SELECT * FROM department_leaderboard WHERE id = $student_id";
$result = mysqli_query($conn, $query);
$student_rank = mysqli_fetch_assoc($result);

// Get university rank
$query = "SELECT university_rank FROM university_leaderboard WHERE id = $student_id";
$result = mysqli_query($conn, $query);
$university_rank = mysqli_fetch_assoc($result)['university_rank'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/leaderboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <section class="leaderboard-hero">
            <div class="container">
                <div class="section-header">
                    <h1>Leaderboard</h1>
                    <p>See how you rank among your peers</p>
                    <div class="underline"></div>
                </div>
            </div>
        </section>
        
        <section class="your-rank">
            <div class="container">
                <div class="rank-card">
                    <div class="rank-header">
                        <h2>Your Rank</h2>
                    </div>
                    
                    <div class="rank-details">
                        <div class="rank-item">
                            <div class="rank-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="rank-info">
                                <h3>Class Rank</h3>
                                <p class="rank-value"><?php echo $student_rank['rank_in_class']; ?></p>
                                <p class="rank-meta">among <?php echo $departments[$department]; ?> <?php echo $years[$year]; ?> students</p>
                            </div>
                        </div>
                        
                        <div class="rank-divider"></div>
                        
                        <div class="rank-item">
                            <div class="rank-icon rank-icon-university">
                                <i class="fas fa-university"></i>
                            </div>
                            <div class="rank-info">
                                <h3>University Rank</h3>
                                <p class="rank-value"><?php echo $university_rank; ?></p>
                                <p class="rank-meta">among all students</p>
                            </div>
                        </div>
                        
                        <div class="rank-divider"></div>
                        
                        <div class="rank-item">
                            <div class="rank-icon rank-icon-points">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="rank-info">
                                <h3>Total Points</h3>
                                <p class="rank-value"><?php echo $student_rank['total_points']; ?></p>
                                <p class="rank-meta">earned so far</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="leaderboard-tabs">
            <div class="container">
                <div class="tabs">
                    <button class="tab-button active" data-tab="class">Class Leaderboard</button>
                    <button class="tab-button" data-tab="department">Department Leaderboard</button>
                    <button class="tab-button" data-tab="university">University Leaderboard</button>
                </div>
                
                <div class="tab-content">
                    <div class="tab-pane active" id="class">
                        <div class="leaderboard-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Name</th>
                                        <th>Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get class leaderboard
                                    $query = "SELECT * FROM department_leaderboard 
                                              WHERE department = '$department' AND year = '$year' 
                                              ORDER BY rank_in_class 
                                              LIMIT 10";
                                    $result = mysqli_query($conn, $query);
                                    
                                    $rank = 1;
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $highlight = $row['id'] === $student_id ? 'class="highlight"' : '';
                                        
                                        echo '<tr '.$highlight.'>';
                                        echo '<td>';
                                        
                                        // Add medal for top 3
                                        if($rank === 1) {
                                            echo '<span class="medal gold"><i class="fas fa-medal"></i></span>';
                                        } elseif($rank === 2) {
                                            echo '<span class="medal silver"><i class="fas fa-medal"></i></span>';
                                        } elseif($rank === 3) {
                                            echo '<span class="medal bronze"><i class="fas fa-medal"></i></span>';
                                        } else {
                                            echo $rank;
                                        }
                                        
                                        echo '</td>';
                                        echo '<td>'.$row['name'].'</td>';
                                        echo '<td>'.$row['total_points'].'</td>';
                                        echo '</tr>';
                                        
                                        $rank++;
                                    }
                                    
                                    // If student is not in top 10, add them at the bottom
                                    if($student_rank['rank_in_class'] > 10) {
                                        echo '<tr class="leaderboard-divider">';
                                        echo '<td colspan="3">...</td>';
                                        echo '</tr>';
                                        
                                        echo '<tr class="highlight">';
                                        echo '<td>'.$student_rank['rank_in_class'].'</td>';
                                        echo '<td>'.$student_rank['name'].'</td>';
                                        echo '<td>'.$student_rank['total_points'].'</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane" id="department">
                        <div class="leaderboard-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Name</th>
                                        <th>Year</th>
                                        <th>Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get department leaderboard
                                    $query = "SELECT *, ROW_NUMBER() OVER (ORDER BY total_points DESC) as dept_rank 
                                              FROM department_leaderboard 
                                              WHERE department = '$department' 
                                              ORDER BY total_points DESC 
                                              LIMIT 10";
                                    $result = mysqli_query($conn, $query);
                                    
                                    $rank = 1;
                                    $student_dept_rank = 0;
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $highlight = $row['id'] === $student_id ? 'class="highlight"' : '';
                                        
                                        if($row['id'] === $student_id) {
                                            $student_dept_rank = $rank;
                                        }
                                        
                                        echo '<tr '.$highlight.'>';
                                        echo '<td>';
                                        
                                        // Add medal for top 3
                                        if($rank === 1) {
                                            echo '<span class="medal gold"><i class="fas fa-medal"></i></span>';
                                        } elseif($rank === 2) {
                                            echo '<span class="medal silver"><i class="fas fa-medal"></i></span>';
                                        } elseif($rank === 3) {
                                            echo '<span class="medal bronze"><i class="fas fa-medal"></i></span>';
                                        } else {
                                            echo $rank;
                                        }
                                        
                                        echo '</td>';
                                        echo '<td>'.$row['name'].'</td>';
                                        echo '<td>'.$row['year'].'</td>';
                                        echo '<td>'.$row['total_points'].'</td>';
                                        echo '</tr>';
                                        
                                        $rank++;
                                    }
                                    
                                    // If student is not in top 10, add them at the bottom
                                    if($student_dept_rank === 0) {
                                        // Get student's department rank
                                        $query = "SELECT COUNT(*) + 1 as dept_rank FROM department_leaderboard 
                                                  WHERE department = '$department' AND total_points > {$student_rank['total_points']}";
                                        $result = mysqli_query($conn, $query);
                                        $student_dept_rank = mysqli_fetch_assoc($result)['dept_rank'];
                                        
                                        echo '<tr class="leaderboard-divider">';
                                        echo '<td colspan="4">...</td>';
                                        echo '</tr>';
                                        
                                        echo '<tr class="highlight">';
                                        echo '<td>'.$student_dept_rank.'</td>';
                                        echo '<td>'.$student_rank['name'].'</td>';
                                        echo '<td>'.$student_rank['year'].'</td>';
                                        echo '<td>'.$student_rank['total_points'].'</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane" id="university">
                        <div class="leaderboard-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Year</th>
                                        <th>Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get university leaderboard
                                    $query = "SELECT * FROM university_leaderboard 
                                              ORDER BY university_rank 
                                              LIMIT 10";
                                    $result = mysqli_query($conn, $query);
                                    
                                    $rank = 1;
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $highlight = $row['id'] === $student_id ? 'class="highlight"' : '';
                                        
                                        echo '<tr '.$highlight.'>';
                                        echo '<td>';
                                        
                                        // Add medal for top 3
                                        if($rank === 1) {
                                            echo '<span class="medal gold"><i class="fas fa-medal"></i></span>';
                                        } elseif($rank === 2) {
                                            echo '<span class="medal silver"><i class="fas fa-medal"></i></span>';
                                        } elseif($rank === 3) {
                                            echo '<span class="medal bronze"><i class="fas fa-medal"></i></span>';
                                        } else {
                                            echo $rank;
                                        }
                                        
                                        echo '</td>';
                                        echo '<td>'.$row['name'].'</td>';
                                        echo '<td>'.$row['department'].'</td>';
                                        echo '<td>'.$row['year'].'</td>';
                                        echo '<td>'.$row['total_points'].'</td>';
                                        echo '</tr>';
                                        
                                        $rank++;
                                    }
                                    
                                    // If student is not in top 10, add them at the bottom
                                    if($university_rank > 10) {
                                        echo '<tr class="leaderboard-divider">';
                                        echo '<td colspan="5">...</td>';
                                        echo '</tr>';
                                        
                                        echo '<tr class="highlight">';
                                        echo '<td>'.$university_rank.'</td>';
                                        echo '<td>'.$student_rank['name'].'</td>';
                                        echo '<td>'.$student_rank['department'].'</td>';
                                        echo '<td>'.$student_rank['year'].'</td>';
                                        echo '<td>'.$student_rank['total_points'].'</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="point-breakdown">
            <div class="container">
                <h2>Your Point Breakdown</h2>
                
                <div class="breakdown-card">
                    <div class="breakdown-list">
                        <?php
                        // Get point breakdown
                        $query = "SELECT * FROM student_points WHERE student_id = $student_id ORDER BY date_added DESC";
                        $result = mysqli_query($conn, $query);
                        
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo '<div class="breakdown-item">';
                                echo '<div class="breakdown-info">';
                                echo '<h4>'.$row['description'].'</h4>';
                                echo '<span>'.date('M d, Y', strtotime($row['date_added'])).'</span>';
                                echo '</div>';
                                echo '<div class="breakdown-points">+'.$row['points'].'</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="no-data">No point history available.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="points-info">
            <div class="container">
                <h2>How to Earn Points</h2>
                
                <div class="points-grid">
                    <div class="points-card">
                        <div class="points-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3>Event Participation</h3>
                        <p>Attend DSC events to earn <?php echo $points['event_participation']; ?> points per event.</p>
                    </div>
                    
                    <div class="points-card">
                        <div class="points-icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h3>Hackathons</h3>
                        <p>Participate in hackathons to earn <?php echo $points['hackathon_participation']; ?> points.</p>
                    </div>
                    
                    <div class="points-card">
                        <div class="points-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3>Competitions</h3>
                        <p>Win competitions to earn <?php echo $points['competition_winner']; ?> points.</p>
                    </div>
                    
                    <div class="points-card">
                        <div class="points-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h3>Certifications</h3>
                        <p>Upload certifications to earn <?php echo $points['certification_basic']; ?>-<?php echo $points['certification_advanced']; ?> points based on level.</p>
                    </div>
                    
                    <div class="points-card">
                        <div class="points-icon">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <h3>Projects</h3>
                        <p>Complete projects to earn <?php echo $points['project_completion']; ?> points.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Tab switching
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and panes
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
                
                // Add active class to clicked button and corresponding pane
                button.classList.add('active');
                document.getElementById(button.dataset.tab).classList.add('active');
            });
        });
    </script>
</body>
</html>