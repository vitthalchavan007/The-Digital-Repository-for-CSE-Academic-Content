<?php
require_once 'config.php';

if (!isLoggedIn() || !isFaculty()) {
    redirect('faculty-login.php');
}

// Get faculty ID
$stmt = $pdo->prepare("SELECT id FROM faculty WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$faculty = $stmt->fetch(PDO::FETCH_ASSOC);
$faculty_id = $faculty['id'];

// Get all sessions for this faculty
$stmt = $pdo->prepare("
    SELECT cs.*, s.subject_name, s.subject_code
    FROM class_sessions cs
    JOIN subjects s ON cs.subject_id = s.id
    WHERE cs.faculty_id = ?
    ORDER BY cs.lecture_date DESC
");
$stmt->execute([$faculty_id]);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>All Attendance Reports</title>
  <style>
  body {
    font-family: Arial, sans-serif;
    padding: 20px;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  th,
  td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
  }

  th {
    background: #4cc9f0;
    color: white;
  }

  .btn {
    padding: 5px 10px;
    background: #4cc9f0;
    color: white;
    text-decoration: none;
    border-radius: 3px;
  }

  .btn:hover {
    background: #3ab8df;
  }
  </style>
</head>

<body>
  <h1>All Attendance Reports</h1>
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Subject</th>
        <th>Topic</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($sessions as $session): ?>
      <tr>
        <td><?php echo date('d-m-Y', strtotime($session['lecture_date'])); ?></td>
        <td><?php echo htmlspecialchars($session['subject_name']); ?></td>
        <td><?php echo htmlspecialchars($session['lecture_topic']) ?: '-'; ?></td>
        <td>
          <a href="download-attendance-pdf.php?session_id=<?php echo $session['id']; ?>" class="btn"
            target="_blank">Download PDF</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>

</html>