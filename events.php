<?php
session_start();
include 'includes/config.php';

// Check if viewing a specific event
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$single_event = false;
$event = null;

if($event_id > 0) {
    $query = "SELECT * FROM events WHERE id = $event_id";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) === 1) {
        $event = mysqli_fetch_assoc($result);
        $single_event = true;
    } else {
        redirect('events.php');
    }
}

// Handle event registration
if(isset($_POST['register']) && isLoggedIn() && $_SESSION['user_role'] === 'student') {
    $event_id = (int)$_POST['event_id'];
    $student_id = $_SESSION['user_id'];
    
    // Check if already registered
    $check_query = "SELECT * FROM event_participants WHERE event_id = $event_id AND student_id = $student_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) === 0) {
        // Register for the event
        $register_query = "INSERT INTO event_participants (event_id, student_id, registration_date) VALUES ($event_id, $student_id, NOW())";
        
        if(mysqli_query($conn, $register_query)) {
            // Add points for event participation
            $points_query = "INSERT INTO student_points (student_id, points, description, date_added) 
                            VALUES ($student_id, {$points['event_participation']}, 'Registered for event: {$event['title']}', NOW())";
            mysqli_query($conn, $points_query);
            
            setAlert('success', 'Successfully registered for the event!');
        } else {
            setAlert('error', 'Error registering for the event.');
        }
    } else {
        setAlert('error', 'You are already registered for this event.');
    }
    
    redirect("events.php?id=$event_id");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $single_event ? $event['title'] : 'Events'; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/events.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <?php if($single_event): ?>
            <!-- Single Event View -->
            <section class="event-detail">
                <div class="container">
                    <?php displayAlert(); ?>
                    
                    <div class="event-header">
                        <h1><?php echo $event['title']; ?></h1>
                        <div class="event-meta">
                            <div class="event-date">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                                <i class="far fa-clock"></i>
                                <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                            </div>
                            <div class="event-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo $event['location']; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="event-content">
                        <div class="event-image">
                            <img src="<?php echo !empty($event['image']) ? 'uploads/events/'.$event['image'] : 'images/event-default.jpg'; ?>" alt="<?php echo $event['title']; ?>">
                        </div>
                        
                        <div class="event-info">
                            <div class="event-description">
                                <h3>About this event</h3>
                                <p><?php echo nl2br($event['description']); ?></p>
                            </div>
                            
                            <?php if(!empty($event['speaker'])): ?>
                            <div class="event-speaker">
                                <h3>Speaker</h3>
                                <p><?php echo $event['speaker']; ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="event-actions">
                                <?php if(isLoggedIn() && $_SESSION['user_role'] === 'student'): ?>
                                    <?php
                                    // Check if already registered
                                    $check_query = "SELECT * FROM event_participants WHERE event_id = $event_id AND student_id = {$_SESSION['user_id']}";
                                    $check_result = mysqli_query($conn, $check_query);
                                    $is_registered = mysqli_num_rows($check_result) > 0;
                                    ?>
                                    
                                    <?php if($is_registered): ?>
                                        <button class="btn btn-success" disabled>Registered</button>
                                    <?php else: ?>
                                        <form method="POST" action="events.php?id=<?php echo $event_id; ?>">
                                            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                            <button type="submit" name="register" class="btn btn-primary">Register Now</button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if(!isLoggedIn()): ?>
                                        <a href="login.php" class="btn btn-primary">Login to Register</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <a href="events.php" class="btn btn-outline">Back to Events</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <!-- Events List View -->
            <section class="events-list">
                <div class="container">
                    <div class="section-header">
                        <h1>Our Events</h1>
                        <div class="underline"></div>
                    </div>
                    
                    <div class="filter-controls">
                        <div class="search-box">
                            <input type="text" id="event-search" placeholder="Search events...">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="filter-options">
                            <select id="event-filter">
                                <option value="all">All Events</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="past">Past</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="events-grid">
                        <?php
                        $query = "SELECT * FROM events ORDER BY event_date DESC";
                        $result = mysqli_query($conn, $query);
                        
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                $event_date = strtotime($row['event_date']);
                                $event_class = $event_date > time() ? 'upcoming' : 'past';
                                
                                echo '<div class="event-card '.$event_class.'" data-title="'.strtolower($row['title']).'">';
                                echo '<div class="event-image">';
                                echo '<img src="'.(!empty($row['image']) ? 'uploads/events/'.$row['image'] : 'images/event-default.jpg').'" alt="'.$row['title'].'">';
                                echo '<div class="event-date-badge">'.date('M d', $event_date).'</div>';
                                echo '</div>';
                                echo '<div class="event-details">';
                                echo '<h3>'.$row['title'].'</h3>';
                                echo '<div class="event-meta">';
                                echo '<div><i class="far fa-clock"></i> '.date('h:i A', strtotime($row['event_time'])).'</div>';
                                echo '<div><i class="fas fa-map-marker-alt"></i> '.$row['location'].'</div>';
                                echo '</div>';
                                echo '<p>'.substr($row['description'], 0, 100).'...</p>';
                                echo '<a href="events.php?id='.$row['id'].'" class="btn btn-small">View Details</a>';
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="no-events">No events available right now. Stay tuned!</p>';
                        }
                        ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <?php if(!$single_event): ?>
    <script>
        // Search functionality
        document.getElementById('event-search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const eventCards = document.querySelectorAll('.event-card');
            
            eventCards.forEach(card => {
                const title = card.dataset.title;
                if(title.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Filter functionality
        document.getElementById('event-filter').addEventListener('change', function() {
            const filter = this.value;
            const eventCards = document.querySelectorAll('.event-card');
            
            eventCards.forEach(card => {
                if(filter === 'all' || card.classList.contains(filter)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>