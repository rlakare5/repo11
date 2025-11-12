<?php
session_start();
include 'includes/config.php';

// Handle contact form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Validate inputs
    if(empty($name) || empty($email) || empty($subject) || empty($message)) {
        setAlert('error', 'Please fill in all required fields.');
    } else {
        // Save message to database
        $query = "INSERT INTO contact_messages (name, email, phone, subject, message, created_at) 
                  VALUES ('$name', '$email', '$phone', '$subject', '$message', NOW())";
                  
        if(mysqli_query($conn, $query)) {
            setAlert('success', 'Your message has been sent. We will get back to you soon!');
            
            // Clear form data
            $name = $email = $phone = $subject = $message = '';
        } else {
            setAlert('error', 'Error sending message. Please try again later.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/contact.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <section class="contact-hero">
            <div class="container">
                <div class="section-header">
                    <h1>Let's Talk!</h1>
                    <p>Do you have any questions? Or maybe you'd like to discuss about a partnership?</p>
                    <p>Get in touch with us!</p>
                    <div class="underline"></div>
                </div>
            </div>
        </section>
        
        <section class="contact-form-section">
            <div class="container">
                <div class="contact-wrapper">
                    <div class="contact-image">
                        <img src="images/contact.webp" alt="Contact us">
                    </div>
                    
                    <div class="contact-form">
                        <?php displayAlert(); ?>
                        
                        <form method="POST" action="contact.php">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" required value="<?php echo isset($name) ? $name : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required value="<?php echo isset($email) ? $email : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo isset($phone) ? $phone : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Subject</label>
                                <input type="text" id="subject" name="subject" required value="<?php echo isset($subject) ? $subject : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Your Message</label>
                                <textarea id="message" name="message" rows="5" required><?php echo isset($message) ? $message : ''; ?></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Submit Message</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="contact-info">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Email</h3>
                        <p><a href="mailto:dsc@sanjivani.edu.in">dsc@sanjivani.edu.in</a></p>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>Address</h3>
                        <p>Sanjivani University, Kopargaon, Maharashtra, India</p>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h3>Phone</h3>
                        <p><a href="tel:+918767195562">+91 8767195562</a></p>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="map-section">
            <div class="container">
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3752.2385369825374!2d74.47271631490635!3d19.85736098664296!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bdc5ca09cd85b77%3A0x5a06b22e8dc38fc5!2sSanjivani%20College%20of%20Engineering%2C%20Kopargaon!5e0!3m2!1sen!2sin!4v1625851264883!5m2!1sen!2sin" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>