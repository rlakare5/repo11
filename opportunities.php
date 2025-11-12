<?php
session_start();
include 'includes/config.php';

// Check if user is logged in
if(!isLoggedIn()) {
    redirect('login.php');
}

$department = $_SESSION['department'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opportunities - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/opportunities.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <section class="opportunities-hero">
            <div class="container">
                <div class="section-header">
                    <h1>Opportunities</h1>
                    <p>Discover internships, certifications, and project opportunities to boost your skills and career</p>
                    <div class="underline"></div>
                </div>
            </div>
        </section>
        
        <section class="opportunities-section">
            <div class="container">
                <div class="filter-controls">
                    <div class="search-box">
                        <input type="text" id="opportunity-search" placeholder="Search opportunities...">
                        <i class="fas fa-search"></i>
                    </div>
                    
                    <div class="filter-options">
                        <select id="opportunity-filter">
                            <option value="all">All Types</option>
                            <option value="internship">Internships</option>
                            <option value="certification">Certifications</option>
                            <option value="project">Projects</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="opportunities-grid">
                    <?php
                    // Get opportunities for student's department or all departments
                    $query = "SELECT * FROM opportunities 
                              WHERE (department = '$department' OR department = 'all') 
                              AND (expiry_date >= CURDATE() OR expiry_date IS NULL) 
                              ORDER BY created_at DESC";
                    $result = mysqli_query($conn, $query);
                    
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            echo '<div class="opportunity-card" data-type="'.$row['type'].'" data-title="'.strtolower($row['title']).'">';
                            
                            echo '<div class="opportunity-type-badge '.$row['type'].'">';
                            
                            switch($row['type']) {
                                case 'internship':
                                    echo '<i class="fas fa-briefcase"></i> Internship';
                                    break;
                                case 'certification':
                                    echo '<i class="fas fa-certificate"></i> Certification';
                                    break;
                                case 'project':
                                    echo '<i class="fas fa-project-diagram"></i> Project';
                                    break;
                                case 'other':
                                    echo '<i class="fas fa-star"></i> Opportunity';
                                    break;
                            }
                            
                            echo '</div>';
                            
                            echo '<h3>'.$row['title'].'</h3>';
                            echo '<p>'.substr($row['description'], 0, 150).'...</p>';
                            
                            if($row['expiry_date']) {
                                echo '<div class="opportunity-expiry">';
                                echo '<i class="far fa-calendar-alt"></i> Expires on '.date('M d, Y', strtotime($row['expiry_date']));
                                echo '</div>';
                            }
                            
                            echo '<a href="'.$row['link'].'" class="btn btn-primary" target="_blank">Apply Now</a>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="no-opportunities">No opportunities available right now. Check back later!</p>';
                    }
                    ?>
                </div>
            </div>
        </section>
        
        <section class="opportunity-resources">
            <div class="container">
                <h2>Useful Resources</h2>
                
                <div class="resources-grid">
                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h3>Learning Platforms</h3>
                        <ul>
                            <li><a href="https://www.coursera.org/" target="_blank">Coursera</a></li>
                            <li><a href="https://www.udemy.com/" target="_blank">Udemy</a></li>
                            <li><a href="https://www.edx.org/" target="_blank">edX</a></li>
                            <li><a href="https://www.pluralsight.com/" target="_blank">Pluralsight</a></li>
                        </ul>
                    </div>
                    
                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3>Internship Portals</h3>
                        <ul>
                            <li><a href="https://internshala.com/" target="_blank">Internshala</a></li>
                            <li><a href="https://www.linkedin.com/jobs/" target="_blank">LinkedIn Jobs</a></li>
                            <li><a href="https://angel.co/jobs" target="_blank">AngelList</a></li>
                            <li><a href="https://www.indeed.com/" target="_blank">Indeed</a></li>
                        </ul>
                    </div>
                    
                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h3>Certification Providers</h3>
                        <ul>
                            <li><a href="https://www.microsoft.com/en-us/learning/certification-overview.aspx" target="_blank">Microsoft</a></li>
                            <li><a href="https://cloud.google.com/certification" target="_blank">Google Cloud</a></li>
                            <li><a href="https://aws.amazon.com/certification/" target="_blank">AWS</a></li>
                            <li><a href="https://www.cisco.com/c/en/us/training-events/training-certifications/certifications.html" target="_blank">Cisco</a></li>
                        </ul>
                    </div>
                    
                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <h3>Project Ideas</h3>
                        <ul>
                            <li><a href="https://github.com/explore" target="_blank">GitHub Explore</a></li>
                            <li><a href="https://www.hackerearth.com/challenges/" target="_blank">HackerEarth Challenges</a></li>
                            <li><a href="https://www.kaggle.com/competitions" target="_blank">Kaggle Competitions</a></li>
                            <li><a href="https://devpost.com/hackathons" target="_blank">Devpost Hackathons</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Search functionality
        document.getElementById('opportunity-search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.opportunity-card');
            
            cards.forEach(card => {
                const title = card.dataset.title;
                if(title.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Filter functionality
        document.getElementById('opportunity-filter').addEventListener('change', function() {
            const filter = this.value;
            const cards = document.querySelectorAll('.opportunity-card');
            
            cards.forEach(card => {
                if(filter === 'all' || card.dataset.type === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>