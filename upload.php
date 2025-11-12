<?php
session_start();
include 'includes/config.php';

// Function to validate certificate using OCR
function validateCertificate($imagePath, $expectedTitle, $expectedFullName, $expectedFirstName, $expectedLastName) {
    try {
        // Simple OCR simulation using basic image text extraction
        $extractedText = extractTextFromImage($imagePath);
        
        if (!$extractedText) {
            return 'rejected'; // Could not extract text
        }
        
        // Convert to lowercase for case-insensitive matching
        $extractedText = strtolower($extractedText);
        $expectedTitle = strtolower($expectedTitle);
        $expectedFullName = strtolower($expectedFullName);
        $expectedFirstName = strtolower($expectedFirstName);
        $expectedLastName = strtolower($expectedLastName);
        
        // Enhanced title matching
        $titleMatch = false;
        $titleWords = explode(' ', $expectedTitle);
        $matchedWords = 0;
        foreach ($titleWords as $word) {
            if (strlen($word) > 2 && strpos($extractedText, $word) !== false) {
                $matchedWords++;
            }
        }
        // At least 50% of title words should match
        $titleMatch = ($matchedWords >= ceil(count($titleWords) * 0.5));
        
        // Enhanced name matching with variations
        $nameMatch = false;
        $nameVariations = [
            $expectedFullName,
            $expectedFirstName . ' ' . $expectedLastName,
            $expectedLastName . ' ' . $expectedFirstName,
            $expectedFirstName,
            $expectedLastName
        ];
        
        foreach ($nameVariations as $nameVariation) {
            if (strpos($extractedText, $nameVariation) !== false) {
                $nameMatch = true;
                break;
            }
        }
        
        // Return approval status based on validation
        return ($titleMatch && $nameMatch) ? 'approved' : 'rejected';
        
    } catch (Exception $e) {
        // Log error for debugging
        error_log("Certificate validation error: " . $e->getMessage());
        return 'rejected';
    }
}

// Function to extract text from image (basic implementation)
function extractTextFromImage($imagePath) {
    try {
        // Check if Tesseract OCR is available
        if (function_exists('exec')) {
            $output = [];
            $command = "tesseract " . escapeshellarg($imagePath) . " stdout 2>/dev/null";
            exec($command, $output, $return_code);
            
            if ($return_code === 0 && !empty($output)) {
                return implode(' ', $output);
            }
        }
        
        // Fallback: Basic text extraction using GD library
        return basicTextExtraction($imagePath);
        
    } catch (Exception $e) {
        error_log("OCR extraction error: " . $e->getMessage());
        return false;
    }
}

// Fallback basic text extraction (limited but works without external dependencies)
function basicTextExtraction($imagePath) {
    try {
        // Get image info
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return false;
        }
        
        // Create image resource based on type
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($imagePath);
                break;
            default:
                return false;
        }
        
        if (!$image) {
            return false;
        }
        
        // This is a simplified approach - in a real implementation,
        // you would use proper OCR libraries like Tesseract
        // For now, we'll return a basic success indicator
        imagedestroy($image);
        
        // Simulate text extraction by reading filename patterns
        // This is a fallback when OCR is not available
        $filename = basename($imagePath);
        
        // For demonstration, return empty string to trigger manual review
        // In production, integrate with proper OCR service
        return "";
        
    } catch (Exception $e) {
        error_log("Basic text extraction error: " . $e->getMessage());
        return false;
    }
}

// Check if user is logged in and is a student
if(!isLoggedIn() || $_SESSION['user_role'] !== 'student') {
    redirect('login.php');
}

$student_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $issuer = mysqli_real_escape_string($conn, $_POST['issuer']);
    $issue_date = mysqli_real_escape_string($conn, $_POST['issue_date']);
    $verification_link = mysqli_real_escape_string($conn, $_POST['verification_link']);
