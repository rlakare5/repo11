<?php
session_start();
include 'includes/config.php';

// Check if user is logged in and is a student
if(!isLoggedIn() || $_SESSION['user_role'] !== 'student') {
    redirect('login.php');
}

$student_id = $_SESSION['user_id'];

// Get student data
$query = "SELECT * FROM students WHERE id = $student_id";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

// Get total points
$query = "SELECT SUM(points) as total_points FROM student_points WHERE student_id = $student_id";
$result = mysqli_query($conn, $query);
$total_points = mysqli_fetch_assoc($result)['total_points'] ?? 0;

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_no = sanitize($_POST['contact_no']);
    $linkedin_url = sanitize($_POST['linkedin_url']);
    $github_url = sanitize($_POST['github_url']);
    $leetcode_url = sanitize($_POST['leetcode_url']);
    $other_url = sanitize($_POST['other_url']);
    
    // Update profile image if uploaded
    $profile_image = $student['profile_image'];
    
    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if(!in_array($_FILES['profile_image']['type'], $allowed_types)) {
            setAlert('error', 'Only JPG, JPEG, and PNG files are allowed.');
        } elseif($_FILES['profile_image']['size'] > $max_size) {
            setAlert('error', 'File size must be less than 2MB.');
        } else {
$upload_dir = 'uploads/profiles/' . $_SESSION['prn'] . '/';
            
            // Create directory if it doesn't exist
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $filename = time() . '_' . $_FILES['profile_image']['name'];
            $target_file = $upload_dir . $filename;
            
            if(move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $profile_image = $filename;
                
                // Delete old profile image if exists
                if(!empty($student['profile_image']) && file_exists($upload_dir . $student['profile_image'])) {
                    unlink($upload_dir . $student['profile_image']);
                }
            } else {
                setAlert('error', 'Failed to upload profile image.');
            }
        }
    }
    
    // Update student data
    $query = "UPDATE students 
              SET contact_no = '$contact_no', 
                  profile_image = '$profile_image', 
                  linkedin_url = '$linkedin_url', 
                  github_url = '$github_url', 
                  leetcode_url = '$leetcode_url', 
                  other_url = '$other_url' 
              WHERE id = $student_id";
              
    if(mysqli_query($conn, $query)) {
        // Update session data
        $_SESSION['profile_image'] = $profile_image;
        
        setAlert('success', 'Profile updated successfully.');
        
        // Reload student data
        $query = "SELECT * FROM students WHERE id = $student_id";
        $result = mysqli_query($conn, $query);
        $student = mysqli_fetch_assoc($result);
    } else {
        setAlert('error', 'Error updating profile. Please try again.');
    }
}

// Get event participation
$query = "SELECT e.* FROM events e 
          JOIN event_participants ep ON e.id = ep.event_id 
          WHERE ep.student_id = $student_id 
          ORDER BY e.event_date DESC";
$events_result = mysqli_query($conn, $query);

// Get certifications
$query = "SELECT * FROM certifications WHERE student_id = $student_id ORDER BY issue_date DESC";
$certifications_result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <section class="profile-header-section">
            <div class="container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php
$studentImage = $student['profile_image'] ?? null;
$studentFirstName = $student['first_name'] ?? 'S';
$studentInitial = strtoupper(substr($studentFirstName, 0, 1));

// Default fallback image
$studentDefaultLogoPath = "images/default/default-avatar.png";

if (!$studentImage) {
    $stmt = $conn->prepare("SELECT image_path FROM default_logos WHERE letter = ?");
    $stmt->bind_param("s", $studentInitial);
    $stmt->execute();
    $stmt->bind_result($logoPath);
    if ($stmt->fetch()) {
        $studentDefaultLogoPath = $logoPath;
    }
    $stmt->close();
}
?>

<div class="avatar-container">
    <img src="<?php echo !empty($studentImage) ? 'uploads/profiles/'. $_SESSION['prn'] . '/'.$studentImage : htmlspecialchars($studentDefaultLogoPath); ?>" alt="Profile Avatar">
