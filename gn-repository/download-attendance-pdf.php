<?php
require_once 'config.php';

if (!isLoggedIn() || !isFaculty()) {
    redirect('faculty-login.php');
}

$session_id = $_GET['session_id'] ?? 0;

if (!$session_id) {
    die('Session ID required');
}

// Get session details
$stmt = $pdo->prepare("
    SELECT cs.*, s.subject_name, s.subject_code, f.faculty_id, u.first_name, u.last_name, u.email
    FROM class_sessions cs
    JOIN subjects s ON cs.subject_id = s.id
    JOIN faculty f ON cs.faculty_id = f.id
    JOIN users u ON f.user_id = u.id
    WHERE cs.id = ?
");
$stmt->execute([$session_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    die('Session not found');
}

// Verify this faculty owns this session
$stmt = $pdo->prepare("SELECT id FROM faculty WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$faculty = $stmt->fetch(PDO::FETCH_ASSOC);

if ($session['faculty_id'] != $faculty['id']) {
    die('Unauthorized access');
}

// Get all students with their attendance status
$stmt = $pdo->prepare("
    SELECT 
        s.student_id,
        CONCAT(u.first_name, ' ', u.last_name) as student_name,
        u.email,
        COALESCE(ar.status, 'not_marked') as status,
        ar.marked_at
    FROM students s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN attendance_records ar ON ar.student_id = s.id AND ar.session_id = ?
    WHERE s.branch = ? AND s.semester = ?
    ORDER BY u.first_name ASC
");
$stmt->execute([$session_id, $session['branch'], $session['semester']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$present_count = 0;
$absent_count = 0;
$late_count = 0;
$not_marked_count = 0;
$total_students = count($students);

foreach ($students as $student) {
    if ($student['status'] == 'present') $present_count++;
    elseif ($student['status'] == 'absent') $absent_count++;
    elseif ($student['status'] == 'late') $late_count++;
    else $not_marked_count++;
}

$attendance_percentage = $total_students > 0 ? round(($present_count / $total_students) * 100, 2) : 0;
$current_date = date('d-m-Y');
$current_time = date('h:i A');
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Attendance Report - <?php echo htmlspecialchars($session['subject_name']); ?></title>
  <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Segoe UI', 'Arial', sans-serif;
    background: #ffffff;
    padding: 40px;
  }

  .print-container {
    max-width: 1000px;
    margin: 0 auto;
    background: white;
  }

  .header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #2c3e50;
  }

  .institution-name {
    font-size: 22px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
  }

  .institution-address {
    font-size: 11px;
    color: #666;
    margin-bottom: 10px;
  }

  .report-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin-top: 15px;
  }

  .info-section {
    margin-bottom: 25px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
  }

  .info-table {
    width: 100%;
    border-collapse: collapse;
  }

  .info-table td {
    padding: 8px 12px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
  }

  .info-table td:first-child {
    font-weight: 600;
    width: 120px;
    background: #f9f9f9;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-bottom: 25px;
  }

  .stat-box {
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 10px;
    text-align: center;
    background: #fafafa;
  }

  .stat-number {
    font-size: 22px;
    font-weight: 700;
    color: #2c3e50;
  }

  .stat-label {
    font-size: 11px;
    color: #666;
    margin-top: 4px;
  }

  .section-title {
    font-size: 15px;
    font-weight: 600;
    color: #2c3e50;
    margin: 20px 0 12px 0;
    padding-bottom: 6px;
    border-bottom: 2px solid #e0e0e0;
  }

  .attendance-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
  }

  .attendance-table th {
    background: #f5f5f5;
    padding: 10px;
    text-align: left;
    font-weight: 600;
    border-bottom: 1px solid #ddd;
  }

  .attendance-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #f0f0f0;
  }

  .status-present {
    color: #27ae60;
    font-weight: 500;
  }

  .status-absent {
    color: #e74c3c;
    font-weight: 500;
  }

  .status-late {
    color: #f39c12;
    font-weight: 500;
  }

  .status-not-marked {
    color: #95a5a6;
  }

  .footer {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
    font-size: 11px;
    color: #666;
  }

  .signature-section {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
  }

  .signature-box {
    text-align: center;
    width: 180px;
  }

  .signature-line {
    border-top: 1px solid #333;
    margin-top: 40px;
    margin-bottom: 8px;
  }

  .print-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #2c3e50;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    z-index: 1000;
  }

  .print-btn:hover {
    background: #1a2632;
  }

  .text-center {
    text-align: center;
  }

  .text-success {
    color: #27ae60;
  }

  .text-danger {
    color: #e74c3c;
  }

  .text-warning {
    color: #f39c12;
  }

  @media print {
    .print-btn {
      display: none;
    }

    body {
      padding: 0;
    }
  }
  </style>
