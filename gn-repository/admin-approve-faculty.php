<?php
require_once 'config.php';

// Check if admin is logged in
if (!isLoggedIn() || !isAdmin()) {
    redirect('index.html');
}

$message = '';
$error = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $faculty_user_id = $_POST['faculty_user_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    
    if ($action == 'approve') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ? AND user_type = 'faculty'");
        $stmt->execute([$faculty_user_id]);
        $message = 'Faculty approved successfully!';
    } elseif ($action == 'reject') {
        $reason = $_POST['reason'] ?? 'No reason provided';
        $stmt = $pdo->prepare("UPDATE users SET status = 'rejected', rejection_reason = ? WHERE id = ? AND user_type = 'faculty'");
        $stmt->execute([$reason, $faculty_user_id]);
        $message = 'Faculty registration rejected.';
    }
}

// Get all faculty with pending status
$stmt = $pdo->prepare("
    SELECT u.*, f.* 
    FROM users u 
    JOIN faculty f ON u.id = f.user_id 
    WHERE u.user_type = 'faculty' 
    ORDER BY FIELD(u.status, 'pending', 'approved', 'rejected'), u.created_at DESC
");
$stmt->execute();
$faculty_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count statistics
$pending_count = 0;
$approved_count = 0;
$rejected_count = 0;
foreach ($faculty_members as $faculty) {
    if ($faculty['status'] == 'pending') $pending_count++;
    elseif ($faculty['status'] == 'approved') $approved_count++;
    elseif ($faculty['status'] == 'rejected') $rejected_count++;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Approval - Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
  .status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
  }

  .status-pending {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
    border: 1px solid #ffc107;
  }

  .status-approved {
    background: rgba(40, 167, 69, 0.2);
    color: #28a745;
    border: 1px solid #28a745;
  }

  .status-rejected {
    background: rgba(220, 53, 69, 0.2);
    color: #dc3545;
    border: 1px solid #dc3545;
  }

  .faculty-card {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
  }

  .faculty-card:hover {
    transform: translateY(-3px);
    border-color: var(--primary);
  }

  .stats-card {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.05);
  }

  .nav-tabs .nav-link {
    color: rgba(255, 255, 255, 0.7);
    border: none;
    padding: 12px 24px;
    font-weight: 500;
  }

  .nav-tabs .nav-link.active {
    background: rgba(76, 201, 240, 0.1);
    color: var(--primary);
    border-bottom: 2px solid var(--primary);
  }

  .nav-tabs .nav-link:hover {
    color: white;
    border-color: transparent;
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
        <span class="text-white me-3">Admin: <?php echo htmlspecialchars($_SESSION['name']); ?></span>
        <a href="admin-dashboard.php" class="btn btn-sm btn-outline-glow me-2">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="logout.php" class="btn btn-sm btn-danger rounded-pill px-3">
          <i class="fas fa-power-off"></i>
        </a>
      </div>
    </div>
  </nav>

  <div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="stats-card">
          <i class="fas fa-clock fa-2x text-warning mb-2"></i>
          <h3 class="mb-0"><?php echo $pending_count; ?></h3>
          <small class="text-white-50">Pending Approvals</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card">
          <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
          <h3 class="mb-0"><?php echo $approved_count; ?></h3>
          <small class="text-white-50">Approved Faculty</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card">
          <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
          <h3 class="mb-0"><?php echo $rejected_count; ?></h3>
          <small class="text-white-50">Rejected Applications</small>
        </div>
      </div>
    </div>

    <!-- Faculty List -->
    <div class="glass-panel p-4">
      <h4 class="fw-bold mb-4">
        <i class="fas fa-users text-primary me-2"></i>Faculty Management
      </h4>

      <ul class="nav nav-tabs mb-4" id="facultyTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button"
            role="tab">
            <i class="fas fa-clock me-1"></i> Pending <span
              class="badge bg-warning ms-1"><?php echo $pending_count; ?></span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button"
            role="tab">
            <i class="fas fa-check-circle me-1"></i> Approved <span
              class="badge bg-success ms-1"><?php echo $approved_count; ?></span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button"
            role="tab">
            <i class="fas fa-times-circle me-1"></i> Rejected <span
              class="badge bg-danger ms-1"><?php echo $rejected_count; ?></span>
          </button>
        </li>
      </ul>

      <div class="tab-content">
        <!-- Pending Tab -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel">
          <?php
                    $pending_faculty = array_filter($faculty_members, function($f) { return $f['status'] == 'pending'; });
                    if (empty($pending_faculty)): ?>
          <div class="text-center text-white-50 py-5">
            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
            <p class="mb-0">No pending faculty registrations.</p>
          </div>
          <?php else: ?>
          <?php foreach ($pending_faculty as $faculty): ?>
          <div class="faculty-card">
            <div class="row align-items-start">
              <div class="col-md-7">
                <div class="d-flex align-items-center gap-3 mb-3">
                  <div class="rounded-circle bg-primary bg-opacity-25 d-flex align-items-center justify-content-center"
                    style="width: 50px; height: 50px;">
                    <i class="fas fa-user-tie fa-2x text-primary"></i>
                  </div>
                  <div>
                    <h5 class="fw-bold mb-0">
                      <?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?></h5>
                    <span class="status-badge status-pending">
                      <i class="fas fa-clock me-1"></i> Pending Approval
                    </span>
                  </div>
                </div>
                <div class="row g-3">
                  <div class="col-md-6">
                    <small class="text-white-50 d-block">Faculty ID</small>
                    <p class="mb-2"><strong><?php echo htmlspecialchars($faculty['faculty_id']); ?></strong></p>
                  </div>
                  <div class="col-md-6">
                    <small class="text-white-50 d-block">Email</small>
                    <p class="mb-2"><?php echo htmlspecialchars($faculty['email']); ?></p>
                  </div>
                  <div class="col-md-6">
                    <small class="text-white-50 d-block">Department</small>
                    <p class="mb-2"><?php echo htmlspecialchars($faculty['department']); ?></p>
                  </div>
                  <div class="col-md-6">
                    <small class="text-white-50 d-block">Primary Subject</small>
                    <p class="mb-2"><?php echo htmlspecialchars($faculty['subject']); ?></p>
                  </div>
                  <div class="col-12">
                    <small class="text-white-50 d-block">Registered On</small>
                    <p class="mb-0"><?php echo date('d M Y, h:i A', strtotime($faculty['created_at'])); ?></p>
                  </div>
                </div>
              </div>
              <div class="col-md-5 mt-3 mt-md-0">
                <div class="d-flex gap-2 justify-content-md-end">
                  <button class="btn btn-success"
                    onclick="approveFaculty(<?php echo $faculty['user_id']; ?>, '<?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?>')">
                    <i class="fas fa-check me-1"></i> Approve
                  </button>
                  <button class="btn btn-danger"
                    onclick="rejectFaculty(<?php echo $faculty['user_id']; ?>, '<?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?>')">
                    <i class="fas fa-times me-1"></i> Reject
                  </button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Approved Tab -->
        <div class="tab-pane fade" id="approved" role="tabpanel">
          <?php
                    $approved_faculty = array_filter($faculty_members, function($f) { return $f['status'] == 'approved'; });
                    if (empty($approved_faculty)): ?>
          <div class="text-center text-white-50 py-5">
            <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
            <p class="mb-0">No approved faculty members yet.</p>
          </div>
          <?php else: ?>
          <?php foreach ($approved_faculty as $faculty): ?>
          <div class="faculty-card">
            <div class="row align-items-start">
              <div class="col-md-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                  <div class="rounded-circle bg-success bg-opacity-25 d-flex align-items-center justify-content-center"
                    style="width: 50px; height: 50px;">
                    <i class="fas fa-user-check fa-2x text-success"></i>
                  </div>
                  <div>
                    <h5 class="fw-bold mb-0">
                      <?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?></h5>
                    <span class="status-badge status-approved">
                      <i class="fas fa-check-circle me-1"></i> Approved
                    </span>
                  </div>
                </div>
                <div class="row g-3">
                  <div class="col-md-4">
                    <small class="text-white-50 d-block">Faculty ID</small>
                    <p class="mb-2"><?php echo htmlspecialchars($faculty['faculty_id']); ?></p>
                  </div>
                  <div class="col-md-4">
                    <small class="text-white-50 d-block">Department</small>
                    <p class="mb-2"><?php echo htmlspecialchars($faculty['department']); ?></p>
                  </div>
                  <div class="col-md-4">
                    <small class="text-white-50 d-block">Subject</small>
                    <p class="mb-0"><?php echo htmlspecialchars($faculty['subject']); ?></p>
                  </div>
                </div>
              </div>
              <div class="col-md-4 mt-3 mt-md-0 text-md-end">
                <small class="text-white-50 d-block">Approved on</small>
                <p class="mb-0"><?php echo date('d M Y', strtotime($faculty['updated_at'])); ?></p>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Rejected Tab -->
        <div class="tab-pane fade" id="rejected" role="tabpanel">
          <?php
                    $rejected_faculty = array_filter($faculty_members, function($f) { return $f['status'] == 'rejected'; });
                    if (empty($rejected_faculty)): ?>
          <div class="text-center text-white-50 py-5">
            <i class="fas fa-ban fa-3x mb-3 opacity-50"></i>
            <p class="mb-0">No rejected applications.</p>
          </div>
          <?php else: ?>
          <?php foreach ($rejected_faculty as $faculty): ?>
          <div class="faculty-card">
            <div class="row align-items-start">
              <div class="col-md-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                  <div class="rounded-circle bg-danger bg-opacity-25 d-flex align-items-center justify-content-center"
                    style="width: 50px; height: 50px;">
                    <i class="fas fa-user-slash fa-2x text-danger"></i>
                  </div>
                  <div>
                    <h5 class="fw-bold mb-0">
                      <?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?></h5>
                    <span class="status-badge status-rejected">
                      <i class="fas fa-times-circle me-1"></i> Rejected
                    </span>
                  </div>
                </div>
                <div class="row g-3">
                  <div class="col-md-4">
                    <small class="text-white-50 d-block">Faculty ID</small>
                    <p class="mb-2"><?php echo htmlspecialchars($faculty['faculty_id']); ?></p>
                  </div>
                  <div class="col-md-4">
                    <small class="text-white-50 d-block">Department</small>
                    <p class="mb-2"><?php echo htmlspecialchars($faculty['department']); ?></p>
                  </div>
                  <div class="col-md-4">
                    <small class="text-white-50 d-block">Email</small>
                    <p class="mb-0"><?php echo htmlspecialchars($faculty['email']); ?></p>
                  </div>
                </div>
                <?php if ($faculty['rejection_reason']): ?>
                <div class="mt-2">
                  <small class="text-danger d-block">Rejection Reason:</small>
                  <p class="small text-white-50 mb-0"><?php echo htmlspecialchars($faculty['rejection_reason']); ?></p>
                </div>
                <?php endif; ?>
              </div>
              <div class="col-md-4 mt-3 mt-md-0 text-md-end">
                <button class="btn btn-sm btn-outline-warning"
                  onclick="reapproveFaculty(<?php echo $faculty['user_id']; ?>)">
                  <i class="fas fa-redo me-1"></i> Reconsider
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

  <!-- Approve Modal -->
  <div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-dark text-white">
        <div class="modal-header border-secondary">
          <h5 class="modal-title">Approve Faculty Registration</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to approve <strong id="approveFacultyName"></strong>?</p>
          <p class="text-success small">The faculty member will be able to login and access the dashboard.</p>
          <form method="POST" id="approveForm">
            <input type="hidden" name="faculty_user_id" id="approveFacultyId">
            <input type="hidden" name="action" value="approve">
            <div class="d-flex gap-2 mt-3">
              <button type="submit" class="btn btn-success flex-grow-1">Yes, Approve</button>
              <button type="button" class="btn btn-outline-glow" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Reject Modal -->
  <div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-dark text-white">
        <div class="modal-header border-secondary">
          <h5 class="modal-title">Reject Faculty Registration</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to reject <strong id="rejectFacultyName"></strong>?</p>
          <div class="mb-3">
            <label class="form-label text-white">Reason for rejection (Optional)</label>
            <textarea name="reason" id="rejectReason" class="form-control bg-dark text-white border-secondary" rows="3"
              placeholder="Enter reason for rejection..."></textarea>
          </div>
          <form method="POST" id="rejectForm">
            <input type="hidden" name="faculty_user_id" id="rejectFacultyId">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="reason" id="rejectReasonInput">
            <div class="d-flex gap-2 mt-3">
              <button type="submit" class="btn btn-danger flex-grow-1">Yes, Reject</button>
              <button type="button" class="btn btn-outline-glow" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Reapprove Form (for reconsidering rejected) -->
  <form method="POST" id="reapproveForm" style="display: none;">
    <input type="hidden" name="faculty_user_id" id="reapproveFacultyId">
    <input type="hidden" name="action" value="approve">
  </form>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function approveFaculty(userId, name) {
    document.getElementById('approveFacultyId').value = userId;
    document.getElementById('approveFacultyName').textContent = name;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
  }

  function rejectFaculty(userId, name) {
    document.getElementById('rejectFacultyId').value = userId;
    document.getElementById('rejectFacultyName').textContent = name;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
  }

  function reapproveFaculty(userId) {
    if (confirm('Do you want to reconsider and approve this faculty registration?')) {
      document.getElementById('reapproveFacultyId').value = userId;
      document.getElementById('reapproveForm').submit();
    }
  }

  // Handle reject reason
  document.getElementById('rejectForm')?.addEventListener('submit', function(e) {
    const reason = document.getElementById('rejectReason').value;
    document.getElementById('rejectReasonInput').value = reason;
  });
  </script>
</body>

</html>