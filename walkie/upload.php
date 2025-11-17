<?php
session_start();

// Security: Check if user is logged in
if (!isset($_SESSION['type'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

// Security: Validate file upload
if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== 0) {
    echo json_encode(["status" => "error", "message" => "File upload failed"]);
    exit;
}

// Security: File validation
$allowed_types = ['audio/wav', 'audio/wave', 'audio/x-wav'];
$max_size = 10 * 1024 * 1024; // 10MB
$file_info = $_FILES['audio'];

// Check file type
if (!in_array($file_info['type'], $allowed_types)) {
    echo json_encode(["status" => "error", "message" => "Invalid file type. Only WAV files are allowed."]);
    exit;
}

// Check file size
if ($file_info['size'] > $max_size) {
    echo json_encode(["status" => "error", "message" => "File too large. Maximum size is 10MB."]);
    exit;
}

// Security: Validate file extension
$file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
if ($file_extension !== 'wav') {
    echo json_encode(["status" => "error", "message" => "Invalid file extension."]);
    exit;
}

// Security: Generate secure filename
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$filename = "audio_" . time() . "_" . bin2hex(random_bytes(8)) . ".wav";
$file_path = $upload_dir . $filename;

// Security: Move uploaded file
if (move_uploaded_file($file_info['tmp_name'], $file_path)) {
    // Store last uploaded file for the user (avoids hearing own playback)
    $_SESSION['last_uploaded'] = $filename;
    echo json_encode(["status" => "success", "filename" => $filename]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to save file"]);
}
?>
