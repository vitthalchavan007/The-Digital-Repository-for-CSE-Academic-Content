<?php
require_once 'config.php';

if (!isLoggedIn() || !isFaculty()) {
    redirect('faculty-login.php');
}
if (!isLoggedIn() || !isFaculty()) {
    redirect('faculty-login.php');
}
// Handle upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'upload') {
        $title = $_POST['title'] ?? '';
        $type = $_POST['type'] ?? '';
        $branch = $_POST['branch'] ?? '';
        $year = $_POST['year'] ?? '';
        $semester = $_POST['semester'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $subject_code = $_POST['subject_code'] ?? '';
        $video_url = $_POST['video_url'] ?? '';
        $description = $_POST['description'] ?? '';
        
        $file_path = null;
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $file_path = uploadFile($_FILES['file']);
        }
        
        $stmt = $pdo->prepare("INSERT INTO resources (title, resource_type, subject, subject_code, branch, year, semester, file_path, video_url, author_id, author_name, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $type, $subject, $subject_code, $branch, $year, $semester, $file_path, $video_url, $_SESSION['user_id'], $_SESSION['name'], $description]);
        
        $_SESSION['message'] = 'Resource uploaded successfully!';
        redirect('faculty-dashboard.php');
    }
    elseif ($_POST['action'] == 'update') {
        $id = $_POST['resource_id'] ?? 0;
        $title = $_POST['title'] ?? '';
        $type = $_POST['type'] ?? '';
        $branch = $_POST['branch'] ?? '';
        $year = $_POST['year'] ?? '';
        $semester = $_POST['semester'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $subject_code = $_POST['subject_code'] ?? '';
        $video_url = $_POST['video_url'] ?? '';
        $description = $_POST['description'] ?? '';
        
        // Get existing resource
        $existing = getFacultyResourceById($pdo, $id, $_SESSION['user_id']);
        
        $file_path = $existing['file_path'];
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            // Delete old file if exists
            if ($file_path && file_exists($file_path)) {
                unlink($file_path);
            }
            $file_path = uploadFile($_FILES['file']);
        }
        
        $data = [
            'title' => $title,
            'resource_type' => $type,
            'subject' => $subject,
            'subject_code' => $subject_code,
            'branch' => $branch,
            'year' => $year,
            'semester' => $semester,
            'description' => $description,
            'video_url' => $video_url,
            'file_path' => $file_path
        ];
        
        if (updateResource($pdo, $id, $data, $_SESSION['user_id'])) {
            $_SESSION['message'] = 'Resource updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update resource.';
        }
        redirect('faculty-dashboard.php');
    }
    elseif ($_POST['action'] == 'delete') {
        $id = $_POST['resource_id'] ?? 0;
        if (deleteResource($pdo, $id, $_SESSION['user_id'])) {
            $_SESSION['message'] = 'Resource deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete resource.';
        }
        redirect('faculty-dashboard.php');
    }
}

