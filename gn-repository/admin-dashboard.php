<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit();
}

$message = '';
$error = '';

// Handle approve/reject
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

// Get all faculty
$stmt = $pdo->prepare("
    SELECT u.*, f.* 
    FROM users u 
    JOIN faculty f ON u.id = f.user_id 
    WHERE u.user_type = 'faculty' 
    ORDER BY FIELD(u.status, 'pending', 'approved', 'rejected'), u.created_at DESC
");
$stmt->execute();
$faculty_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistics
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
  <title>Admin Dashboard - Faculty Approval</title>
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
  }

  .stats-card {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
  }

  .nav-tabs .nav-link {
    color: rgba(255, 255, 255, 0.7);
    border: none;
    padding: 12px 24px;
  }

  .nav-tabs .nav-link.active {
    background: rgba(76, 201, 240, 0.1);
    color: var(--primary);
    border-bottom: 2px solid var(--primary);
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
        <span class="text-white me-3">Admin: <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
        <a href="admin-logout.php" class="btn btn-sm btn-danger rounded-pill px-3">
          <i class="fas fa-power-off"></i> Logout
        </a>
      </div>
    </div>
  </nav>

  <div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="stats-card">
          <i class="fas fa-clock fa-2x text-warning mb-2"></i>
          <h3><?php echo $pending_count; ?></h3>
          <small>Pending Approvals</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card">
          <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
          <h3><?php echo $approved_count; ?></h3>
          <small>Approved Faculty</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card">
          <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
          <h3><?php echo $rejected_count; ?></h3>
          <small>Rejected</small>
        </div>
      </div>
    </div>

    <!-- Faculty List -->
    <div class="glass-panel p-4">
      <h4 class="fw-bold mb-4">
        <i class="fas fa-users text-primary me-2"></i>Faculty Management
      </h4>

      <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
          <a class="nav-link active" data-bs-toggle="tab" href="#pending">
            Pending <span class="badge bg-warning"><?php echo $pending_count; ?></span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#approved">
            Approved <span class="badge bg-success"><?php echo $approved_count; ?></span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#rejected">
            Rejected <span class="badge bg-danger"><?php echo $rejected_count; ?></span>
          </a>
        </li>
      </ul>

      <div class="tab-content">
        <!-- Pending Tab -->
        <div class="tab-pane fade show active" id="pending">
          <?php
                    $pending = array_filter($faculty_members, function($f) { return $f['status'] == 'pending'; });
                    if (empty($pending)): ?>
          <div class="text-center text-white-50 py-5">No pending faculty registrations.</div>
          <?php else: ?>
          <?php foreach ($pending as $faculty): ?>
          <div class="faculty-card">
            <div class="row">
              <div class="col-md-8">
                <h5><?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?></h5>
                <p class="mb-1"><strong>ID:</strong> <?php echo $faculty['faculty_id']; ?></p>
                <p class="mb-1"><strong>Email:</strong> <?php echo $faculty['email']; ?></p>
                <p class="mb-1"><strong>Department:</strong> <?php echo $faculty['department']; ?></p>
                <p class="mb-0"><strong>Subject:</strong> <?php echo $faculty['subject']; ?></p>
              </div>
              <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button class="btn btn-success btn-sm me-2"
                  onclick="approveFaculty(<?php echo $faculty['user_id']; ?>)">
                  <i class="fas fa-check"></i> Approve
                </button>
                <button class="btn btn-danger btn-sm" onclick="rejectFaculty(<?php echo $faculty['user_id']; ?>)">
                  <i class="fas fa-times"></i> Reject
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Approved Tab -->
        <div class="tab-pane fade" id="approved">
          <?php
                    $approved = array_filter($faculty_members, function($f) { return $f['status'] == 'approved'; });
                    if (empty($approved)): ?>
          <div class="text-center text-white-50 py-5">No approved faculty yet.</div>
          <?php else: ?>
          <?php foreach ($approved as $faculty): ?>
          <div class="faculty-card">
            <div class="row">
              <div class="col-md-8">
                <h5><?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?></h5>
                <p class="mb-1"><strong>ID:</strong> <?php echo $faculty['faculty_id']; ?></p>
                <p class="mb-1"><strong>Department:</strong> <?php echo $faculty['department']; ?></p>
                <p class="mb-0"><strong>Subject:</strong> <?php echo $faculty['subject']; ?></p>
              </div>
              <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="status-badge status-approved">Approved</span>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Rejected Tab -->
        <div class="tab-pane fade" id="rejected">
          <?php
                    $rejected = array_filter($faculty_members, function($f) { return $f['status'] == 'rejected'; });
                    if (empty($rejected)): ?>
          <div class="text-center text-white-50 py-5">No rejected applications.</div>
          <?php else: ?>
          <?php foreach ($rejected as $faculty): ?>
          <div class="faculty-card">
            <div class="row">
              <div class="col-md-8">
                <h5><?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?></h5>
                <p class="mb-1"><strong>ID:</strong> <?php echo $faculty['faculty_id']; ?></p>
                <p class="mb-1"><strong>Email:</strong> <?php echo $faculty['email']; ?></p>
                <?php if ($faculty['rejection_reason']): ?>
                <p class="mb-0 text-danger"><strong>Reason:</strong> <?php echo $faculty['rejection_reason']; ?></p>
                <?php endif; ?>
              </div>
              <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button class="btn btn-sm btn-outline-warning"
                  onclick="approveFaculty(<?php echo $faculty['user_id']; ?>)">
                  <i class="fas fa-redo"></i> Reconsider
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
        <div class="modal-header">
          <h5>Approve Faculty</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Approve this faculty registration?</p>
          <form method="POST" id="approveForm">
            <input type="hidden" name="faculty_user_id" id="approveId">
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="btn btn-success">Yes, Approve</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Reject Modal -->
  <div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-dark text-white">
        <div class="modal-header">
          <h5>Reject Faculty</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Reject this faculty registration?</p>
          <div class="mb-3">
            <label>Reason (Optional)</label>
            <textarea id="rejectReason" class="form-control bg-dark text-white" rows="2"></textarea>
          </div>
          <form method="POST" id="rejectForm">
            <input type="hidden" name="faculty_user_id" id="rejectId">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="reason" id="rejectReasonInput">
            <button type="submit" class="btn btn-danger">Yes, Reject</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function approveFaculty(id) {
    document.getElementById('approveId').value = id;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
  }

  function rejectFaculty(id) {
    document.getElementById('rejectId').value = id;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
  }
  document.getElementById('rejectForm')?.addEventListener('submit', function(e) {
    document.getElementById('rejectReasonInput').value = document.getElementById('rejectReason').value;
  });
  </script>
</body>

</html>