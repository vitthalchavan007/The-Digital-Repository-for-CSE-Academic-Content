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

// Get faculty details
$stmt = $pdo->prepare("
    SELECT u.first_name, u.last_name, u.email, f.faculty_id, f.department, f.subject 
    FROM faculty f 
    JOIN users u ON f.user_id = u.id 
    WHERE f.id = ?
");
$stmt->execute([$faculty_id]);
$faculty_info = $stmt->fetch(PDO::FETCH_ASSOC);

// Get all sessions for this faculty
$stmt = $pdo->prepare("
    SELECT 
        cs.*, 
        s.subject_name, 
        s.subject_code,
        (SELECT COUNT(*) FROM students WHERE branch = cs.branch AND semester = cs.semester) as total_students
    FROM class_sessions cs
    JOIN subjects s ON cs.subject_id = s.id
    WHERE cs.faculty_id = ?
    ORDER BY cs.lecture_date DESC
");
$stmt->execute([$faculty_id]);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate overall stats
$total_sessions = count($sessions);
$total_attendance_records = 0;
$total_present = 0;
$total_absent = 0;
$total_late = 0;
$subject_stats = [];

foreach ($sessions as $session) {
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
            COUNT(*) as total
        FROM attendance_records 
        WHERE session_id = ?
    ");
    $stmt->execute([$session['id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_attendance_records += $stats['total'];
    $total_present += $stats['present'];
    $total_absent += $stats['absent'];
    $total_late += $stats['late'];
    
    // Subject-wise stats
    $subject_key = $session['subject_name'];
    if (!isset($subject_stats[$subject_key])) {
        $subject_stats[$subject_key] = [
            'name' => $session['subject_name'],
            'code' => $session['subject_code'],
            'total_present' => 0,
            'total_absent' => 0,
            'total_late' => 0,
            'total_classes' => 0,
            'total_records' => 0
        ];
    }
    $subject_stats[$subject_key]['total_present'] += $stats['present'];
    $subject_stats[$subject_key]['total_absent'] += $stats['absent'];
    $subject_stats[$subject_key]['total_late'] += $stats['late'];
    $subject_stats[$subject_key]['total_records'] += $stats['total'];
    $subject_stats[$subject_key]['total_classes']++;
}

$overall_attendance_percentage = $total_attendance_records > 0 ? round(($total_present / $total_attendance_records) * 100, 2) : 0;
$current_date = date('d-m-Y');
$current_time = date('h:i A');
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Attendance Report -
    <?php echo htmlspecialchars($faculty_info['first_name'] . ' ' . $faculty_info['last_name']); ?></title>
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
    max-width: 1200px;
    margin: 0 auto;
    background: white;
  }

  /* Header */
  .header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #2c3e50;
  }

  .institution-name {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
  }

  .institution-address {
    font-size: 12px;
    color: #666;
    margin-bottom: 10px;
  }

  .report-title {
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
    margin-top: 15px;
  }

  /* Faculty Info Table */
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
    padding: 10px 15px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
  }

  .info-table td:first-child {
    font-weight: 600;
    width: 150px;
    background: #f9f9f9;
  }

  /* Stats Summary */
  .stats-summary {
    margin-bottom: 30px;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 15px;
    text-align: center;
  }

  .stat-box {
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 12px;
    background: #fafafa;
  }

  .stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
  }

  .stat-label {
    font-size: 11px;
    color: #666;
    margin-top: 5px;
  }

  /* Subject Summary Table */
  .section-title {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin: 25px 0 15px 0;
    padding-bottom: 8px;
    border-bottom: 2px solid #e0e0e0;
  }

  .data-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    font-size: 13px;
  }

  .data-table th {
    background: #f5f5f5;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #ddd;
  }

  .data-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #eee;
  }

  .data-table tr:hover {
    background: #fafafa;
  }

  /* Progress Bar */
  .progress-bar-container {
    width: 100%;
    height: 6px;
    background: #e0e0e0;
    border-radius: 3px;
    overflow: hidden;
  }

  .progress-fill {
    height: 100%;
    background: #2c3e50;
    border-radius: 3px;
  }

  /* Session Cards */
  .session-card {
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    margin-bottom: 25px;
    page-break-inside: avoid;
  }

  .session-header {
    background: #f9f9f9;
    padding: 12px 15px;
    border-bottom: 1px solid #e0e0e0;
  }

  .session-title {
    font-weight: 600;
    font-size: 15px;
    color: #2c3e50;
    margin-bottom: 5px;
  }

  .session-meta {
    font-size: 11px;
    color: #666;
  }

  .session-stats {
    padding: 12px 15px;
    background: #fafafa;
    border-bottom: 1px solid #e0e0e0;
    font-size: 12px;
  }

  .session-stats span {
    margin-right: 20px;
  }

  .attendance-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
  }

  .attendance-table th {
    background: #f5f5f5;
    padding: 10px 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 1px solid #ddd;
  }

  .attendance-table td {
    padding: 8px 12px;
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

  /* Footer */
  .footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
    font-size: 11px;
    color: #666;
  }

  .signature-section {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    padding-top: 20px;
  }

  .signature-box {
    text-align: center;
    width: 200px;
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
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  }

  .print-btn:hover {
    background: #1a2632;
  }

  .no-data {
    text-align: center;
    padding: 50px;
    color: #999;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
  }

  @media print {
    .print-btn {
      display: none;
    }

    body {
      padding: 0;
      background: white;
    }

    .session-card {
      break-inside: avoid;
    }

    .stat-box,
    .data-table,
    .info-section {
      break-inside: avoid;
    }
  }

  .text-right {
    text-align: right;
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

  .mb-1 {
    margin-bottom: 5px;
  }
  </style>
</head>

<body>
  <button class="print-btn" onclick="window.print();">
    <i class="fas fa-print"></i> Save as PDF / Print
  </button>

  <div class="print-container">
    <!-- Header -->
    <div class="header">
      <div class="institution-name">GURU NANAK INSTITUTE OF TECHNOLOGY</div>
      <div class="institution-address">Dahegaon, Kalmeshwar Road, Nagpur - 441501</div>
      <div class="institution-address">Approved by AICTE | Affiliated to RTMNU</div>
      <div class="report-title">Student Attendance Report</div>
    </div>

    <!-- Faculty Information -->
    <div class="info-section">
      <table class="info-table">
        <tr>
          <td>Faculty Name</td>
          <td>
            <strong><?php echo htmlspecialchars($faculty_info['first_name'] . ' ' . $faculty_info['last_name']); ?></strong>
          </td>
          <td>Faculty ID</td>
          <td><strong><?php echo htmlspecialchars($faculty_info['faculty_id']); ?></strong></td>
        </tr>
        <tr>
          <td>Department</td>
          <td><?php echo htmlspecialchars($faculty_info['department']); ?></td>
          <td>Subject</td>
          <td><?php echo htmlspecialchars($faculty_info['subject']); ?></td>
        </tr>
        <tr>
          <td>Email</td>
          <td><?php echo htmlspecialchars($faculty_info['email']); ?></td>
          <td>Report Date</td>
          <td><?php echo $current_date . ' | ' . $current_time; ?></td>
        </tr>
      </table>
    </div>

    <!-- Statistics Summary -->
    <div class="stats-summary">
      <div class="stats-grid">
        <div class="stat-box">
          <div class="stat-number"><?php echo $total_sessions; ?></div>
          <div class="stat-label">Total Sessions</div>
        </div>
        <div class="stat-box">
          <div class="stat-number"><?php echo $total_attendance_records; ?></div>
          <div class="stat-label">Records</div>
        </div>
        <div class="stat-box">
          <div class="stat-number"><?php echo $total_present; ?></div>
          <div class="stat-label">Present</div>
        </div>
        <div class="stat-box">
          <div class="stat-number"><?php echo $total_absent; ?></div>
          <div class="stat-label">Absent</div>
        </div>
        <div class="stat-box">
          <div class="stat-number"><?php echo $total_late; ?></div>
          <div class="stat-label">Late</div>
        </div>
        <div class="stat-box">
          <div class="stat-number"><?php echo $overall_attendance_percentage; ?>%</div>
          <div class="stat-label">Overall %</div>
        </div>
      </div>
    </div>

    <!-- Subject-wise Summary -->
    <?php if (!empty($subject_stats)): ?>
    <div class="section-title">Subject-wise Attendance Summary</div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Subject Code</th>
          <th>Subject Name</th>
          <th style="width: 60px;">Classes</th>
          <th style="width: 60px;">Present</th>
          <th style="width: 60px;">Absent</th>
          <th style="width: 60px;">Late</th>
          <th style="width: 100px;">Attendance %</th>
      </thead>
      <tbody>
        <?php foreach ($subject_stats as $subject): 
                    $subject_percentage = $subject['total_records'] > 0 ? round(($subject['total_present'] / $subject['total_records']) * 100, 2) : 0;
                ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($subject['code']); ?></strong></td>
          <td><?php echo htmlspecialchars($subject['name']); ?></td>
          <td><?php echo $subject['total_classes']; ?></td>
          <td class="text-success"><?php echo $subject['total_present']; ?></td>
          <td class="text-danger"><?php echo $subject['total_absent']; ?></td>
          <td class="text-warning"><?php echo $subject['total_late']; ?></td>
          <td>
            <div style="display: flex; align-items: center; gap: 8px;">
              <span style="font-weight: 500; min-width: 40px;"><?php echo $subject_percentage; ?>%</span>
              <div class="progress-bar-container" style="flex: 1;">
                <div class="progress-fill" style="width: <?php echo $subject_percentage; ?>%;"></div>
              </div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <!-- Session-wise Detailed Reports -->
    <div class="section-title">Session-wise Attendance Details</div>

    <?php if (empty($sessions)): ?>
    <div class="no-data">
      No attendance sessions found. Please create class sessions to mark attendance.
    </div>
    <?php else: ?>
    <?php foreach ($sessions as $session): 
            // Get attendance records for this session
            $stmt = $pdo->prepare("
                SELECT 
                    ar.status,
                    ar.marked_at,
                    s.student_id,
                    CONCAT(u.first_name, ' ', u.last_name) as student_name
                FROM attendance_records ar
                JOIN students s ON ar.student_id = s.id
                JOIN users u ON s.user_id = u.id
                WHERE ar.session_id = ?
                ORDER BY u.first_name ASC
            ");
            $stmt->execute([$session['id']]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate session stats
            $present = 0;
            $absent = 0;
            $late = 0;
            foreach ($records as $record) {
                if ($record['status'] == 'present') $present++;
                elseif ($record['status'] == 'absent') $absent++;
                elseif ($record['status'] == 'late') $late++;
            }
            $total = count($records);
            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        ?>
    <div class="session-card">
      <div class="session-header">
        <div class="session-title">
          <?php echo htmlspecialchars($session['subject_name']); ?>
          (<?php echo htmlspecialchars($session['subject_code']); ?>)
        </div>
        <div class="session-meta">
          Date: <?php echo date('d-m-Y', strtotime($session['lecture_date'])); ?> |
          Branch: <?php echo htmlspecialchars($session['branch']); ?> |
          Semester: <?php echo $session['semester']; ?>
          <?php if ($session['lecture_topic']): ?> | Topic:
          <?php echo htmlspecialchars($session['lecture_topic']); endif; ?>
        </div>
      </div>
      <div class="session-stats">
        <span>📊 Total: <?php echo $session['total_students']; ?> students</span>
        <span>✅ Present: <?php echo $present; ?></span>
        <span>❌ Absent: <?php echo $absent; ?></span>
        <span>⌛ Late: <?php echo $late; ?></span>
        <span>📈 Attendance: <?php echo $percentage; ?>%</span>
        <span>📝 Marked: <?php echo $total; ?> / <?php echo $session['total_students']; ?></span>
      </div>

      <table class="attendance-table">
        <thead>
          <tr>
            <th style="width: 50px;">#</th>
            <th>Student ID</th>
            <th>Student Name</th>
            <th style="width: 100px;">Status</th>
            <th style="width: 130px;">Marked At</th>
          </tr>
        </thead>
        <tbody>
          <?php 
                    $sno = 1;
                    // Get all students with their status
                    $stmt = $pdo->prepare("
                        SELECT 
                            s.student_id,
                            CONCAT(u.first_name, ' ', u.last_name) as student_name,
                            COALESCE(ar.status, 'not_marked') as status,
                            ar.marked_at
                        FROM students s
                        JOIN users u ON s.user_id = u.id
                        LEFT JOIN attendance_records ar ON ar.student_id = s.id AND ar.session_id = ?
                        WHERE s.branch = ? AND s.semester = ?
                        ORDER BY u.first_name ASC
                    ");
                    $stmt->execute([$session['id'], $session['branch'], $session['semester']]);
                    $all_students_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
          <?php foreach ($all_students_list as $student): ?>
          <tr>
            <td><?php echo $sno++; ?></td>
            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
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
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Footer with Signatures -->
    <div class="footer">
      <div class="signature-section">
        <div class="signature-box">
          <div class="signature-line"></div>
          <div><strong>Faculty Signature</strong></div>
          <div style="font-size: 11px; margin-top: 5px;">
            (<?php echo htmlspecialchars($faculty_info['first_name'] . ' ' . $faculty_info['last_name']); ?>)</div>
        </div>
        <div class="signature-box">
          <div class="signature-line"></div>
          <div><strong>HOD Signature</strong></div>
          <div style="font-size: 11px; margin-top: 5px;">(Department of
            <?php echo htmlspecialchars($faculty_info['department']); ?>)</div>
        </div>
        <div class="signature-box">
          <div class="signature-line"></div>
          <div><strong>Principal Signature</strong></div>
          <div style="font-size: 11px; margin-top: 5px;">(GNIT, Nagpur)</div>
        </div>
      </div>
      <div class="text-center" style="margin-top: 20px;">
        <p>This is a system-generated attendance report from GNIT Repository.</p>
        <p>Report ID: ATT-<?php echo $faculty_id; ?>-<?php echo date('YmdHis'); ?></p>
      </div>
    </div>
  </div>
</body>

</html>