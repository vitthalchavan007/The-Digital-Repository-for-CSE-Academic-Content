<?php
require_once 'config.php';

if (!isLoggedIn() || !isStudent()) {
    redirect('login.php');
}

// Get student details
$branch = $_SESSION['branch'];
$year = $_SESSION['year'];
$semester = $_SESSION['semester'];

// Handle filters
$type_filter = $_GET['type'] ?? 'all';
$subject_filter = $_GET['subject'] ?? 'all';
$search = $_GET['search'] ?? '';

// Get resources using the new function
$resources = getAllResources($pdo, [
    'branch' => $branch,
    'year' => $year,
    'semester' => $semester,
    'type' => $type_filter != 'all' ? $type_filter : null,
    'subject' => $subject_filter != 'all' ? $subject_filter : null,
    'search' => $search
]);

// Get subjects for filter from database
$subjects = getSubjectsByBranchSemester($pdo, $branch, $semester);

// Get stats
$stats = getResourceStats($pdo, $branch, $year, $semester);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard - GNIT Repository</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/dashboard.css">
  <style>
  .stats-card {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.05);
  }

  .stats-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary);
  }

  .stats-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
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
          style="width:35px; height:35px; background: var(--secondary);">
          <i class="fas fa-user-graduate small"></i>
        </div>
        <span class="text-white me-3"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
        <a href="logout.php" class="btn btn-sm btn-danger rounded-pill px-3">
          <i class="fas fa-power-off"></i>
        </a>
      </div>
    </div>
  </nav>

  <div id="dashboard" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="container">
      <!-- Stats Cards -->
      <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
          <div class="stats-card">
            <i class="fas fa-file-pdf text-primary fs-2 mb-2"></i>
            <div class="stats-number"><?php echo $stats['note']; ?></div>
            <small class="text-white-50">Notes</small>
          </div>
        </div>
        <div class="col-md-2 col-6">
          <div class="stats-card">
            <i class="fas fa-video text-info fs-2 mb-2"></i>
            <div class="stats-number"><?php echo $stats['video']; ?></div>
            <small class="text-white-50">Videos</small>
          </div>
        </div>
        <div class="col-md-2 col-6">
          <div class="stats-card">
            <i class="fas fa-tasks text-warning fs-2 mb-2"></i>
            <div class="stats-number"><?php echo $stats['assignment']; ?></div>
            <small class="text-white-50">Assignments</small>
          </div>
        </div>
        <div class="col-md-2 col-6">
          <div class="stats-card">
            <i class="fas fa-book text-success fs-2 mb-2"></i>
            <div class="stats-number"><?php echo $stats['syllabus']; ?></div>
            <small class="text-white-50">Syllabus</small>
          </div>
        </div>
        <div class="col-md-2 col-6">
          <div class="stats-card">
            <i class="fas fa-file-contract text-danger fs-2 mb-2"></i>
            <div class="stats-number"><?php echo $stats['pyq']; ?></div>
            <small class="text-white-50">PYQs</small>
          </div>
        </div>
        <div class="col-md-2 col-6">
          <div class="stats-card">
            <i class="fas fa-database text-secondary fs-2 mb-2"></i>
            <div class="stats-number"><?php echo $stats['total']; ?></div>
            <small class="text-white-50">Total</small>
          </div>
        </div>
      </div>

      <div class="semester-info-box mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
          <div class="d-flex align-items-center">
            <i class="fas fa-filter text-primary me-3 fs-4"></i>
            <div>
              <h6 class="mb-1 text-white">Semester-Specific View</h6>
              <p class="mb-0 text-white-50 small">
                You are viewing resources for <?php echo $branch; ?> • Year <?php echo $year; ?> • Semester
                <?php echo $semester; ?>
              </p>
            </div>
          </div>
          <a href="my-notes.php" class="btn btn-outline-glow btn-sm mt-2 mt-sm-0">
            <i class="fas fa-edit me-1"></i> My Notes
          </a>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-3 mb-4 sidebar-container">
          <div class="glass-panel p-3 h-100">
            <small class="text-uppercase text-white-50 fw-bold px-3">Categories</small>
            <div class="mt-2">
              <a href="?type=all" class="sidebar-link <?php echo $type_filter == 'all' ? 'active' : ''; ?>">
                <i class="fas fa-th-large me-3"></i>All Resources
              </a>
              <a href="?type=note" class="sidebar-link <?php echo $type_filter == 'note' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt me-3"></i>Lecture Notes
              </a>
              <a href="?type=video" class="sidebar-link <?php echo $type_filter == 'video' ? 'active' : ''; ?>">
                <i class="fas fa-play-circle me-3"></i>Video Classes
              </a>
              <a href="?type=assignment"
                class="sidebar-link <?php echo $type_filter == 'assignment' ? 'active' : ''; ?>">
                <i class="fas fa-tasks me-3"></i>Assignments
              </a>
              <a href="?type=syllabus" class="sidebar-link <?php echo $type_filter == 'syllabus' ? 'active' : ''; ?>">
                <i class="fas fa-book me-3"></i>Syllabus
              </a>
              <a href="?type=pyq" class="sidebar-link <?php echo $type_filter == 'pyq' ? 'active' : ''; ?>">
                <i class="fas fa-file-contract me-3"></i>Previous Year Papers
              </a>
              <a href="student-attendance.php" class="sidebar-link">
                <i class="fas fa-calendar-alt me-3"></i>My Attendance
              </a>
            </div>
          </div>
        </div>

        <div class="col-lg-9">
          <form method="GET" class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <?php if ($type_filter != 'all'): ?>
            <input type="hidden" name="type" value="<?php echo $type_filter; ?>">
            <?php endif; ?>

            <div class="flex-grow-1">
              <div class="floating-input-group mb-0">
                <input type="text" name="search" id="search" placeholder=" "
                  value="<?php echo htmlspecialchars($search); ?>">
                <label><i class="fas fa-search me-2"></i>Search topics, subjects...</label>
              </div>
            </div>
            <div style="min-width: 200px;">
              <div class="floating-input-group mb-0">
                <select name="subject" onchange="this.form.submit()">
                  <option value="all" <?php echo $subject_filter == 'all' ? 'selected' : ''; ?>>All Subjects</option>
                  <?php foreach ($subjects as $subject): ?>
                  <option value="<?php echo htmlspecialchars($subject['subject_name']); ?>"
                    <?php echo $subject_filter == $subject['subject_name'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
                <label>Filter by Subject</label>
              </div>
            </div>
          </form>

          <div class="row g-4" id="resourceGrid">
            <?php if (empty($resources)): ?>
            <div class="col-12 text-center text-white-50 mt-5 py-5">
              <i class="fas fa-folder-open fa-4x mb-3 opacity-50"></i>
              <h4>No resources found</h4>
              <p class="mb-0">Try changing your filters or check back later for new content.</p>
            </div>
            <?php endif; ?>

            <?php foreach ($resources as $index => $item): 
                $coverClass = 'cover-pdf';
                $icon = 'fa-file-pdf';
                $btnText = 'Download';
                $action = "downloadResource({$item['id']})";
                
                if($item['resource_type'] == 'video') { 
                    $coverClass = 'cover-video'; 
                    $icon = 'fa-play'; 
                    $btnText = 'Watch Video';
                    $action = "openVideoModal('{$item['video_url']}', '{$item['title']}')";
                } else if($item['resource_type'] == 'assignment') { 
                    $coverClass = 'cover-assign'; 
                    $icon = 'fa-clipboard-list';
                } else if($item['resource_type'] == 'syllabus') {
                    $coverClass = 'cover-syllabus';
                    $icon = 'fa-book';
                } else if($item['resource_type'] == 'pyq') {
                    $coverClass = 'cover-pyq';
                    $icon = 'fa-file-contract';
                }
            ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
              <div class="resource-card">
                <div class="card-cover <?php echo $coverClass; ?>"></div>
                <div class="card-icon-float">
                  <i class="fas <?php echo $icon; ?>"></i>
                </div>
                <div class="p-4 pt-5 mt-2">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-secondary bg-opacity-25 text-info border border-info border-opacity-25">
                      <?php echo htmlspecialchars($item['subject']); ?>
                    </span>
                    <small class="text-white-50"><?php echo date('M d, Y', strtotime($item['created_at'])); ?></small>
                  </div>
                  <h5 class="fw-bold text-white mb-1 text-truncate"
                    title="<?php echo htmlspecialchars($item['title']); ?>">
                    <?php echo htmlspecialchars($item['title']); ?>
                  </h5>
                  <small class="text-white-50 d-block mb-3">
                    By <?php echo htmlspecialchars($item['author_name']); ?>
                  </small>

                  <?php if (!empty($item['description'])): ?>
                  <p class="small text-white-50 mb-3">
                    <?php echo htmlspecialchars(substr($item['description'], 0, 80)); ?>...</p>
                  <?php endif; ?>

                  <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-glow flex-grow-1" onclick="<?php echo $action; ?>">
                      <i class="fas <?php echo $btnText == 'Watch Video' ? 'fa-play' : 'fa-download'; ?> me-1"></i>
                      <?php echo $btnText; ?>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary"
                      onclick="showResourceDetails(<?php echo $item['id']; ?>)" title="Details">
                      <i class="fas fa-info-circle"></i>
                    </button>
                  </div>

                  <div class="mt-2 d-flex justify-content-between text-white-50 small">
                    <span><i class="fas fa-download me-1"></i> <?php echo $item['downloads_count']; ?></span>
                    <span><i class="fas fa-eye me-1"></i> <?php echo $item['views_count']; ?></span>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Video Modal - Replace the existing video modal with this -->
  <div class="modal fade custom-modal" id="videoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="fw-bold" id="videoModalTitle">Video Player</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <div class="ratio ratio-16x9">
            <iframe id="videoFrame" src="" frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen>
            </iframe>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Resource Details Modal -->
  <div class="modal fade custom-modal" id="resourceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="fw-bold" id="resourceModalTitle"></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="resourceModalContent"></div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
  AOS.init({
    duration: 800,
    once: true
  });

  function downloadResource(id) {
    window.location.href = 'download.php?id=' + id;
  }

  function openVideoModal(url, title) {
    document.getElementById('videoModalTitle').textContent = title;
    document.getElementById('videoFrame').src = url;
    new bootstrap.Modal(document.getElementById('videoModal')).show();
  }

  function showResourceDetails(id) {
    fetch('get-resource.php?id=' + id)
      .then(response => response.json())
      .then(data => {
        document.getElementById('resourceModalTitle').textContent = data.title;
        document.getElementById('resourceModalContent').innerHTML = `
          <div class="mb-3">
            <strong class="text-primary">Subject:</strong> ${data.subject}
          </div>
          <div class="mb-3">
            <strong class="text-primary">Type:</strong> ${data.resource_type}
          </div>
          <div class="mb-3">
            <strong class="text-primary">Uploaded By:</strong> ${data.author_name}
          </div>
          <div class="mb-3">
            <strong class="text-primary">Date:</strong> ${new Date(data.created_at).toLocaleDateString()}
          </div>
          ${data.description ? `<div class="mb-3"><strong class="text-primary">Description:</strong><br>${data.description}</div>` : ''}
          <div class="mb-3">
            <strong class="text-primary">Downloads:</strong> ${data.downloads_count}
          </div>
        `;
        new bootstrap.Modal(document.getElementById('resourceModal')).show();
      });
  }

  document.getElementById('videoModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('videoFrame').src = "";
  }); <
  script src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" >
  </script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
  AOS.init({
    duration: 800,
    once: true
  });

  function downloadResource(id) {
    window.location.href = 'download.php?id=' + id;
  }

  // Updated function to handle YouTube URLs properly
  function openVideoModal(url, title) {
    document.getElementById('videoModalTitle').textContent = title;
    let videoUrl = url;

    // If it's a YouTube watch URL, convert to embed
    if (videoUrl && videoUrl.includes('youtube.com/watch?v=')) {
      let videoId = videoUrl.split('v=')[1];
      const ampersandIndex = videoId.indexOf('&');
      if (ampersandIndex !== -1) {
        videoId = videoId.substring(0, ampersandIndex);
      }
      videoUrl = 'https://www.youtube.com/embed/' + videoId;
    }
    // If it's a youtu.be URL, convert to embed
    else if (videoUrl && videoUrl.includes('youtu.be/')) {
      let videoId = videoUrl.split('youtu.be/')[1];
      const questionMarkIndex = videoId.indexOf('?');
      if (questionMarkIndex !== -1) {
        videoId = videoId.substring(0, questionMarkIndex);
      }
      videoUrl = 'https://www.youtube.com/embed/' + videoId;
    }
    // If it's already an embed URL, keep as is
    else if (videoUrl && videoUrl.includes('youtube.com/embed/')) {
      // Already in embed format, use as is
    }
    // If it's an invalid URL, show error
    else if (videoUrl && !videoUrl.includes('youtube.com')) {
      alert('Invalid video URL format. Please contact faculty for correct link.');
      return;
    }

    document.getElementById('videoFrame').src = videoUrl;
    new bootstrap.Modal(document.getElementById('videoModal')).show();
  }

  function showResourceDetails(id) {
    fetch('get-resource.php?id=' + id)
      .then(response => response.json())
      .then(data => {
        document.getElementById('resourceModalTitle').textContent = data.title;
        document.getElementById('resourceModalContent').innerHTML = `
        <div class="mb-3">
          <strong class="text-primary">Subject:</strong> ${data.subject}
        </div>
        <div class="mb-3">
          <strong class="text-primary">Type:</strong> ${data.resource_type}
        </div>
        <div class="mb-3">
          <strong class="text-primary">Uploaded By:</strong> ${data.author_name}
        </div>
        <div class="mb-3">
          <strong class="text-primary">Date:</strong> ${new Date(data.created_at).toLocaleDateString()}
        </div>
        ${data.description ? `<div class="mb-3"><strong class="text-primary">Description:</strong><br>${data.description}</div>` : ''}
        <div class="mb-3">
          <strong class="text-primary">Downloads:</strong> ${data.downloads_count}
        </div>
        ${data.resource_type === 'video' && data.video_url ? 
          `<div class="mb-3">
            <strong class="text-primary">Video Link:</strong><br>
            <a href="#" onclick="openVideoModal('${data.video_url}', '${data.title}')" class="text-primary">Click here to watch video</a>
          </div>` : ''}
      `;
        new bootstrap.Modal(document.getElementById('resourceModal')).show();
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to load resource details');
      });
  }

  // Clear video frame when modal closes
  document.getElementById('videoModal')?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('videoFrame').src = "";
  });
  </script>
  </script>
</body>

</html>