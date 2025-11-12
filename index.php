<?php
session_start();
include 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Student Club - Sanjivani University</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/creative-effects.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <!-- Hero Section -->
        <section class="hero">
    <div class="hero-content">
        <h1 class="typewriter-text">Developer Student Club</h1>
        <h2>Sanjivani University, Kopargaon</h2>
        <p>Learn. Connect. Build. Grow.</p>

        <!-- Connect Icons -->
        

        <?php if(!isset($_SESSION['user_id'])): ?>
        <div class="cta-buttons">
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="#about" class="btn btn-outline">Learn More</a>
        </div>
        <?php endif; ?>
    </div>
    <div class="hero-image">
        <img src="images/1.png" alt="DSC Illustration">
    </div>
</section>


        <!-- About Section -->
        <section id="about" class="about">
            <div class="section-header">
                <h2>About DSC</h2>
                <div class="underline"></div>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <h3>Our Vision</h3>
                    <p>Developer Student Clubs at Sanjivani University aims to bridge the gap between theory and practical application, helping students grow their knowledge in a peer-to-peer learning environment and build solutions for local businesses and the community.</p>
                    
                    <h3>Our Mission</h3>
                    <p>To provide students with the resources, opportunities, and experience necessary to be industry-ready developers and create impact through technology.</p>
                </div>
                <div class="about-image">
                    <img src="images/2.png" alt="About DSC">
                </div>
            </div>
        </section>

        <!-- Technologies Section -->
        <section class="technologies">
            <div class="section-header">
                <h2>Technologies We Focus On</h2>
                <div class="underline"></div>
            </div>
            <div class="tech-grid">
                <div class="tech-card">
                    <img src="images/web.svg" alt="Web Development">
                    <h3>Web Development</h3>
                    <p>HTML, CSS, JavaScript, React, Node.js, PHP, and more.</p>
                </div>
                <div class="tech-card">
                    <img src="images/app.svg" alt="Mobile Development">
                    <h3>Mobile Development</h3>
                    <p>Android, Flutter, React Native, and more.</p>
                </div>
                <div class="tech-card">
                    <img src="images/ml.svg" alt="Machine Learning">
                    <h3>Machine Learning</h3>
                    <p>TensorFlow, PyTorch, Scikit-learn, and more.</p>
                </div>
                <div class="tech-card">
                    <img src="images/gc.svg" alt="Cloud Computing">
                    <h3>Cloud Computing</h3>
                    <p>Google Cloud Platform, Firebase, AWS, and more.</p>
                </div>
            </div>
        </section>

        <!-- Why Join Section -->
        <section class="why-join">
            <div class="section-header">
                <h2>Why Join DSC?</h2>
                <div class="underline"></div>
            </div>
            <div class="reasons-grid">
                <div class="reason-card animate-on-scroll">
                    <div class="reason-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Community</h3>
                    <p>Be part of a global community of student developers.</p>
                </div>
                <div class="reason-card animate-on-scroll">
                    <div class="reason-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h3>Learn</h3>
                    <p>Gain practical development experience and enhance your skills.</p>
                </div>
                <div class="reason-card animate-on-scroll">
                    <div class="reason-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3>Build</h3>
                    <p>Work on real projects that solve real-world problems.</p>
                </div>
                <div class="reason-card animate-on-scroll">
                    <div class="reason-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Grow</h3>
                    <p>Develop leadership skills and advance your career.</p>
                </div>
            </div>
        </section>

        <!-- Latest Events Preview -->
        <section class="latest-events">
            <div class="section-header">
                <h2>Latest Events</h2>
                <div class="underline"></div>
            </div>
            <div class="events-preview">
                <?php
                // Get latest 3 events
                $query = "SELECT * FROM events ORDER BY event_date DESC LIMIT 3";
                $result = mysqli_query($conn, $query);
                
                if(mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="event-card">';
                        echo '<div class="event-date">'.date('d M', strtotime($row['event_date'])).'</div>';
                        echo '<h3>'.$row['title'].'</h3>';
                        echo '<p>'.substr($row['description'], 0, 100).'...</p>';
                        echo '<a href="events.php?id='.$row['id'].'" class="btn btn-small">Learn More</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="no-events">No events available right now. Stay tuned!</p>';
                }
                ?>
                <a href="events.php" class="btn btn-outline view-all">View All Events</a>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>