// Get faculty uploads
$stmt = $pdo->prepare("SELECT * FROM resources WHERE author_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get stats
$notes = 0; $videos = 0; $assignments = 0; $syllabus = 0; $pyq = 0;
foreach ($uploads as $item) {
    if ($item['resource_type'] == 'note') $notes++;
    else if ($item['resource_type'] == 'video') $videos++;
    else if ($item['resource_type'] == 'assignment') $assignments++;
    else if ($item['resource_type'] == 'syllabus') $syllabus++;
    else if ($item['resource_type'] == 'pyq') $pyq++;
}

// Get subjects for dropdown
$branches = ['CSE', 'IT', 'AIDS', 'AIML', 'CSEDS', 'CE', 'ME', 'ECE'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Dashboard - GN Group</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/dashboard.css">
  <style>
  .resource-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
    display: flex;
    gap: 5px;
  }

  .resource-actions .btn-action {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.7);
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
  }

  .resource-actions .btn-action:hover {
    transform: scale(1.1);
  }

  .btn-edit:hover {
    background: var(--primary);
  }

  .btn-delete:hover {
    background: #dc3545;
  }

  .table-actions {
    display: flex;
    gap: 8px;
  }

  .btn-sm-custom {
    padding: 4px 10px;
    font-size: 12px;
    border-radius: 6px;
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
      <!-- Add this link in the sidebar section -->
      <a href="edit-profile.php" class="sidebar-link">
        <i class="fas fa-user-edit me-3"></i>Edit Profile
      </a>
      <div class="ms-auto d-flex align-items-center">
        <div class="bg-gradient rounded-circle me-2 d-flex align-items-center justify-content-center"
          style="width:35px; height:35px; background: var(--purple);">
          <i class="fas fa-chalkboard-teacher small"></i>
        </div>
        <span class="text-white me-3"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
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
      <!-- Sidebar -->
      <div class="col-md-3 mb-4">
        <div class="glass-panel p-4">
          <div class="text-center mb-4">
            <div
              class="bg-primary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
              style="width: 80px; height: 80px;">
              <i class="fas fa-chalkboard-teacher fa-3x text-primary"></i>
            </div>
            <h4><?php echo htmlspecialchars($_SESSION['name']); ?></h4>
            <p class="text-white-50 mb-0">Faculty Member</p>
            <p class="text-white-50 small mb-0"><?php echo htmlspecialchars($_SESSION['department'] ?? 'GNIT'); ?></p>
          </div>
          <div class="border-top border-secondary pt-3">
            <div class="d-flex justify-content-between mb-2">
              <span>Total Uploads</span>
              <span class="badge bg-primary"><?php echo count($uploads); ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Department</span>
              <span class="badge bg-secondary"><?php echo htmlspecialchars($_SESSION['department'] ?? 'GNIT'); ?></span>
            </div>
          </div>

          <div class="d-grid gap-2 mt-4">
            <button class="btn btn-glow" data-bs-toggle="modal" data-bs-target="#uploadModal">
              <i class="fas fa-cloud-upload-alt me-2"></i> Upload Content
            </button>
            <a href="faculty-attendance.php" class="sidebar-link">
              <i class="fas fa-calendar-check me-3"></i>Live Class Attendance
            </a>
            <!-- Add this in the sidebar section -->
            <a href="export-all-attendance-pdf.php" class="btn btn-outline-glow w-100 mb-3" target="_blank">
              <i class="fas fa-file-pdf me-2"></i> Download All Attendance
            </a>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-md-9">
        <!-- Quick Stats Cards -->
        <div class="row g-3 mb-4">
          <div class="col-md-2 col-6">
            <div class="glass-panel p-3 text-center">
              <i class="fas fa-file-pdf text-primary mb-2"></i>
              <h5 class="mb-0"><?php echo $notes; ?></h5>
              <small class="text-white-50">Notes</small>
            </div>
          </div>
          <div class="col-md-2 col-6">
            <div class="glass-panel p-3 text-center">
              <i class="fas fa-video text-info mb-2"></i>
              <h5 class="mb-0"><?php echo $videos; ?></h5>
              <small class="text-white-50">Videos</small>
            </div>
          </div>
          <div class="col-md-2 col-6">
            <div class="glass-panel p-3 text-center">
              <i class="fas fa-tasks text-warning mb-2"></i>
              <h5 class="mb-0"><?php echo $assignments; ?></h5>
              <small class="text-white-50">Assignments</small>
            </div>
          </div>
          <div class="col-md-2 col-6">
            <div class="glass-panel p-3 text-center">
              <i class="fas fa-book text-success mb-2"></i>
              <h5 class="mb-0"><?php echo $syllabus; ?></h5>
              <small class="text-white-50">Syllabus</small>
            </div>
          </div>
          <div class="col-md-2 col-6">
            <div class="glass-panel p-3 text-center">
              <i class="fas fa-file-contract text-danger mb-2"></i>
              <h5 class="mb-0"><?php echo $pyq; ?></h5>
              <small class="text-white-50">PYQs</small>
            </div>
          </div>
        </div>

        <!-- All Uploads Section with Edit/Delete -->
        <div class="glass-panel p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">My Uploads (<?php echo count($uploads); ?>)</h4>
            <button class="btn btn-sm btn-outline-glow" onclick="location.reload()">
              <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
          </div>

          <div class="table-responsive">
            <?php if (empty($uploads)): ?>
            <p class="text-center text-white-50 py-4">
              <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i><br>
              No uploads yet. Click "Upload Content" to get started.
            </p>
            <?php else: ?>
            <table class="table table-dark table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Title</th>
                  <th>Type</th>
                  <th>Branch</th>
                  <th>Year/Sem</th>
                  <th>Subject</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($uploads as $index => $item): ?>
                <tr>
                  <td><?php echo $index + 1; ?></td>
                  <td>
                    <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                    <?php if (!empty($item['description'])): ?>
                    <br><small
                      class="text-white-50"><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php
                    $typeBadge = '';
                    switch($item['resource_type']) {
                        case 'note': $typeBadge = '<span class="badge bg-primary">📄 Note</span>'; break;
                        case 'video': $typeBadge = '<span class="badge bg-info">🎥 Video</span>'; break;
                        case 'assignment': $typeBadge = '<span class="badge bg-warning">📋 Assignment</span>'; break;
                        case 'syllabus': $typeBadge = '<span class="badge bg-success">📚 Syllabus</span>'; break;
                        case 'pyq': $typeBadge = '<span class="badge bg-danger">📝 PYQ</span>'; break;
                        default: $typeBadge = '<span class="badge bg-secondary">' . $item['resource_type'] . '</span>';
                    }
                    echo $typeBadge;
                    ?>
                  </td>
                  <td><?php echo $item['branch']; ?></td>
                  <td>Y<?php echo $item['year']; ?> S<?php echo $item['semester']; ?></td>
                  <td><small><?php echo htmlspecialchars($item['subject']); ?></small></td>
                  <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                  <td class="table-actions">
                    <button class="btn btn-sm btn-primary btn-sm-custom"
                      onclick="editResource(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger btn-sm-custom"
                      onclick="deleteResource(<?php echo $item['id']; ?>, '<?php echo addslashes($item['title']); ?>')">
                      <i class="fas fa-trash"></i> Delete
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Upload Modal -->
  <div class="modal fade custom-modal" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="fw-bold">Upload Learning Material</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="action" value="upload">

            <div class="floating-input-group mb-3">
              <input type="text" name="title" id="title" placeholder=" " required>
              <label>Resource Title *</label>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <div class="floating-input-group">
                  <select name="branch" id="branch" required>
                    <option value="" disabled selected></option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b; ?>"><?php echo $b; ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label>Branch *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group">
                  <select name="year" id="year" required onchange="updateSemesters()">
                    <option value="" disabled selected></option>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                  </select>
                  <label>Year *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group">
                  <select name="semester" id="semester" required>
                    <option value="" disabled selected>Select Year first</option>
                  </select>
                  <label>Semester *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group">
                  <select name="type" id="type" required onchange="toggleFields()">
                    <option value="" disabled selected></option>
                    <option value="note">📄 PDF / Note</option>
                    <option value="video">🎥 Video URL</option>
                    <option value="assignment">📋 Assignment</option>
                    <option value="syllabus">📚 Syllabus</option>
                    <option value="pyq">📝 Previous Year Questions</option>
                  </select>
                  <label>Resource Type *</label>
                </div>
              </div>
            </div>

            <div class="floating-input-group mb-3">
              <input type="text" name="subject" id="subject" placeholder=" " required>
              <label>Subject Name *</label>
            </div>

            <div class="floating-input-group mb-3">
              <input type="text" name="subject_code" id="subject_code" placeholder=" ">
              <label>Subject Code (Optional)</label>
            </div>

            <div id="file-field" class="mb-3">
              <div class="file-input-wrapper">
                <input type="file" name="file" id="file" class="file-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt">
                <label for="file" class="file-input-label">
                  <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                  <div>
                    <span id="file-label-text">Choose file (PDF, DOC, PPT)</span>
                    <p class="small text-white-50 mb-0 mt-1">Maximum file size: 50MB</p>
                  </div>
                </label>
              </div>
            </div>

            <div id="url-field" class="floating-input-group mb-3" style="display:none;">
              <input type="url" name="video_url" id="video_url" placeholder=" ">
              <label>YouTube / Video Link</label>
              <small class="text-white-50 d-block mt-1">
                <i class="fas fa-info-circle me-1"></i>
                Enter YouTube URL (e.g., https://www.youtube.com/watch?v=xxxxx or https://youtu.be/xxxxx)
              </small>
            </div>

            <div class="floating-input-group mb-4">
              <textarea name="description" id="description" placeholder=" " rows="3"></textarea>
              <label>Description (Optional)</label>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-glow flex-fill">Publish Now</button>
              <button type="button" class="btn btn-outline-glow" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade custom-modal" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="fw-bold">Edit Resource</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="resource_id" id="edit_resource_id">

            <div class="floating-input-group mb-3">
              <input type="text" name="title" id="edit_title" placeholder=" " required>
              <label>Resource Title *</label>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <div class="floating-input-group">
                  <select name="branch" id="edit_branch" required>
                    <option value="" disabled>Select Branch</option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b; ?>"><?php echo $b; ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label>Branch *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group">
                  <select name="year" id="edit_year" required onchange="updateEditSemesters()">
                    <option value="" disabled>Select Year</option>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                  </select>
                  <label>Year *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group">
                  <select name="semester" id="edit_semester" required>
                    <option value="" disabled>Select Semester</option>
                  </select>
                  <label>Semester *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group">
                  <select name="type" id="edit_type" required onchange="toggleEditFields()">
                    <option value="note">📄 PDF / Note</option>
                    <option value="video">🎥 Video URL</option>
                    <option value="assignment">📋 Assignment</option>
                    <option value="syllabus">📚 Syllabus</option>
                    <option value="pyq">📝 Previous Year Questions</option>
                  </select>
                  <label>Resource Type *</label>
                </div>
              </div>
            </div>

            <div class="floating-input-group mb-3">
              <input type="text" name="subject" id="edit_subject" placeholder=" " required>
              <label>Subject Name *</label>
            </div>

            <div class="floating-input-group mb-3">
              <input type="text" name="subject_code" id="edit_subject_code" placeholder=" ">
              <label>Subject Code (Optional)</label>
            </div>

            <div id="edit-file-field" class="mb-3">
              <div class="file-input-wrapper">
                <input type="file" name="file" id="edit_file" class="file-input"
                  accept=".pdf,.doc,.docx,.ppt,.pptx,.txt">
                <label for="edit_file" class="file-input-label">
                  <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                  <div>
                    <span id="edit-file-label-text">Choose new file (leave empty to keep current)</span>
                    <p class="small text-white-50 mb-0 mt-1">Maximum file size: 50MB</p>
                  </div>
                </label>
              </div>
            </div>

            <div id="edit-url-field" class="floating-input-group mb-3" style="display:none;">
              <input type="url" name="video_url" id="edit_video_url" placeholder=" ">
              <label>YouTube / Video Link</label>
              <small class="text-white-50 d-block mt-1">
                <i class="fas fa-info-circle me-1"></i>
                Enter YouTube URL (e.g., https://www.youtube.com/watch?v=xxxxx or https://youtu.be/xxxxx)
              </small>
            </div>

            <div class="floating-input-group mb-4">
              <textarea name="description" id="edit_description" placeholder=" " rows="3"></textarea>
              <label>Description (Optional)</label>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-glow flex-fill">Update Resource</button>
              <button type="button" class="btn btn-outline-glow" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade custom-modal" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="fw-bold text-danger">Confirm Delete</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete "<span id="deleteResourceTitle"></span>"?</p>
          <p class="text-white-50 small">This action cannot be undone. The file will be permanently deleted.</p>
          <form method="POST" id="deleteForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="resource_id" id="delete_resource_id">
            <div class="d-flex gap-2 mt-3">
              <button type="submit" class="btn btn-danger flex-fill">Yes, Delete</button>
              <button type="button" class="btn btn-outline-glow" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
  function updateSemesters() {
    const year = document.getElementById('year').value;
    const semester = document.getElementById('semester');
    semester.innerHTML = '<option value="" disabled selected>Select Semester</option>';

    if (year === '1') {
      semester.innerHTML += '<option value="1">Semester 1</option><option value="2">Semester 2</option>';
    } else if (year === '2') {
      semester.innerHTML += '<option value="3">Semester 3</option><option value="4">Semester 4</option>';
    } else if (year === '3') {
      semester.innerHTML += '<option value="5">Semester 5</option><option value="6">Semester 6</option>';
    } else if (year === '4') {
      semester.innerHTML += '<option value="7">Semester 7</option><option value="8">Semester 8</option>';
    }
  }

  function updateEditSemesters() {
    const year = document.getElementById('edit_year').value;
    const semester = document.getElementById('edit_semester');
    const currentSemester = semester.getAttribute('data-current') || '';
    semester.innerHTML = '<option value="" disabled>Select Semester</option>';

    if (year === '1') {
      semester.innerHTML += '<option value="1">Semester 1</option><option value="2">Semester 2</option>';
    } else if (year === '2') {
      semester.innerHTML += '<option value="3">Semester 3</option><option value="4">Semester 4</option>';
    } else if (year === '3') {
      semester.innerHTML += '<option value="5">Semester 5</option><option value="6">Semester 6</option>';
    } else if (year === '4') {
      semester.innerHTML += '<option value="7">Semester 7</option><option value="8">Semester 8</option>';
    }

    if (currentSemester) {
      semester.value = currentSemester;
    }
  }

  function convertToEmbedUrl(url) {
    if (!url) return url;

    if (url.includes('youtube.com/watch?v=')) {
      let videoId = url.split('v=')[1];
      const ampersandIndex = videoId.indexOf('&');
      if (ampersandIndex !== -1) {
        videoId = videoId.substring(0, ampersandIndex);
      }
      return 'https://www.youtube.com/embed/' + videoId;
    } else if (url.includes('youtu.be/')) {
      let videoId = url.split('youtu.be/')[1];
      const questionMarkIndex = videoId.indexOf('?');
      if (questionMarkIndex !== -1) {
        videoId = videoId.substring(0, questionMarkIndex);
      }
      return 'https://www.youtube.com/embed/' + videoId;
    }
    return url;
  }

  function validateYouTubeUrl(url) {
    if (!url) return false;
    const youtubeRegex = /^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})(.*)?$/;
    return youtubeRegex.test(url);
  }

  function toggleFields() {
    const type = document.getElementById('type').value;
    const fileField = document.getElementById('file-field');
    const urlField = document.getElementById('url-field');
    const fileInput = document.getElementById('file');

    if (type === 'video') {
      urlField.style.display = 'block';
      fileField.style.display = 'none';
      fileInput.removeAttribute('required');
    } else {
      urlField.style.display = 'none';
      fileField.style.display = 'block';
      fileInput.setAttribute('required', 'required');
    }
  }

  function toggleEditFields() {
    const type = document.getElementById('edit_type').value;
    const fileField = document.getElementById('edit-file-field');
    const urlField = document.getElementById('edit-url-field');
    const fileInput = document.getElementById('edit_file');

    if (type === 'video') {
      urlField.style.display = 'block';
      fileField.style.display = 'none';
      fileInput.removeAttribute('required');
    } else {
      urlField.style.display = 'none';
      fileField.style.display = 'block';
    }
  }

  function validateAndSubmit() {
    const type = document.getElementById('type').value;

    if (type === 'video') {
      const videoUrl = document.getElementById('video_url').value;
      if (!videoUrl) {
        alert('Please enter a video URL');
        return false;
      }

      if (!validateYouTubeUrl(videoUrl)) {
        alert(
          'Please enter a valid YouTube URL.\n\nExamples:\n- https://www.youtube.com/watch?v=xxxxx\n- https://youtu.be/xxxxx'
        );
        return false;
      }

      const embedUrl = convertToEmbedUrl(videoUrl);
      document.getElementById('video_url').value = embedUrl;
    }
    return true;
  }

  function validateEditAndSubmit() {
    const type = document.getElementById('edit_type').value;

    if (type === 'video') {
      const videoUrl = document.getElementById('edit_video_url').value;
      if (videoUrl && !validateYouTubeUrl(videoUrl)) {
        alert(
          'Please enter a valid YouTube URL.\n\nExamples:\n- https://www.youtube.com/watch?v=xxxxx\n- https://youtu.be/xxxxx'
        );
        return false;
      }
      if (videoUrl) {
        const embedUrl = convertToEmbedUrl(videoUrl);
        document.getElementById('edit_video_url').value = embedUrl;
      }
    }
    return true;
  }

  function editResource(resource) {
    document.getElementById('edit_resource_id').value = resource.id;
    document.getElementById('edit_title').value = resource.title;
    document.getElementById('edit_branch').value = resource.branch;
    document.getElementById('edit_year').value = resource.year;
    document.getElementById('edit_semester').setAttribute('data-current', resource.semester);
    document.getElementById('edit_type').value = resource.resource_type;
    document.getElementById('edit_subject').value = resource.subject;
    document.getElementById('edit_subject_code').value = resource.subject_code || '';
    document.getElementById('edit_description').value = resource.description || '';

    if (resource.resource_type === 'video') {
      document.getElementById('edit_video_url').value = resource.video_url || '';
      document.getElementById('edit-url-field').style.display = 'block';
      document.getElementById('edit-file-field').style.display = 'none';
    } else {
      document.getElementById('edit-url-field').style.display = 'none';
      document.getElementById('edit-file-field').style.display = 'block';
    }

    updateEditSemesters();

    new bootstrap.Modal(document.getElementById('editModal')).show();
  }

  function deleteResource(id, title) {
    document.getElementById('deleteResourceTitle').textContent = title;
    document.getElementById('delete_resource_id').value = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
  }

  document.getElementById('file')?.addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose file';
    document.getElementById('file-label-text').textContent = fileName;
  });

  document.getElementById('edit_file')?.addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose new file (leave empty to keep current)';
    document.getElementById('edit-file-label-text').textContent = fileName;
  });

  document.getElementById('uploadForm').onsubmit = validateAndSubmit;
  document.getElementById('editForm').onsubmit = validateEditAndSubmit;
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>