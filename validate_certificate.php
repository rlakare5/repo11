
<?php
// Standalone certificate validation API
// Can be called from any web host

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['image_base64']) || !isset($input['expected_title']) || !isset($input['expected_name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

// Decode base64 image
$imageData = base64_decode($input['image_base64']);
if (!$imageData) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid image data']);
    exit;
}

// Save temporary image
$tempFile = tempnam(sys_get_temp_dir(), 'cert_') . '.jpg';
file_put_contents($tempFile, $imageData);

try {
    // Perform validation
    $result = validateCertificateStandalone($tempFile, $input['expected_title'], $input['expected_name']);
    
    // Clean up
    unlink($tempFile);
    
    echo json_encode([
        'success' => true,
        'approved' => $result['approved'],
        'confidence' => $result['confidence'],
        'matches' => $result['matches']
    ]);
    
} catch (Exception $e) {
    // Clean up
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
    
    http_response_code(500);
    echo json_encode(['error' => 'Validation failed: ' . $e->getMessage()]);
}

function validateCertificateStandalone($imagePath, $expectedTitle, $expectedName) {
    $extractedText = extractTextStandalone($imagePath);
    
    if (!$extractedText) {
        return [
            'approved' => false,
            'confidence' => 0,
            'matches' => ['title' => false, 'name' => false]
        ];
    }
    
    $extractedText = strtolower($extractedText);
    $expectedTitle = strtolower($expectedTitle);
    $expectedName = strtolower($expectedName);
    
    // Title matching
    $titleWords = explode(' ', $expectedTitle);
    $titleMatches = 0;
    foreach ($titleWords as $word) {
        if (strlen($word) > 2 && strpos($extractedText, $word) !== false) {
            $titleMatches++;
        }
    }
    $titleMatch = $titleMatches >= ceil(count($titleWords) * 0.5);
    
    // Name matching
    $nameMatch = strpos($extractedText, $expectedName) !== false;
    
    $confidence = (($titleMatches / count($titleWords)) + ($nameMatch ? 1 : 0)) / 2;
    
    return [
        'approved' => $titleMatch && $nameMatch,
        'confidence' => round($confidence * 100, 2),
        'matches' => [
            'title' => $titleMatch,
            'name' => $nameMatch
        ]
    ];
}

function extractTextStandalone($imagePath) {
    // Try Tesseract first
    if (function_exists('exec')) {
        $output = [];
        $command = "tesseract " . escapeshellarg($imagePath) . " stdout 2>/dev/null";
        exec($command, $output, $return_code);
        
        if ($return_code === 0 && !empty($output)) {
            return implode(' ', $output);
        }
    }
    
    // Fallback to basic image analysis
    return basicImageAnalysis($imagePath);
}

function basicImageAnalysis($imagePath) {
    // This is a placeholder for basic image analysis
    // In a real implementation, you would integrate with online OCR APIs
    // like Google Cloud Vision API, Microsoft Computer Vision, etc.
    
    return ""; // Return empty for now, triggering manual review
}
?>
