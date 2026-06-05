<?php
// config.php
session_start();

$host = 'localhost';
$dbname = 'gn_repository';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isStudent() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'student';
}

function isFaculty() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'faculty';
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function uploadFile($file, $targetDir = 'uploads/') {
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
    $maxSize = 50 * 1024 * 1024; // 50MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        $_SESSION['error'] = 'Invalid file type. Only PDF, DOC, DOCX, PPT, PPTX allowed.';
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        $_SESSION['error'] = 'File size exceeds 50MB limit.';
        return false;
    }
    
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
    $targetPath = $targetDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath;
    }
    return false;
}

function getSubjectsByBranchSemester($pdo, $branch, $semester) {
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE branch = ? AND semester = ? ORDER BY subject_name");
    $stmt->execute([$branch, $semester]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllBranches() {
    return ['CSE', 'IT', 'AIDS', 'AIML', 'CSEDS', 'CE', 'ME', 'ECE'];
}

function getSemestersByYear($year) {
    $semesters = [
        1 => [1, 2],
        2 => [3, 4],
        3 => [5, 6],
        4 => [7, 8]
    ];
    return isset($semesters[$year]) ? $semesters[$year] : [];
}

// NEW: Get all subjects for dropdown
function getAllSubjects($pdo) {
    $stmt = $pdo->query("SELECT subject_code, subject_name, branch, semester FROM subjects ORDER BY branch, semester, subject_name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// NEW: Get resource by ID
function getResourceById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name FROM resources r 
                           JOIN users u ON r.author_id = u.id 
                           WHERE r.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// NEW: Get all resources with filters
function getAllResources($pdo, $filters = []) {
    $sql = "SELECT r.*, u.first_name, u.last_name FROM resources r 
            JOIN users u ON r.author_id = u.id 
            WHERE r.is_approved = 1";
    $params = [];
    
    if (!empty($filters['branch'])) {
        $sql .= " AND r.branch = ?";
        $params[] = $filters['branch'];
    }
    
    if (!empty($filters['year'])) {
        $sql .= " AND r.year = ?";
        $params[] = $filters['year'];
    }
    
    if (!empty($filters['semester'])) {
        $sql .= " AND r.semester = ?";
        $params[] = $filters['semester'];
    }
    
    if (!empty($filters['type'])) {
        $sql .= " AND r.resource_type = ?";
        $params[] = $filters['type'];
    }
    
    if (!empty($filters['subject'])) {
        $sql .= " AND r.subject = ?";
        $params[] = $filters['subject'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (r.title LIKE ? OR r.subject LIKE ? OR r.description LIKE ?)";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
    }
    
    $sql .= " ORDER BY r.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// NEW: Get resource stats
function getResourceStats($pdo, $branch, $year, $semester) {
    $stmt = $pdo->prepare("SELECT resource_type, COUNT(*) as count FROM resources 
                           WHERE branch = ? AND year = ? AND semester = ? AND is_approved = 1 
                           GROUP BY resource_type");
    $stmt->execute([$branch, $year, $semester]);
    $result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    return [
        'note' => $result['note'] ?? 0,
        'video' => $result['video'] ?? 0,
        'assignment' => $result['assignment'] ?? 0,
        'syllabus' => $result['syllabus'] ?? 0,
        'pyq' => $result['pyq'] ?? 0,
        'total' => array_sum($result)
    ];
}

// NEW: Increment download count
function incrementDownloadCount($pdo, $resourceId) {
    $stmt = $pdo->prepare("UPDATE resources SET downloads_count = downloads_count + 1 WHERE id = ?");
    $stmt->execute([$resourceId]);
}

// NEW: Get popular resources
function getPopularResources($pdo, $limit = 10) {
    $stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name FROM resources r 
                           JOIN users u ON r.author_id = u.id 
                           WHERE r.is_approved = 1 
                           ORDER BY r.downloads_count DESC, r.views_count DESC 
                           LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Add these functions at the end of config.php file

// NEW: Get resource by ID for faculty
function getFacultyResourceById($pdo, $id, $author_id) {
    $stmt = $pdo->prepare("SELECT * FROM resources WHERE id = ? AND author_id = ?");
    $stmt->execute([$id, $author_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// NEW: Update resource
function updateResource($pdo, $id, $data, $author_id) {
    $stmt = $pdo->prepare("UPDATE resources SET 
        title = ?, 
        resource_type = ?, 
        subject = ?, 
        subject_code = ?, 
        branch = ?, 
        year = ?, 
        semester = ?, 
        description = ?,
        video_url = ?,
        file_path = ?
        WHERE id = ? AND author_id = ?");
    
    return $stmt->execute([
        $data['title'],
        $data['resource_type'],
        $data['subject'],
        $data['subject_code'],
        $data['branch'],
        $data['year'],
        $data['semester'],
        $data['description'],
        $data['video_url'],
        $data['file_path'],
        $id,
        $author_id
    ]);
}

// NEW: Delete resource with file
function deleteResource($pdo, $id, $author_id) {
    // Get file path first
    $stmt = $pdo->prepare("SELECT file_path FROM resources WHERE id = ? AND author_id = ?");
    $stmt->execute([$id, $author_id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete the file if exists
    if ($resource && $resource['file_path'] && file_exists($resource['file_path'])) {
        unlink($resource['file_path']);
    }
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM resources WHERE id = ? AND author_id = ?");
    return $stmt->execute([$id, $author_id]);
}
?>