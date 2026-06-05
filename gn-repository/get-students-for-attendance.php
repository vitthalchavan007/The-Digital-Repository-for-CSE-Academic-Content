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

// Get session details
$stmt = $pdo->prepare("SELECT branch, semester, subject_id FROM class_sessions WHERE id = ?");
$stmt->execute([$session_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    echo json_encode(['success' => false, 'error' => 'Session not found']);
    exit;
}

// Get students for this branch and semester
$stmt = $pdo->prepare("
    SELECT s.id, s.student_id, CONCAT(u.first_name, ' ', u.last_name) as name,
        COALESCE(ar.status, 'absent') as status
    FROM students s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN attendance_records ar ON ar.student_id = s.id AND ar.session_id = ?
    WHERE s.branch = ? AND s.semester = ?
    ORDER BY u.first_name ASC
");
$stmt->execute([$session_id, $session['branch'], $session['semester']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'students' => $students]);
?>