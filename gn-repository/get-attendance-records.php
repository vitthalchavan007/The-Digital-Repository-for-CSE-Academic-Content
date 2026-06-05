<?php
require_once 'config.php';

if (!isLoggedIn() || !isFaculty()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$session_id = $_GET['session_id'] ?? 0;

if (!$session_id) {
    echo json_encode(['success' => false, 'error' => 'Session ID required']);
    exit;
}

// Verify faculty owns this session
$stmt = $pdo->prepare("
    SELECT cs.id FROM class_sessions cs
    JOIN faculty f ON cs.faculty_id = f.id
    WHERE cs.id = ? AND f.user_id = ?
");
$stmt->execute([$session_id, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        ar.id,
        ar.status,
        DATE_FORMAT(ar.marked_at, '%d-%m-%Y %h:%i %p') as marked_at,
        s.student_id,
        CONCAT(u.first_name, ' ', u.last_name) as student_name
    FROM attendance_records ar
    JOIN students s ON ar.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE ar.session_id = ?
    ORDER BY u.first_name ASC
");
$stmt->execute([$session_id]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'records' => $records]);
?>