</head>

<body>
  <button class="print-btn" onclick="window.print();">
    <i class="fas fa-print"></i> Save as PDF
  </button>

  <div class="print-container">
    <div class="header">
      <div class="institution-name">GURU NANAK INSTITUTE OF TECHNOLOGY</div>
      <div class="institution-address">Dahegaon, Kalmeshwar Road, Nagpur - 441501</div>
      <div class="report-title">Class Attendance Report</div>
    </div>

    <div class="info-section">
      <table class="info-table">
        <tr>
          <td>Subject</td>
          <td colspan="3"><strong><?php echo htmlspecialchars($session['subject_name']); ?></strong>
            (<?php echo htmlspecialchars($session['subject_code']); ?>)</td>
        </tr>
        <tr>
          <td>Date & Time</td>
          <td><?php echo date('d-m-Y', strtotime($session['lecture_date'])); ?> |
            <?php echo $session['start_time'] ? date('h:i A', strtotime($session['start_time'])) : 'Not specified'; ?>
          </td>
          <td>Faculty</td>
          <td><?php echo htmlspecialchars($session['first_name'] . ' ' . $session['last_name']); ?>
            (<?php echo htmlspecialchars($session['faculty_id']); ?>)</td>
        </tr>
        <tr>
          <td>Branch</td>
          <td><?php echo htmlspecialchars($session['branch']); ?></td>
          <td>Semester</td>
          <td><?php echo $session['semester']; ?></td>
        </tr>
        <?php if ($session['lecture_topic']): ?>
        <tr>
          <td>Topic</td>
          <td colspan="3"><?php echo htmlspecialchars($session['lecture_topic']); ?></td>
        </tr>
        <?php endif; ?>
      </table>
    </div>

    <div class="stats-grid">
      <div class="stat-box">
        <div class="stat-number"><?php echo $total_students; ?></div>
        <div class="stat-label">Total Students</div>
      </div>
      <div class="stat-box">
        <div class="stat-number"><?php echo $present_count; ?></div>
        <div class="stat-label">Present</div>
      </div>
      <div class="stat-box">
        <div class="stat-number"><?php echo $absent_count; ?></div>
        <div class="stat-label">Absent</div>
      </div>
      <div class="stat-box">
        <div class="stat-number"><?php echo $late_count; ?></div>
        <div class="stat-label">Late</div>
      </div>
      <div class="stat-box">
        <div class="stat-number"><?php echo $attendance_percentage; ?>%</div>
        <div class="stat-label">Attendance</div>
      </div>
    </div>

    <div class="section-title">Student Attendance List</div>

    <table class="attendance-table">
      <thead>
        <tr>
          <th>S.No</th>
          <th>Student ID</th>
          <th>Student Name</th>
          <th>Email</th>
          <th>Status</th>
          <th>Marked At</th>
        </tr>
      </thead>
      <tbody>
        <?php $sno = 1; foreach ($students as $student): ?>
        <tr>
          <td><?php echo $sno++; ?></td>
          <td><?php echo htmlspecialchars($student['student_id']); ?></td>
          <td><?php echo htmlspecialchars($student['student_name']); ?></td>
          <td><?php echo htmlspecialchars($student['email']); ?></td>
          <td>
            <?php if ($student['status'] == 'present'): ?>
            <span class="status-present">✓ Present</span>
            <?php elseif ($student['status'] == 'absent'): ?>
            <span class="status-absent">✗ Absent</span>
            <?php elseif ($student['status'] == 'late'): ?>
            <span class="status-late">⌛ Late</span>
            <?php else: ?>
            <span class="status-not-marked">— Not Marked</span>
            <?php endif; ?>
          </td>
          <td><?php echo $student['marked_at'] ? date('d-m-Y h:i A', strtotime($student['marked_at'])) : '-'; ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="footer">
      <div class="signature-section">
        <div class="signature-box">
          <div class="signature-line"></div><strong>Faculty Signature</strong>
          <div style="font-size: 10px;">
            (<?php echo htmlspecialchars($session['first_name'] . ' ' . $session['last_name']); ?>)</div>
        </div>
        <div class="signature-box">
          <div class="signature-line"></div><strong>HOD Signature</strong>
          <div style="font-size: 10px;">(Department of <?php echo htmlspecialchars($session['branch']); ?>)</div>
        </div>
        <div class="signature-box">
          <div class="signature-line"></div><strong>Principal Signature</strong>
          <div style="font-size: 10px;">(GNIT, Nagpur)</div>
        </div>
      </div>
      <div class="text-center" style="margin-top: 20px;">Generated on:
        <?php echo $current_date . ' | ' . $current_time; ?></div>
    </div>
  </div>
</body>

</html>