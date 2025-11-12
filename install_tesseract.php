
<?php
// Tesseract OCR Installation and Setup Script
// Run this script once to set up OCR capabilities

echo "Setting up Tesseract OCR for certificate validation...\n";

// Check if running on shared hosting
if (function_exists('exec')) {
    // Try to install tesseract if possible
    $commands = [
        'which tesseract',
        'tesseract --version'
    ];
    
    foreach ($commands as $cmd) {
        $output = [];
        $return_code = 0;
        exec($cmd, $output, $return_code);
        
        if ($return_code === 0) {
            echo "Tesseract is available: " . implode(' ', $output) . "\n";
            break;
        }
    }
    
    // If tesseract is not available, provide installation instructions
    if ($return_code !== 0) {
        echo "Tesseract OCR is not installed. For better accuracy, install it using:\n";
        echo "Ubuntu/Debian: sudo apt-get install tesseract-ocr\n";
        echo "CentOS/RHEL: sudo yum install tesseract\n";
        echo "Windows: Download from GitHub releases\n";
        echo "The system will use basic validation as fallback.\n";
    }
} else {
    echo "exec() function is disabled. Using basic validation method.\n";
}

echo "Setup complete. The certificate validation system is ready.\n";
?>
