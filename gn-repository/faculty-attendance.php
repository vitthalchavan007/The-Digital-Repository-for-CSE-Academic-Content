<?php
require_once 'config.php';

if (!isLoggedIn() || !isFaculty()) {
    redirect('faculty-login.php');
}

// Get faculty ID from session
$stmt = $pdo->prepare("SELECT id FROM faculty WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$faculty = $stmt->fetch(PDO::FETCH_ASSOC);
$faculty_id = $faculty['id'];

// Get subjects taught by this faculty
$stmt = $pdo->prepare("
    SELECT DISTINCT s.id, s.subject_code, s.subject_name, s.branch, s.semester 
    FROM subjects s 
    WHERE s.branch = ? 
    ORDER BY s.semester, s.subject_name
");
$stmt->execute([$_SESSION['department'] ?? 'CSE']);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle creating new session
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create_session') {
        $subject_id = $_POST['subject_id'] ?? 0;
        $lecture_topic = $_POST['lecture_topic'] ?? '';
        $lecture_date = $_POST['lecture_date'] ?? date('Y-m-d');
        $start_time = $_POST['start_time'] ?? date('H:i:s');
        $end_time = $_POST['end_time'] ?? null;
        
        // Get subject details
        $stmt = $pdo->prepare("SELECT branch, semester FROM subjects WHERE id = ?");
        $stmt->execute([$subject_id]);
        $subject = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("INSERT INTO class_sessions (faculty_id, subject_id, branch, year, semester, lecture_topic, lecture_date, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$faculty_id, $subject_id, $subject['branch'], $_SESSION['year'] ?? 3, $subject['semester'], $lecture_topic, $lecture_date, $start_time, $end_time]);
        
        $_SESSION['message'] = 'Class session created successfully!';
        redirect('faculty-attendance.php');
    }
    elseif ($_POST['action'] == 'mark_attendance') {
        $session_id = $_POST['session_id'] ?? 0;
        $attendance_data = $_POST['attendance'] ?? [];
        
        foreach ($attendance_data as $student_id => $status) {
            // Check if record exists
            $stmt = $pdo->prepare("SELECT id FROM attendance_records WHERE session_id = ? AND student_id = ?");
            $stmt->execute([$session_id, $student_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                $stmt = $pdo->prepare("UPDATE attendance_records SET status = ?, marked_by = ?, marked_at = NOW() WHERE session_id = ? AND student_id = ?");
                $stmt->execute([$status, $_SESSION['user_id'], $session_id, $student_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO attendance_records (session_id, student_id, status, marked_by, marked_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$session_id, $student_id, $status, $_SESSION['user_id']]);
            }
        }
        
        // Update summary
        updateAttendanceSummary($pdo, $session_id);
        
        // Mark session as completed
        $stmt = $pdo->prepare("UPDATE class_sessions SET status = 'completed' WHERE id = ?");
        $stmt->execute([$session_id]);
        
        $_SESSION['message'] = 'Attendance marked successfully!';
        redirect('faculty-attendance.php');
    }
    elseif ($_POST['action'] == 'update_session') {
        $session_id = $_POST['session_id'] ?? 0;
        $lecture_topic = $_POST['lecture_topic'] ?? '';
        $lecture_date = $_POST['lecture_date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE class_sessions SET lecture_topic = ?, lecture_date = ?, start_time = ?, end_time = ? WHERE id = ? AND faculty_id = ?");
        $stmt->execute([$lecture_topic, $lecture_date, $start_time, $end_time, $session_id, $faculty_id]);
        
        $_SESSION['message'] = 'Session updated successfully!';
        redirect('faculty-attendance.php');
    }
    elseif ($_POST['action'] == 'update_attendance') {
        $record_id = $_POST['record_id'] ?? 0;
        $new_status = $_POST['new_status'] ?? '';
        
        if ($record_id && $new_status) {
            $stmt = $pdo->prepare("UPDATE attendance_records SET status = ?, marked_by = ?, marked_at = NOW() WHERE id = ?");
            $stmt->execute([$new_status, $_SESSION['user_id'], $record_id]);
            
            // Get session_id from the record to update summary
            $stmt = $pdo->prepare("SELECT session_id FROM attendance_records WHERE id = ?");
            $stmt->execute([$record_id]);
            $record = $stmt->fetch();
            
            if ($record) {
                updateAttendanceSummary($pdo, $record['session_id']);
            }
            
            $_SESSION['message'] = 'Attendance record updated successfully!';
        }
        redirect('faculty-attendance.php');
    }
    elseif ($_POST['action'] == 'delete_attendance') {
        $record_id = $_POST['record_id'] ?? 0;
        
        // Get session_id before deleting
        $stmt = $pdo->prepare("SELECT session_id FROM attendance_records WHERE id = ?");
        $stmt->execute([$record_id]);
        $record = $stmt->fetch();
        
        if ($record) {
            $stmt = $pdo->prepare("DELETE FROM attendance_records WHERE id = ?");
            $stmt->execute([$record_id]);
            
            // Update summary
            updateAttendanceSummary($pdo, $record['session_id']);
            
            $_SESSION['message'] = 'Attendance record deleted successfully!';
        }
        redirect('faculty-attendance.php');
    }
    elseif ($_POST['action'] == 'delete_session') {
        $session_id = $_POST['session_id'] ?? 0;
        
        // Delete all attendance records first
        $stmt = $pdo->prepare("DELETE FROM attendance_records WHERE session_id = ?");
        $stmt->execute([$session_id]);
        
        // Delete the session
        $stmt = $pdo->prepare("DELETE FROM class_sessions WHERE id = ? AND faculty_id = ?");
        $stmt->execute([$session_id, $faculty_id]);
        
        $_SESSION['message'] = 'Session and all associated attendance records deleted successfully!';
        redirect('faculty-attendance.php');
    }
}

// Get upcoming sessions
$stmt = $pdo->prepare("
    SELECT cs.*, s.subject_name, s.subject_code 
    FROM class_sessions cs
    JOIN subjects s ON cs.subject_id = s.id
    WHERE cs.faculty_id = ? AND cs.lecture_date >= CURDATE() AND cs.status != 'completed'
    ORDER BY cs.lecture_date ASC, cs.start_time ASC
");
$stmt->execute([$faculty_id]);
$upcomingSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get past sessions
$stmt = $pdo->prepare("
    SELECT cs.*, s.subject_name, s.subject_code,
        (SELECT COUNT(*) FROM attendance_records WHERE session_id = cs.id) as marked_count
    FROM class_sessions cs
    JOIN subjects s ON cs.subject_id = s.id
    WHERE cs.faculty_id = ? AND (cs.lecture_date < CURDATE() OR cs.status = 'completed')
    ORDER BY cs.lecture_date DESC, cs.start_time DESC
    LIMIT 50
");
$stmt->execute([$faculty_id]);
$pastSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

function updateAttendanceSummary($pdo, $session_id) {
    // Get session details
    $stmt = $pdo->prepare("SELECT subject_id FROM class_sessions WHERE id = ?");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    $subject_id = $session['subject_id'];
    
    // Update summary for all students in this subject
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id as student_id
        FROM students s
        JOIN class_sessions cs ON cs.branch = s.branch AND cs.semester = s.semester
        WHERE cs.id = ?
    ");
    $stmt->execute([$session_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($students as $student) {
        // Calculate totals for this student-subject
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late
            FROM class_sessions cs
            JOIN attendance_records ar ON ar.session_id = cs.id
            WHERE cs.subject_id = ? AND ar.student_id = ?
        ");
        $stmt->execute([$subject_id, $student['student_id']]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $percentage = $stats['total'] > 0 ? ($stats['present'] / $stats['total']) * 100 : 0;
        
        // Insert or update summary
        $stmt = $pdo->prepare("
            INSERT INTO attendance_summary (student_id, subject_id, total_classes, present_count, absent_count, late_count, percentage)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            total_classes = ?, present_count = ?, absent_count = ?, late_count = ?, percentage = ?
        ");
        $stmt->execute([
            $student['student_id'], $subject_id, 
            $stats['total'], $stats['present'], $stats['absent'], $stats['late'], $percentage,
            $stats['total'], $stats['present'], $stats['absent'], $stats['late'], $percentage
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Attendance - GNIT Repository</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/dashboard.css">
  <style>
  .attendance-table {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 16px;
    overflow: hidden;
  }

  .attendance-table th {
    background: rgba(76, 201, 240, 0.1);
    padding: 15px;
    font-weight: 600;
  }

  .attendance-table td {
    padding: 12px 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }

  .status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
  }

  .status-present {
    background: rgba(40, 167, 69, 0.2);
    color: #28a745;
    border: 1px solid #28a745;
  }

  .status-absent {
    background: rgba(220, 53, 69, 0.2);
    color: #dc3545;
    border: 1px solid #dc3545;
  }

  .status-late {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
    border: 1px solid #ffc107;
  }

  .session-card {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 16px;
    padding: 15px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.05);
  }

  .session-card:hover {
    transform: translateY(-3px);
    border-color: var(--primary);
  }

  .quick-stats {
    background: linear-gradient(135deg, rgba(76, 201, 240, 0.1), rgba(114, 9, 183, 0.1));
    border-radius: 16px;
    padding: 20px;
    text-align: center;
  }

  .action-buttons {
    display: flex;
    gap: 5px;
  }

  .action-buttons .btn-sm {
    padding: 4px 8px;
    font-size: 11px;
  }

  .edit-attendance {
    cursor: pointer;
  }

  .edit-attendance:hover {
    opacity: 0.8;
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
          style="width:35px; height:35px; background: var(--purple);">
          <i class="fas fa-chalkboard-teacher small"></i>
        </div>
        <span class="text-white me-3"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
        <a href="faculty-dashboard.php" class="btn btn-sm btn-outline-glow me-2">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="logout.php" class="btn btn-sm btn-danger rounded-pill px-3">
          <i class="fas fa-power-off"></i>
        </a>
      </div>
    </div>
  </nav>

  <div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-12 mb-4">
        <div class="glass-panel p-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
              <h2 class="fw-bold mb-1"><i class="fas fa-calendar-check text-primary me-2"></i>Live Class Attendance</h2>
              <p class="text-white-50 mb-0">Manage class sessions and mark student attendance</p>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-glow" data-bs-toggle="modal" data-bs-target="#createSessionModal">
                <i class="fas fa-plus-circle me-2"></i>New Class Session
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="quick-stats">
          <i class="fas fa-chalkboard fa-2x text-primary mb-2"></i>
          <h3 class="mb-0"><?php echo count($upcomingSessions) + count($pastSessions); ?></h3>
          <small class="text-white-50">Total Sessions</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="quick-stats">
          <i class="fas fa-clock fa-2x text-info mb-2"></i>
          <h3 class="mb-0"><?php echo count($upcomingSessions); ?></h3>
          <small class="text-white-50">Upcoming Sessions</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="quick-stats">
          <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
          <h3 class="mb-0">
            <?php echo count(array_filter($pastSessions, function($s) { return $s['status'] == 'completed'; })); ?></h3>
          <small class="text-white-50">Completed</small>
        </div>
      </div>
      <div class="col-md-3">
        <div class="quick-stats">
          <i class="fas fa-users fa-2x text-warning mb-2"></i>
          <h3 class="mb-0" id="totalStudents">--</h3>
          <small class="text-white-50">Students Enrolled</small>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Upcoming Sessions -->
      <div class="col-lg-6 mb-4">
        <div class="glass-panel p-4">
          <h5 class="fw-bold mb-3"><i class="fas fa-hourglass-half text-info me-2"></i>Upcoming Sessions</h5>
          <?php if (empty($upcomingSessions)): ?>
          <p class="text-center text-white-50 py-4">No upcoming sessions scheduled.</p>
          <?php else: ?>
          <?php foreach ($upcomingSessions as $session): ?>
          <div class="session-card">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($session['subject_name']); ?>
                  (<?php echo $session['subject_code']; ?>)</h6>
                <p class="small text-white-50 mb-1">
                  <i class="fas fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($session['lecture_date'])); ?>
                  <?php if ($session['start_time']): ?>
                  | <i class="fas fa-clock me-1"></i> <?php echo date('h:i A', strtotime($session['start_time'])); ?>
                  <?php endif; ?>
                </p>
                <?php if ($session['lecture_topic']): ?>
                <p class="small text-white-50 mb-0">Topic: <?php echo htmlspecialchars($session['lecture_topic']); ?>
                </p>
                <?php endif; ?>
              </div>
              <div class="action-buttons">
                <?php if ($session['lecture_date'] == date('Y-m-d')): ?>
                <button class="btn btn-sm btn-glow"
                  onclick="markAttendance(<?php echo $session['id']; ?>, '<?php echo addslashes($session['subject_name']); ?>')">
                  <i class="fas fa-check-circle me-1"></i> Mark
                </button>
                <?php else: ?>
                <button class="btn btn-sm btn-outline-secondary" disabled>
                  <i class="fas fa-clock me-1"></i> Scheduled
                </button>
                <?php endif; ?>
                <button class="btn btn-sm btn-outline-info"
                  onclick="editSession(<?php echo htmlspecialchars(json_encode($session)); ?>)">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger"
                  onclick="deleteSession(<?php echo $session['id']; ?>, '<?php echo addslashes($session['subject_name']); ?>')">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Recent Sessions -->
      <div class="col-lg-6 mb-4">
        <div class="glass-panel p-4">
          <h5 class="fw-bold mb-3"><i class="fas fa-history text-warning me-2"></i>Recent Sessions</h5>
          <?php if (empty($pastSessions)): ?>
          <p class="text-center text-white-50 py-4">No past sessions found.</p>
          <?php else: ?>
          <?php foreach (array_slice($pastSessions, 0, 5) as $session): ?>
          <div class="session-card">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($session['subject_name']); ?></h6>
                <p class="small text-white-50 mb-1">
                  <i class="fas fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($session['lecture_date'])); ?>
                </p>
                <p class="small text-white-50 mb-0">
                  <i class="fas fa-users me-1"></i> Marked: <?php echo $session['marked_count']; ?> students
                </p>
              </div>
              <div class="action-buttons">
                <span
                  class="status-badge <?php echo $session['status'] == 'completed' ? 'status-present' : 'status-late'; ?>">
                  <?php echo ucfirst($session['status']); ?>
                </span>
                <button class="btn btn-sm btn-outline-info" onclick="viewAttendance(<?php echo $session['id']; ?>)">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger"
                  onclick="deleteSession(<?php echo $session['id']; ?>, '<?php echo addslashes($session['subject_name']); ?>')">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Create Session Modal -->
  <div class="modal fade custom-modal" id="createSessionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="fw-bold"><i class="fas fa-plus-circle text-primary me-2"></i>Create New Class Session</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form method="POST">
            <input type="hidden" name="action" value="create_session">

            <div class="floating-input-group mb-3">
              <select name="subject_id" id="subject_id" required>
                <option value="" disabled selected></option>
                <?php foreach ($subjects as $subject): ?>
                <option value="<?php echo $subject['id']; ?>">
                  <?php echo $subject['subject_code'] . ' - ' . $subject['subject_name'] . ' (Sem ' . $subject['semester'] . ')'; ?>
                </option>
                <?php endforeach; ?>
              </select>
              <label>Select Subject *</label>
            </div>

            <div class="floating-input-group mb-3">
              <input type="text" name="lecture_topic" id="lecture_topic" placeholder=" ">
              <label>Lecture Topic (Optional)</label>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="floating-input-group mb-3">
                  <input type="date" name="lecture_date" id="lecture_date" value="<?php echo date('Y-m-d'); ?>"
                    required>
                  <label>Lecture Date *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group mb-3">
                  <input type="time" name="start_time" id="start_time" value="<?php echo date('H:i'); ?>">
                  <label>Start Time</label>
                </div>
              </div>
            </div>

            <div class="floating-input-group mb-3">
              <input type="time" name="end_time" id="end_time">
              <label>End Time</label>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-glow flex-grow-1">Create Session</button>
              <button type="button" class="btn btn-outline-glow" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Mark Attendance Modal -->
  <div class="modal fade custom-modal" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="fw-bold" id="attendanceModalTitle">Mark Attendance</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form method="POST" id="attendanceForm">
            <input type="hidden" name="action" value="mark_attendance">
            <input type="hidden" name="session_id" id="attendance_session_id">

            <div class="table-responsive">
              <table class="attendance-table w-100">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Attendance Status</th>
                  </tr>
                </thead>
                <tbody id="attendanceStudentsList">
                  <tr>
                    <td colspan="4" class="text-center">Loading students...</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mt-4 d-flex gap-2">
              <button type="button" class="btn btn-outline-info" onclick="markAllPresent()">
                <i class="fas fa-check-double me-1"></i> Mark All Present
              </button>
              <button type="submit" class="btn btn-glow flex-grow-1">
                <i class="fas fa-save me-2"></i> Save Attendance
              </button>
              <button type="button" class="btn btn-outline-glow" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Session Modal -->
  <div class="modal fade custom-modal" id="editSessionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="fw-bold">Edit Session</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form method="POST">
            <input type="hidden" name="action" value="update_session">
            <input type="hidden" name="session_id" id="edit_session_id">

            <div class="floating-input-group mb-3">
              <input type="text" name="lecture_topic" id="edit_lecture_topic" placeholder=" ">
              <label>Lecture Topic</label>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="floating-input-group mb-3">
                  <input type="date" name="lecture_date" id="edit_lecture_date" required>
                  <label>Lecture Date *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group mb-3">
                  <input type="time" name="start_time" id="edit_start_time">
                  <label>Start Time</label>
                </div>
              </div>
            </div>

            <div class="floating-input-group mb-3">
              <input type="time" name="end_time" id="edit_end_time">
              <label>End Time</label>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-glow flex-grow-1">Update Session</button>
              <button type="button" class="btn btn-outline-glow" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- View/Edit Attendance Modal -->
  <div class="modal fade custom-modal" id="viewAttendanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="fw-bold">Attendance Records</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="attendance-table w-100">
              <thead>
                <tr>
                  <th>Student Name</th>
                  <th>Student ID</th>
                  <th>Status</th>
                  <th>Marked At</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="viewAttendanceList">
                <tr>
                  <td colspan="5" class="text-center">Loading...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Individual Attendance Modal -->
  <div class="modal fade custom-modal" id="editAttendanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="fw-bold">Edit Attendance Record</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form method="POST">
            <input type="hidden" name="action" value="update_attendance">
            <input type="hidden" name="record_id" id="edit_record_id">

            <div class="mb-3">
              <label class="form-label text-white">Student: <span id="edit_student_name"></span></label>
            </div>

            <div class="floating-input-group mb-3">
              <select name="new_status" id="edit_status" required>
                <option value="present">✓ Present</option>
                <option value="absent">✗ Absent</option>
                <option value="late">⌛ Late</option>
              </select>
              <label>Select Status *</label>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-glow flex-grow-1">Update Status</button>
              <button type="button" class="btn btn-outline-glow" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Session Confirmation Modal -->
  <div class="modal fade custom-modal" id="deleteSessionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="fw-bold text-danger">Delete Session</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this session?</p>
          <p class="text-danger small">Warning: This will also delete all attendance records associated with this
            session!</p>
          <form method="POST">
            <input type="hidden" name="action" value="delete_session">
            <input type="hidden" name="session_id" id="delete_session_id">
            <div class="d-flex gap-2 mt-3">
              <button type="submit" class="btn btn-danger flex-grow-1">Yes, Delete Session</button>
              <button type="button" class="btn btn-outline-glow" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  let currentSessionId = null;
  let studentsData = [];

  function markAttendance(sessionId, subjectName) {
    currentSessionId = sessionId;
    document.getElementById('attendanceModalTitle').innerHTML =
      '<i class="fas fa-check-circle me-2"></i>Mark Attendance - ' + subjectName;
    document.getElementById('attendance_session_id').value = sessionId;

    fetch('get-students-for-attendance.php?session_id=' + sessionId)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          studentsData = data.students;
          renderAttendanceTable(data.students);
        } else {
          document.getElementById('attendanceStudentsList').innerHTML =
            '<tr><td colspan="4" class="text-center text-danger">' + data.error + '</td></tr>';
        }
      })
      .catch(error => {
        document.getElementById('attendanceStudentsList').innerHTML =
          '<tr><td colspan="4" class="text-center text-danger">Error loading students</td></tr>';
      });

    new bootstrap.Modal(document.getElementById('attendanceModal')).show();
  }

  function renderAttendanceTable(students) {
    const tbody = document.getElementById('attendanceStudentsList');
    if (!students || students.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="4" class="text-center text-white-50">No students found for this session</td></tr>';
      return;
    }

    let html = '';
    students.forEach((student, index) => {
      const status = student.status || 'absent';
      html += `
            <tr>
                <td>${index + 1}</td>
                <td>${student.name}</td>
                <td>${student.student_id}</td>
                <td>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="attendance[${student.id}]" id="present_${student.id}" value="present" autocomplete="off" ${status === 'present' ? 'checked' : ''}>
                        <label class="btn btn-sm btn-outline-success" for="present_${student.id}">
                            <i class="fas fa-check"></i> Present
                        </label>
                        
                        <input type="radio" class="btn-check" name="attendance[${student.id}]" id="absent_${student.id}" value="absent" autocomplete="off" ${status === 'absent' ? 'checked' : ''}>
                        <label class="btn btn-sm btn-outline-danger" for="absent_${student.id}">
                            <i class="fas fa-times"></i> Absent
                        </label>
                        
                        <input type="radio" class="btn-check" name="attendance[${student.id}]" id="late_${student.id}" value="late" autocomplete="off" ${status === 'late' ? 'checked' : ''}>
                        <label class="btn btn-sm btn-outline-warning" for="late_${student.id}">
                            <i class="fas fa-clock"></i> Late
                        </label>
                    </div>
                </td>
             `;
    });
    tbody.innerHTML = html;
    document.getElementById('totalStudents').textContent = students.length;
  }

  function markAllPresent() {
    if (studentsData) {
      studentsData.forEach(student => {
        const presentRadio = document.getElementById(`present_${student.id}`);
        if (presentRadio) presentRadio.checked = true;
      });
    }
  }

  function editSession(session) {
    document.getElementById('edit_session_id').value = session.id;
    document.getElementById('edit_lecture_topic').value = session.lecture_topic || '';
    document.getElementById('edit_lecture_date').value = session.lecture_date;
    document.getElementById('edit_start_time').value = session.start_time || '';
    document.getElementById('edit_end_time').value = session.end_time || '';
    new bootstrap.Modal(document.getElementById('editSessionModal')).show();
  }

  function deleteSession(sessionId, subjectName) {
    if (confirm(
        `Are you sure you want to delete the session for ${subjectName}? This will also delete all attendance records!`
      )) {
      document.getElementById('delete_session_id').value = sessionId;
      new bootstrap.Modal(document.getElementById('deleteSessionModal')).show();
    }
  }

  function viewAttendance(sessionId) {
    fetch('get-attendance-records.php?session_id=' + sessionId)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const tbody = document.getElementById('viewAttendanceList');
          if (data.records.length === 0) {
            tbody.innerHTML =
              '运转<td colspan="5" class="text-center text-white-50">No attendance records found</td></tr>';
          } else {
            let html = '';
            data.records.forEach(record => {
              let statusClass = '';
              let statusText = '';
              if (record.status === 'present') {
                statusClass = 'status-present';
                statusText = 'Present';
              } else if (record.status === 'absent') {
                statusClass = 'status-absent';
                statusText = 'Absent';
              } else {
                statusClass = 'status-late';
                statusText = 'Late';
              }
              html += `
                             <tr>
                                <td>${record.student_name}</td>
                                <td>${record.student_id}</td>
                                <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                                <td>${record.marked_at}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary me-1" onclick="editAttendanceRecord(${record.id}, '${record.student_name}', '${record.status}')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteAttendanceRecord(${record.id}, '${record.student_name}')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                             </tr>`;
            });
            tbody.innerHTML = html;
          }
        } else {
          document.getElementById('viewAttendanceList').innerHTML =
            '运转<td colspan="5" class="text-center text-danger">' + data.error + '</td></tr>';
        }
      });
    new bootstrap.Modal(document.getElementById('viewAttendanceModal')).show();
  }

  function editAttendanceRecord(recordId, studentName, currentStatus) {
    document.getElementById('edit_record_id').value = recordId;
    document.getElementById('edit_student_name').textContent = studentName;
    document.getElementById('edit_status').value = currentStatus;
    new bootstrap.Modal(document.getElementById('editAttendanceModal')).show();
  }

  function deleteAttendanceRecord(recordId, studentName) {
    if (confirm(`Are you sure you want to delete attendance record for ${studentName}?`)) {
      var form = document.createElement('form');
      form.method = 'POST';
      form.action = 'faculty-attendance.php';

      var actionInput = document.createElement('input');
      actionInput.type = 'hidden';
      actionInput.name = 'action';
      actionInput.value = 'delete_attendance';

      var recordIdInput = document.createElement('input');
      recordIdInput.type = 'hidden';
      recordIdInput.name = 'record_id';
      recordIdInput.value = recordId;

      form.appendChild(actionInput);
      form.appendChild(recordIdInput);
      document.body.appendChild(form);
      form.submit();
    }
  }

  function downloadAttendancePDF(sessionId) {
    window.open('download-attendance-pdf.php?session_id=' + sessionId, '_blank');
  }
  </script>
</body>

</html>