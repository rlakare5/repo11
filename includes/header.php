<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, maximum-scale=5.0">
</head>
<header class="header">
    <div class="container">
        <div class="logo">
            <a href="index.php">
                <img src="images/logo.png" alt="DSC Logo">

            </a>
        </div>
        <nav class="nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="events.php">Events</a></li>

                <?php if(isLoggedIn() && $_SESSION['user_role'] === 'student'): ?>
                <li><a href="opportunities.php">Opportunities</a></li>
                <?php endif; ?>

                <li><a href="team.php">Team</a></li>
                <li><a href="contact.php">Contact</a></li>

                <?php if(isLoggedIn()): ?>
                <?php if($_SESSION['user_role'] !== 'student'): ?>
                <li><a href="admin/dashboard.php">Dashboard</a></li>
                <?php else: ?>
                <li><a href="notifications.php"><i class="fas fa-bell"></i></a></li>
                <li class="dropdown">
   <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php'; // update path to actual config file

$profileImage = $_SESSION['profile_image'] ?? null;
$firstName = $_SESSION['first_name'] ?? 'S';
$initial = strtoupper(substr($firstName, 0, 1));

// fallback logo if profile not uploaded
$defaultLogoPath = "images/default/default-avatar.png";

if (!$profileImage && isset($conn)) {
    $stmt = $conn->prepare("SELECT image_path FROM default_logos WHERE letter = ?");
    $stmt->bind_param("s", $initial);
    $stmt->execute();
    $stmt->bind_result($logoPath);
    if ($stmt->fetch()) {
        $defaultLogoPath = $logoPath;
    }
    $stmt->close();
}
?>

<a href="#" class="dropdown-toggle">
    <img src="<?php echo $profileImage ? 'uploads/profiles/' . htmlspecialchars($profileImage) : htmlspecialchars($defaultLogoPath); ?>" alt="Profile" class="profile-img-small">
</a>


                    <ul class="dropdown-menu">
                        <li><a href="profile.php">Your Profile</a></li>
                        <li><a href="leaderboard.php">Leaderboard</a></li>
                        <li><a href="daily-problems.php">Daily Problems</a></li>
                        <li><a href="upload.php">Upload Certificate</a></li>
                        <li><a href="change_password.php">Change Password</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php else: ?>
                <li><a href="login.php" class="btn btn-small">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <button class="menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>