</div>

                        <div class="profile-stats">
                            <div class="stat-item">
                                <span class="stat-value"><?php echo $total_points; ?></span>
                                <span class="stat-label">Points</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value"><?php echo mysqli_num_rows($events_result); ?></span>
                                <span class="stat-label">Events</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value"><?php echo mysqli_num_rows($certifications_result); ?></span>
                                <span class="stat-label">Certifications</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-info">
                        <h1><?php echo $student['first_name'].' '.$student['middle_name'].' '.$student['last_name']; ?></h1>
                        
                        <div class="profile-meta">
                            <div class="meta-item">
                                <i class="fas fa-id-card"></i>
                                <span><?php echo $student['prn']; ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span><?php echo $departments[$student['department']].' - '.$years[$student['year']]; ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo $student['email']; ?></span>
                            </div>
                            <?php if(!empty($student['contact_no'])): ?>
                            <div class="meta-item">
                                <i class="fas fa-phone"></i>
                                <span><?php echo $student['contact_no']; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="profile-social">
                            <?php if(!empty($student['linkedin_url'])): ?>
                            <a href="<?php echo $student['linkedin_url']; ?>" target="_blank" class="social-link linkedin">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if(!empty($student['github_url'])): ?>
                            <a href="<?php echo $student['github_url']; ?>" target="_blank" class="social-link github">
                                <i class="fab fa-github"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if(!empty($student['leetcode_url'])): ?>
                            <a href="<?php echo $student['leetcode_url']; ?>" target="_blank" class="social-link leetcode">
                                <i class="fas fa-code"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if(!empty($student['other_url'])): ?>
                            <a href="<?php echo $student['other_url']; ?>" target="_blank" class="social-link website">
                                <i class="fas fa-globe"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="profile-actions">
                            <button id="edit-profile-btn" class="btn btn-primary">Edit Profile</button>
                            <a href="leaderboard.php" class="btn btn-outline">View Leaderboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="profile-content-section">
            <div class="container">
                <div class="profile-tabs">
                    <button class="tab-button active" data-tab="activity">Activity</button>
                    <button class="tab-button" data-tab="events">Events</button>
                    <button class="tab-button" data-tab="certifications">Certifications</button>
                    <button class="tab-button" data-tab="settings">Settings</button>
                </div>
                
                <div class="tab-content">
                    <div id="activity" class="tab-pane active">
                        <h2>Recent Activity</h2>
                        
                        <div class="activity-timeline">
                            <?php
                            // Get recent activity
                            $query = "SELECT 'point' as type, sp.points, sp.description, sp.date_added as date 
                                      FROM student_points sp
                                      WHERE sp.student_id = $student_id
                                      
                                      UNION ALL
                                      
                                      SELECT 'event' as type, 0 as points, e.title as description, ep.registration_date as date
                                      FROM event_participants ep
                                      JOIN events e ON ep.event_id = e.id
                                      WHERE ep.student_id = $student_id
                                      
                                      UNION ALL
                                      
                                      SELECT 'certification' as type, c.points, c.title as description, c.created_at as date
                                      FROM certifications c
                                      WHERE c.student_id = $student_id
                                      
                                      ORDER BY date DESC
                                      LIMIT 10";
                            $activity_result = mysqli_query($conn, $query);
                            
                            if(mysqli_num_rows($activity_result) > 0) {
                                while($activity = mysqli_fetch_assoc($activity_result)) {
                                    echo '<div class="timeline-item">';
                                    echo '<div class="timeline-icon">';
                                    
                                    switch($activity['type']) {
                                        case 'point':
                                            echo '<i class="fas fa-star"></i>';
                                            break;
                                        case 'event':
                                            echo '<i class="fas fa-calendar-check"></i>';
                                            break;
                                        case 'certification':
                                            echo '<i class="fas fa-certificate"></i>';
                                            break;
                                    }
                                    
                                    echo '</div>';
                                    echo '<div class="timeline-content">';
                                    echo '<div class="timeline-header">';
                                    
                                    switch($activity['type']) {
                                        case 'point':
                                            echo '<h3>Earned '.$activity['points'].' points</h3>';
                                            break;
                                        case 'event':
                                            echo '<h3>Registered for an event</h3>';
                                            break;
                                        case 'certification':
                                            echo '<h3>Uploaded a certification</h3>';
                                            break;
                                    }
                                    
                                    echo '<span>'.date('M d, Y', strtotime($activity['date'])).'</span>';
                                    echo '</div>';
                                    echo '<p>'.$activity['description'].'</p>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="no-data">No recent activity found.</p>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div id="events" class="tab-pane">
                        <h2>Events Participated</h2>
                        
                        <div class="events-grid">
                            <?php
                            mysqli_data_seek($events_result, 0);
                            
                            if(mysqli_num_rows($events_result) > 0) {
                                while($event = mysqli_fetch_assoc($events_result)) {
                                    echo '<div class="event-card">';
                                    echo '<div class="event-date">'.date('M d', strtotime($event['event_date'])).'</div>';
                                    echo '<h3>'.$event['title'].'</h3>';
                                    echo '<div class="event-meta">';
                                    echo '<span><i class="far fa-clock"></i> '.date('h:i A', strtotime($event['event_time'])).'</span>';
                                    echo '<span><i class="fas fa-map-marker-alt"></i> '.$event['location'].'</span>';
                                    echo '</div>';
                                    echo '<p>'.substr($event['description'], 0, 100).'...</p>';
                                    echo '<a href="events.php?id='.$event['id'].'" class="btn btn-small">View Details</a>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="no-data">You haven\'t participated in any events yet.</p>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div id="certifications" class="tab-pane">
                        <h2>Your Certifications</h2>
                        
                        <div class="certifications-grid">
                            <?php
                            mysqli_data_seek($certifications_result, 0);
                            
                            if(mysqli_num_rows($certifications_result) > 0) {
                                while($certification = mysqli_fetch_assoc($certifications_result)) {
                                    echo '<div class="certification-card">';
                                    echo '<div class="certification-image">';
                                    echo '<img src="uploads/certificates/'.$certification['certificate_image'].'" alt="'.$certification['title'].'">';
                                    echo '</div>';
                                    echo '<div class="certification-content">';
                                    echo '<h3>'.$certification['title'].'</h3>';
                                    echo '<p><strong>Issuer:</strong> '.$certification['issuer'].'</p>';
                                    echo '<p><strong>Issue Date:</strong> '.date('M d, Y', strtotime($certification['issue_date'])).'</p>';
                                    echo '<p><strong>Points:</strong> '.$certification['points'].'</p>';
                                    
                                    switch($certification['status']) {
                                        case 'pending':
                                            echo '<div class="certification-status pending">Pending Review</div>';
                                            break;
                                        case 'approved':
                                            echo '<div class="certification-status approved">Approved</div>';
                                            break;
                                        case 'rejected':
                                            echo '<div class="certification-status rejected">Rejected</div>';
                                            break;
                                    }
                                    
                                    echo '</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="no-data">You haven\'t uploaded any certifications yet.</p>';
                            }
                            ?>
                        </div>
                        
                        <div class="certifications-cta">
                            <a href="upload.php" class="btn btn-primary">Upload New Certificate</a>
                        </div>
                    </div>
                    
                    <div id="settings" class="tab-pane">
                        <h2>Account Settings</h2>
                        
                        <div class="settings-card">
                            <h3>Change Password</h3>
                            <p>To change your password, please visit the Change Password page.</p>
                            <a href="change_password.php" class="btn btn-outline">Change Password</a>
                        </div>
                        
                        <div class="settings-card">
                            <h3>Account Status</h3>
                            <p>Your account is currently <span class="status-<?php echo $student['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $student['is_active'] ? 'Active' : 'Inactive'; ?></span>.</p>
                            <p>If you need to change your account status, please contact an administrator.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Edit Profile Modal -->
    <div id="edit-profile-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Profile</h2>
            
            <?php displayAlert(); ?>
            
            <form method="POST" action="profile.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="profile_image">Profile Image</label>
                    <div class="file-upload">
                        <input type="file" id="profile_image" name="profile_image" accept=".jpg, .jpeg, .png">
                        <div class="file-upload-btn">
                            <i class="fas fa-upload"></i>
                            <span>Choose File</span>
                        </div>
                        <div class="file-name">No file chosen</div>
                    </div>
                    <div class="file-preview">
                        <img id="preview-image" src="<?php echo !empty($student['profile_image']) ? 'uploads/profiles/'. $_SESSION['prn'] . '/'.$student['profile_image'] : 'images/default-avatar.png'; ?>" alt="Preview">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="contact_no">Contact Number</label>
                    <input type="tel" id="contact_no" name="contact_no" value="<?php echo $student['contact_no']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="linkedin_url">LinkedIn URL</label>
                    <input type="url" id="linkedin_url" name="linkedin_url" value="<?php echo $student['linkedin_url']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="github_url">GitHub URL</label>
                    <input type="url" id="github_url" name="github_url" value="<?php echo $student['github_url']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="leetcode_url">LeetCode URL</label>
                    <input type="url" id="leetcode_url" name="leetcode_url" value="<?php echo $student['leetcode_url']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="other_url">Other URL</label>
                    <input type="url" id="other_url" name="other_url" value="<?php echo $student['other_url']; ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

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
        
        // Modal handling
        const modal = document.getElementById('edit-profile-modal');
        const btn = document.getElementById('edit-profile-btn');
        const span = document.getElementsByClassName('close')[0];
        
        // Open modal
        btn.onclick = function() {
            modal.style.display = 'block';
        }
        
        // Close modal
        span.onclick = function() {
            modal.style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        // File upload preview
        const fileInput = document.getElementById('profile_image');
        const fileName = document.querySelector('.file-name');
        const previewImage = document.getElementById('preview-image');
        
        fileInput.addEventListener('change', function() {
            if(this.files && this.files[0]) {
                const file = this.files[0];
                fileName.textContent = file.name;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                fileName.textContent = 'No file chosen';
            }
        });
        
        // Custom file input button
        const fileUploadBtn = document.querySelector('.file-upload-btn');
        fileUploadBtn.addEventListener('click', function() {
            fileInput.click();
        });
    </script>
</body>
</html>