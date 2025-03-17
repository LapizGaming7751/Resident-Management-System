<?php
session_start();

$upload_dir = "uploads/";
$files = array_diff(scandir($upload_dir, SCANDIR_SORT_DESCENDING), array('.', '..'));

if (!empty($files)) {
    $latest_file = reset($files);

    // Prevent user from hearing their own message
    if (isset($_SESSION['last_uploaded']) && $_SESSION['last_uploaded'] === $latest_file) {
        echo json_encode(["latest" => null]);
    } else {
        echo json_encode(["latest" => $latest_file]);
    }
} else {
    echo json_encode(["latest" => null]);
}
?>