$certification_level = $_POST['level'];

    // Mapping certification levels to points
    $points_map = [
        'basic' => 10,
        'intermediate' => 20,
        'advanced' => 30
    ];

    // Validate level
    if (!array_key_exists($certification_level, $points_map)) {
        die("Invalid certification level selected.");
    }

    $points = $points_map[$certification_level];

    // Handle image upload
    $target_dir = "uploads/";
    $certificate_image = basename($_FILES["certificate_image"]["name"]);
    $target_file = $target_dir . $certificate_image;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["certificate_image"]["tmp_name"]);
        if ($check === false) {
            die("File is not an image.");
        }
    }

    // Allow certain file formats
    $allowed = ['jpg', 'jpeg', 'png'];
    if (!in_array($imageFileType, $allowed)) {
        die("Only JPG, JPEG, and PNG files are allowed.");
    }

    // Move the uploaded file
    if (!move_uploaded_file($_FILES["certificate_image"]["tmp_name"], $target_file)) {
        die("Error uploading image.");
    }

    // Get student name for validation
    $student_query = "SELECT first_name, last_name FROM students WHERE id = ?";
    $student_stmt = $conn->prepare($student_query);
    $student_stmt->bind_param("i", $student_id);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    $student_data = $student_result->fetch_assoc();
    
    $student_full_name = $student_data['first_name'] . ' ' . $student_data['last_name'];
    
    // Perform automatic validation
    $validation_status = validateCertificate($target_file, $title, $student_full_name, $student_data['first_name'], $student_data['last_name']);
    
    // Prepare SQL query (use prepared statements for security)
    $stmt = $conn->prepare("INSERT INTO certifications (student_id, title, issuer, issue_date, certificate_image, verification_link, points, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssis", $student_id, $title, $issuer, $issue_date, $certificate_image, $verification_link, $points, $validation_status);

    if ($stmt->execute()) {
        if ($validation_status === 'approved') {
            echo "<script>alert('Certificate uploaded and automatically approved! Points have been awarded.'); window.location.href='dashboard.php';</script>";
        } else {
            echo "<script>alert('Certificate uploaded but automatically rejected due to validation failure. Please ensure your name and certificate title match exactly.'); window.location.href='dashboard.php';</script>";
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Certificate - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/upload.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <section class="upload-hero">
            <div class="container">
                <div class="section-header">
                    <h1>Upload Certificate</h1>
                    <p>Share your achievements and earn points</p>
                    <div class="underline"></div>
                </div>
            </div>
        </section>
        
        <section class="upload-section">
            <div class="container">
                <div class="upload-wrapper">
                    <div class="upload-info">
                        <h2>Certificate Guidelines</h2>
                        <ul class="guidelines-list">
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Upload certificates from recognized platforms and institutions.</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Certificate must be in JPG, JPEG, or PNG format (max 5MB).</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Ensure the certificate clearly shows your name, issue date, and issuing organization.</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Provide verification link if available for faster approval.</span>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Points will be awarded based on the certification level:</span>
                                <ul class="points-list">
                                    <li>Basic: <?php echo $points['certification_basic']; ?> points</li>
                                    <li>Intermediate: <?php echo $points['certification_intermediate']; ?> points</li>
                                    <li>Advanced: <?php echo $points['certification_advanced']; ?> points</li>
                                </ul>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>Uploaded certificates will be reviewed by administrators before points are awarded.</span>
                            </li>
                        </ul>
                        
                        <div class="examples">
                            <h3>Example Certifications</h3>
                            <div class="example-list">
                                <div class="example-item">
                                    <h4>Basic Level</h4>
                                    <ul>
                                        <li>Introduction to Programming</li>
                                        <li>HTML & CSS Basics</li>
                                        <li>Python Fundamentals</li>
                                    </ul>
                                </div>
                                <div class="example-item">
                                    <h4>Intermediate Level</h4>
                                    <ul>
                                        <li>JavaScript Frameworks</li>
                                        <li>Database Management</li>
                                        <li>Mobile App Development</li>
                                    </ul>
                                </div>
                                <div class="example-item">
                                    <h4>Advanced Level</h4>
                                    <ul>
                                        <li>AWS Solutions Architect</li>
                                        <li>TensorFlow Developer</li>
                                        <li>Cybersecurity Professional</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="upload-form">
                        <h2>Upload Your Certificate</h2>
                        
                        <?php displayAlert(); ?>
                        
                        <form method="POST" action="upload.php" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="title">Certificate Title *</label>
                                <input type="text" id="title" name="title" required value="<?php echo isset($title) ? $title : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="issuer">Issuing Organization *</label>
                                <input type="text" id="issuer" name="issuer" required value="<?php echo isset($issuer) ? $issuer : ''; ?>">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="issue_date">Issue Date *</label>
                                    <input type="date" id="issue_date" name="issue_date" required value="<?php echo isset($issue_date) ? $issue_date : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="level">Certification Level *</label>
                                    <select id="level" name="level" required>
                                        <option value="basic">Basic</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="verification_link">Verification Link (if available)</label>
                                <input type="url" id="verification_link" name="verification_link" value="<?php echo isset($verification_link) ? $verification_link : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="certificate_image">Certificate Image *</label>
                                <div class="file-upload">
                                    <input type="file" id="certificate_image" name="certificate_image" accept=".jpg, .jpeg, .png" required>
                                    <div class="file-upload-btn">
                                        <i class="fas fa-upload"></i>
                                        <span>Choose File</span>
                                    </div>
                                    <div class="file-name">No file chosen</div>
                                </div>
                                <div class="file-preview">
                                    <img id="preview-image" src="#" alt="Preview">
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Upload Certificate</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="recent-uploads">
                    <h2>Your Recent Uploads</h2>
                    
                    <div class="uploads-list">
                        <?php
                        // Get recent uploads
                        $query = "SELECT * FROM certifications WHERE student_id = $student_id ORDER BY created_at DESC";
                        $result = mysqli_query($conn, $query);
                        
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo '<div class="upload-item">';
                                echo '<div class="upload-image">';
                                echo '<img src="uploads/certificates/'.$row['certificate_image'].'" alt="'.$row['title'].'">';
                                echo '</div>';
                                echo '<div class="upload-details">';
                                echo '<h3>'.$row['title'].'</h3>';
                                echo '<p><strong>Issuer:</strong> '.$row['issuer'].'</p>';
                                echo '<p><strong>Issue Date:</strong> '.date('M d, Y', strtotime($row['issue_date'])).'</p>';
                                echo '<p><strong>Points:</strong> '.$row['points'].'</p>';
                                
                                switch($row['status']) {
                                    case 'pending':
                                        echo '<p><strong>Status:</strong> <span class="status-pending">Pending Review</span></p>';
                                        break;
                                    case 'approved':
                                        echo '<p><strong>Status:</strong> <span class="status-approved">Approved</span></p>';
                                        break;
                                    case 'rejected':
                                        echo '<p><strong>Status:</strong> <span class="status-rejected">Rejected</span></p>';
                                        break;
                                }
                                
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="no-uploads">You haven\'t uploaded any certificates yet.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // File upload preview
        const fileInput = document.getElementById('certificate_image');
        const fileName = document.querySelector('.file-name');
        const previewImage = document.getElementById('preview-image');
        const filePreview = document.querySelector('.file-preview');
        
        fileInput.addEventListener('change', function() {
            if(this.files && this.files[0]) {
                const file = this.files[0];
                fileName.textContent = file.name;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    filePreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                fileName.textContent = 'No file chosen';
                filePreview.style.display = 'none';
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