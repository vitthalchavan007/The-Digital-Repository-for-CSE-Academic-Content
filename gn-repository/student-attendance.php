<?php
require_once 'config.php';

if (!isLoggedIn() || !isStudent()) {
    redirect('login.php');
}

// Get student ID
$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
$student_id = $student['id'];

// Get attendance summary by subject
$stmt = $pdo->prepare("
    SELECT 
        s.subject_name,
        s.subject_code,
        a.total_classes,
        a.present_count,
        a.absent_count,
        a.late_count,
        a.percentage
    FROM attendance_summary a
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.student_id = ?
    ORDER BY s.semester ASC, s.subject_name ASC
");
$stmt->execute([$student_id]);
$subjects_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent attendance records
$stmt = $pdo->prepare("
    SELECT 
        cs.lecture_date,
        cs.lecture_topic,
        s.subject_name,
        ar.status,
        ar.marked_at
    FROM attendance_records ar
    JOIN class_sessions cs ON ar.session_id = cs.id
    JOIN subjects s ON cs.subject_id = s.id
    WHERE ar.student_id = ?
    ORDER BY cs.lecture_date DESC
    LIMIT 20
");
$stmt->execute([$student_id]);
$recent_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate overall attendance percentage
$total_present = 0;
$total_classes = 0;
foreach ($subjects_attendance as $subj) {
    $total_present += $subj['present_count'];
    $total_classes += $subj['total_classes'];
}
$overall_percentage = $total_classes > 0 ? ($total_present / $total_classes) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Attendance - GNIT Repository</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
  .attendance-card {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
  }

  .attendance-card:hover {
    transform: translateY(-3px);
    border-color: var(--primary);
  }

  .progress-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(var(--primary) 0deg, rgba(255, 255, 255, 0.1) 0deg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
  }

  .attendance-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
  }

  .badge-present {
    background: rgba(40, 167, 69, 0.2);
    color: #28a745;
  }

  .badge-absent {
    background: rgba(220, 53, 69, 0.2);
    color: #dc3545;
  }

  .badge-late {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
  }
  </style>
</head>

<body>
  <div class="bg-animated">
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
  </div>

  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand" href="index.html">
        <i class="fas fa-atom me-2"></i>GNIT Repository
      </a>
      <div class="ms-auto d-flex align-items-center">
        <div class="bg-gradient rounded-circle me-2 d-flex align-items-center justify-content-center"
          style="width:35px; height:35px; background: var(--secondary);">
          <i class="fas fa-user-graduate small"></i>
        </div>
        <span class="text-white me-3"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
        <a href="student-dashboard.php" class="btn btn-sm btn-outline-glow me-2">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="logout.php" class="btn btn-sm btn-danger rounded-pill px-3">
          <i class="fas fa-power-off"></i>
        </a>
      </div>
    </div>
  </nav>

  <div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="row">
      <div class="col-12 mb-4">
        <div class="glass-panel p-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
              <h2 class="fw-bold mb-1"><i class="fas fa-calendar-check text-primary me-2"></i>My Attendance</h2>
              <p class="text-white-50 mb-0">Track your class attendance and performance</p>
            </div>
            <div class="text-center">
              <div class="progress-circle" id="attendanceCircle">
                <div class="text-center">
                  <h3 class="mb-0" id="overallPercentage"><?php echo round($overall_percentage); ?></h3>
                  <small class="text-white-50">%</small>
                </div>
              </div>
              <small class="text-white-50 mt-2 d-block">Overall Attendance</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Subject-wise Attendance -->
    <div class="row">
      <div class="col-lg-7 mb-4">
        <div class="glass-panel p-4">
          <h5 class="fw-bold mb-3"><i class="fas fa-chart-line text-primary me-2"></i> Attendance</h5>
          <div class="table-responsive">
            <table class="table table-dark table-hover">
              <thead>
                <tr>
                  <th>Subject</th>
                  <th>Code</th>
                  <th>Present</th>
                  <th>Absent</th>
                  <th>Late</th>
                  <th>Total</th>
                  <th>%</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($subjects_attendance)): ?>
                <tr>
                  <td colspan="7" class="text-center text-white-50">No attendance records found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($subjects_attendance as $subj): 
                                    $barColor = $subj['percentage'] >= 75 ? '#28a745' : ($subj['percentage'] >= 60 ? '#ffc107' : '#dc3545');
                                ?>
                <tr>
                  <td><?php echo htmlspecialchars($subj['subject_name']); ?></td>
                  <td><code><?php echo $subj['subject_code']; ?></code></td>
                  <td class="text-success"><?php echo $subj['present_count']; ?></td>
                  <td class="text-danger"><?php echo $subj['absent_count']; ?></td>
                  <td class="text-warning"><?php echo $subj['late_count']; ?></td>
                  <td><?php echo $subj['total_classes']; ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span
                        style="color: <?php echo $barColor; ?>; font-weight: 600;"><?php echo round($subj['percentage']); ?>%</span>
                      <div class="progress flex-grow-1" style="height: 6px; background: rgba(255,255,255,0.1);">
                        <div class="progress-bar" role="progressbar"
                          style="width: <?php echo $subj['percentage']; ?>%; background: <?php echo $barColor; ?>">
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Recent Attendance Records -->
      <div class="col-lg-5 mb-4">
        <div class="glass-panel p-4">
          <h5 class="fw-bold mb-3"><i class="fas fa-history text-info me-2"></i>Recent Attendance</h5>
          <?php if (empty($recent_records)): ?>
          <p class="text-center text-white-50 py-4">No recent attendance records</p>
          <?php else: ?>
          <div class="timeline">
            <?php foreach ($recent_records as $record): ?>
            <div class="attendance-card p-3 mb-3">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($record['subject_name']); ?></h6>
                  <p class="small text-white-50 mb-1">
                    <i class="fas fa-calendar me-1"></i>
                    <?php echo date('d M Y', strtotime($record['lecture_date'])); ?>
                  </p>
                  <?php if ($record['lecture_topic']): ?>
                  <p class="small text-white-50 mb-0">Topic: <?php echo htmlspecialchars($record['lecture_topic']); ?>
                  </p>
                  <?php endif; ?>
                </div>
                <span class="attendance-badge badge-<?php echo $record['status']; ?>">
                  <i
                    class="fas fa-<?php echo $record['status'] == 'present' ? 'check' : ($record['status'] == 'absent' ? 'times' : 'clock'); ?> me-1"></i>
                  <?php echo ucfirst($record['status']); ?>
                </span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Attendance Guidelines -->
    <div class="row">
      <div class="col-12">
        <div class="glass-panel p-4">
          <h5 class="fw-bold mb-3"><i class="fas fa-info-circle text-info me-2"></i>Attendance Guidelines</h5>
          <div class="row">
            <div class="col-md-4">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-success bg-opacity-25 p-3">
                  <i class="fas fa-check-circle text-success fa-2x"></i>
                </div>
                <div>
                  <h6 class="mb-0">Minimum 75% Required</h6>
                  <small class="text-white-50">For eligibility in semester exams</small>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-warning bg-opacity-25 p-3">
                  <i class="fas fa-clock text-warning fa-2x"></i>
                </div>
                <div>
                  <h6 class="mb-0">Late Entry Marked</h6>
                  <small class="text-white-50">If you arrive after 5 minutes</small>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-danger bg-opacity-25 p-3">
                  <i class="fas fa-envelope text-danger fa-2x"></i>
                </div>
                <div>
                  <h6 class="mb-0">Report Discrepancies</h6>
                  <small class="text-white-50">Contact faculty within 7 days</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  // Create progress circle
  const percentage = <?php echo $overall_percentage; ?>;
  const circle = document.getElementById('attendanceCircle');
  const angle = (percentage / 100) * 360;
  circle.style.background = `conic-gradient(var(--primary) 0deg ${angle}deg, rgba(255,255,255,0.1) ${angle}deg 360deg)`;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>