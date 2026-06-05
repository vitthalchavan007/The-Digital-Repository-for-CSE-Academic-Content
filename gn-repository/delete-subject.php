<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('index.html');
}

$subject_id = $_POST['subject_id'] ?? 0;

if ($subject_id) {
    try {
        // First, check if there are resources using this subject
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM resources WHERE subject_id = ?");
        $stmt->execute([$subject_id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $_SESSION['error'] = "Cannot delete subject. It is used by $count resource(s).";
        } else {
            $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->execute([$subject_id]);
            $_SESSION['message'] = "Subject deleted successfully!";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Failed to delete subject: " . $e->getMessage();
    }
}

redirect('add-subjects.php');
?>