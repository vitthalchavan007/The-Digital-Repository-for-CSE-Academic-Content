<?php
require_once 'config.php';

// Check if admin is logged in
if (!isLoggedIn() || !isAdmin()) {
    redirect('index.html');
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_code = $_POST['subject_code'] ?? '';
    $subject_name = $_POST['subject_name'] ?? '';
    $branch = $_POST['branch'] ?? '';
    $semester = $_POST['semester'] ?? '';
    
    if (empty($subject_code) || empty($subject_name) || empty($branch) || empty($semester)) {
        $error = 'All fields are required';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, branch, semester) VALUES (?, ?, ?, ?)");
            $stmt->execute([$subject_code, $subject_name, $branch, $semester]);
            $message = 'Subject added successfully!';
        } catch(PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = 'Subject code already exists for this branch and semester';
            } else {
                $error = 'Failed to add subject: ' . $e->getMessage();
            }
        }
    }
}

// Get all subjects for display
$stmt = $pdo->query("SELECT * FROM subjects ORDER BY branch, semester, subject_code");
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$branches = ['CSE', 'IT', 'AIDS', 'AIML', 'CSEDS', 'CE', 'ME', 'ECE'];
$semesters = [1, 2, 3, 4, 5, 6, 7, 8];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Subjects - Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
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
    <div class="row">
      <div class="col-lg-4 mb-4">
        <div class="glass-panel p-4">
          <h4 class="fw-bold mb-3">
            <i class="fas fa-plus-circle text-primary me-2"></i>Add New Subject
          </h4>

          <?php if ($message): ?>
          <div class="alert alert-success"><?php echo $message; ?></div>
          <?php endif; ?>

          <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label text-white">Subject Code *</label>
              <input type="text" name="subject_code" class="form-control bg-dark text-white border-secondary" required>
              <small class="text-white-50">e.g., CS101, IT201, AIDS301</small>
            </div>

            <div class="mb-3">
              <label class="form-label text-white">Subject Name *</label>
              <input type="text" name="subject_name" class="form-control bg-dark text-white border-secondary" required>
            </div>

            <div class="mb-3">
              <label class="form-label text-white">Branch *</label>
              <select name="branch" class="form-control bg-dark text-white border-secondary" required>
                <option value="">Select Branch</option>
                <?php foreach ($branches as $b): ?>
                <option value="<?php echo $b; ?>"><?php echo $b; ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label text-white">Semester *</label>
              <select name="semester" class="form-control bg-dark text-white border-secondary" required>
                <option value="">Select Semester</option>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                <?php endfor; ?>
              </select>
            </div>

            <button type="submit" class="btn btn-glow w-100">
              <i class="fas fa-save me-2"></i>Add Subject
            </button>
          </form>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="glass-panel p-4">
          <h4 class="fw-bold mb-3">
            <i class="fas fa-book text-primary me-2"></i>All Subjects
          </h4>

          <div class="table-responsive">
            <table class="table table-dark table-hover">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Subject Name</th>
                  <th>Branch</th>
                  <th>Semester</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($subjects)): ?>
                <tr>
                  <td colspan="5" class="text-center text-white-50">No subjects found</td>
                </tr>
                <?php endif; ?>

                <?php foreach ($subjects as $subject): ?>
                <tr>
                  <td><code><?php echo htmlspecialchars($subject['subject_code']); ?></code></td>
                  <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                  <td><span class="badge bg-primary"><?php echo $subject['branch']; ?></span></td>
                  <td><span class="badge bg-info">Sem <?php echo $subject['semester']; ?></span></td>
                  <td>
                    <button class="btn btn-sm btn-danger"
                      onclick="deleteSubject(<?php echo $subject['id']; ?>, '<?php echo addslashes($subject['subject_code']); ?>')">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-dark text-white">
        <div class="modal-header border-secondary">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete subject <strong id="deleteSubjectCode"></strong>?</p>
          <p class="text-danger small">Warning: This will also delete all resources linked to this subject!</p>
        </div>
        <div class="modal-footer border-secondary">
          <form method="POST" action="delete-subject.php">
            <input type="hidden" name="subject_id" id="deleteSubjectId">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
  function deleteSubject(id, code) {
    document.getElementById('deleteSubjectId').value = id;
    document.getElementById('deleteSubjectCode').textContent = code;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
  }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>