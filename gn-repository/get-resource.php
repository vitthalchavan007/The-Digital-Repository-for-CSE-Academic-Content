<?php
require_once 'config.php';

if (!isLoggedIn() || !isStudent()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$resource_id = $_GET['id'] ?? 0;

if ($resource_id) {
    $resource = getResourceById($pdo, $resource_id);
    
    if ($resource) {
        // Increment view count
        $stmt = $pdo->prepare("UPDATE resources SET views_count = views_count + 1 WHERE id = ?");
        $stmt->execute([$resource_id]);
        
        echo json_encode($resource);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Resource not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid resource ID']);
}
?>