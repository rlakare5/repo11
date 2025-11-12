
<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and is admin
if(!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
}

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['action']) && $_POST['action'] === 'create') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $location = $_POST['location'];
        $speaker = $_POST['speaker'];
        $max_participants = $_POST['max_participants'];
        
        $query = "INSERT INTO events (title, description, event_date, event_time, location, speaker, max_participants, created_by) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssii", $title, $description, $event_date, $event_time, $location, $speaker, $max_participants, $_SESSION['user_id']);
        
        if($stmt->execute()) {
            setAlert('success', 'Event created successfully!');
        } else {
            setAlert('error', 'Failed to create event.');
        }
        $stmt->close();
    }
}

// Get all events
$query = "SELECT e.*, COUNT(ep.id) as participant_count 
          FROM events e 
          LEFT JOIN event_participants ep ON e.id = ep.event_id 
          GROUP BY e.id 
          ORDER BY e.event_date DESC";
$events = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management - Developer Student Club</title>
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
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="students.php">
                    <i class="fas fa-user-graduate"></i>
                    <span>Students</span>
                </a>
                <a href="events.php" class="active">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Events</span>
                </a>
                <a href="problems.php">
                    <i class="fas fa-code"></i>
                    <span>Daily Problems</span>
                </a>
                <a href="notifications.php">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
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
                    <input type="text" placeholder="Search events...">
                </div>
                
                <div class="header-profile">
                    <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <img src="../images/default-avatar.png" alt="Admin">
                </div>
            </header>

            <div class="admin-content">
                <?php displayAlerts(); ?>
                
                <div class="page-header">
                    <h1>Events Management</h1>
                    <button class="btn btn-primary" onclick="document.getElementById('create-modal').style.display='block'">
                        <i class="fas fa-plus"></i> Create Event
                    </button>
                </div>

                <div class="content-card">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Speaker</th>
                                    <th>Participants</th>
                                    <th>Max</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($event = mysqli_fetch_assoc($events)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($event['location']); ?></td>
                                    <td><?php echo htmlspecialchars($event['speaker']); ?></td>
                                    <td><?php echo $event['participant_count']; ?></td>
                                    <td><?php echo $event['max_participants'] ?: 'Unlimited'; ?></td>
                                    <td>
                                        <button class="btn btn-small btn-secondary" onclick="viewEvent(<?php echo $event['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-small btn-danger" onclick="deleteEvent(<?php echo $event['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Create Event Modal -->
    <div id="create-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Event</h2>
                <span class="close" onclick="document.getElementById('create-modal').style.display='none'">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="title">Event Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="event_date">Date:</label>
                        <input type="date" id="event_date" name="event_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_time">Time:</label>
                        <input type="time" id="event_time" name="event_time" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="speaker">Speaker:</label>
                        <input type="text" id="speaker" name="speaker">
                    </div>
                    
                    <div class="form-group">
                        <label for="max_participants">Max Participants:</label>
                        <input type="number" id="max_participants" name="max_participants" min="1">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('create-modal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>
