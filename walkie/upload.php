<?php
session_start();

if ($_FILES['audio']['error'] == 0) {
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $filename = "audio_" . time() . ".wav";
    move_uploaded_file($_FILES['audio']['tmp_name'], $upload_dir . $filename);

    // Store last uploaded file for the user (avoids hearing own playback)
    $_SESSION['last_uploaded'] = $filename;

    echo json_encode(["status" => "success", "filename" => $filename]);
} else {
    echo json_encode(["status" => "error", "message" => "File upload failed"]);
}
?>
