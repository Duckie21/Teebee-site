<?php
// Serve the IMG_6945.png from the current assets/img directory.
$imgPath = __DIR__ . DIRECTORY_SEPARATOR . 'IMG_6945.png';
if (!is_file($imgPath)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo 'Image not found.';
    exit;
}

header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');
readfile($imgPath);
exit;

