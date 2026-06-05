<?php
require_once 'config.php';

if (!isLoggedIn() || !isStudent()) {
    redirect('login.php');
}

$resource_id = $_GET['id'] ?? 0;

if ($resource_id) {
    $resource = getResourceById($pdo, $resource_id);
    
    if ($resource && $resource['file_path']) {
        // Increment download count
        incrementDownloadCount($pdo, $resource_id);
        
        $file = $resource['file_path'];
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            $_SESSION['error'] = 'File not found.';
        }
    } else {
        $_SESSION['error'] = 'Resource not found.';
    }
}

redirect('student-dashboard.php');
?>