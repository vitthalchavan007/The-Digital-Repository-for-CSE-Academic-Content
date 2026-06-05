<?php
require_once 'config.php';

if (!isLoggedIn() || !isStudent()) {
    redirect('login.php');
}

// Get student ID from session
$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
$student_id = $student['id'];

// Handle save note
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'save') {
        $title = trim($_POST['title'] ?? 'Untitled Note');
        $content = trim($_POST['content'] ?? '');
        
        if (empty($content)) {
            $_SESSION['error'] = 'Note content cannot be empty.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO student_notes (student_id, title, content) VALUES (?, ?, ?)");
            $stmt->execute([$student_id, $title, $content]);
            $_SESSION['message'] = 'Note saved successfully!';
        }
        redirect('my-notes.php');
    } elseif ($_POST['action'] == 'delete') {
        $note_id = $_POST['note_id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM student_notes WHERE id = ? AND student_id = ?");
        $stmt->execute([$note_id, $student_id]);
        $_SESSION['message'] = 'Note deleted successfully!';
        redirect('my-notes.php');
    } elseif ($_POST['action'] == 'update') {
        $note_id = $_POST['note_id'] ?? 0;
        $title = trim($_POST['title'] ?? 'Untitled Note');
        $content = trim($_POST['content'] ?? '');
        
        if (empty($content)) {
            $_SESSION['error'] = 'Note content cannot be empty.';
        } else {
            $stmt = $pdo->prepare("UPDATE student_notes SET title = ?, content = ? WHERE id = ? AND student_id = ?");
            $stmt->execute([$title, $content, $note_id, $student_id]);
            $_SESSION['message'] = 'Note updated successfully!';
        }
        redirect('my-notes.php');
    }
}

// Get all notes
$stmt = $pdo->prepare("SELECT * FROM student_notes WHERE student_id = ? ORDER BY updated_at DESC");
$stmt->execute([$student_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Notes - GNIT Repository</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/dashboard.css">
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
      <div class="col-md-8 mx-auto">
        <div class="glass-panel p-4 mb-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Create New Note</h4>
            <a href="student-dashboard.php" class="btn btn-outline-glow btn-sm">
              <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
          </div>
          
          <form method="POST">
            <input type="hidden" name="action" value="save">

            <div class="floating-input-group mb-3">
              <input type="text" name="title" id="title" placeholder=" " value="Untitled Note">
              <label>Note Title</label>
            </div>

            <div class="mb-3">
              <textarea name="content" id="content" class="note-editor w-100" rows="8"
                placeholder="Start typing your notes here..." required></textarea>
            </div>

            <button type="submit" class="btn btn-glow w-100 py-3">
              <i class="fas fa-save me-2"></i> Save Note
            </button>
          </form>
        </div>

        <div class="glass-panel p-4">
          <h4 class="fw-bold mb-4">My Saved Notes (<?php echo count($notes); ?>)</h4>

          <?php if (empty($notes)): ?>
          <div class="text-center text-white-50 py-5">
            <i class="fas fa-edit fa-3x mb-3 opacity-50"></i>
            <p class="mb-0">No saved notes yet. Create your first note above!</p>
          </div>
          <?php endif; ?>

          <?php foreach ($notes as $note): ?>
          <div class="enhanced-card p-3 mb-3">
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($note['title']); ?></h6>
                <small class="text-white-50">
                  <i class="far fa-calendar-alt me-1"></i> 
                  Last updated: <?php echo date('M d, Y h:i A', strtotime($note['updated_at'])); ?>
                </small>
              </div>
              <div class="btn-group">
                <button class="btn btn-sm btn-outline-primary me-2" onclick="editNote(<?php echo htmlspecialchars(json_encode($note)); ?>)">
                  <i class="fas fa-edit"></i>
                </button>
                <form method="POST" onsubmit="return confirm('Delete this note?')" class="d-inline">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </div>
            </div>
            <p class="text-white-70 mt-2 note-preview">
              <?php echo nl2br(htmlspecialchars(substr($note['content'], 0, 150))); ?>
              <?php if (strlen($note['content']) > 150): ?>
              <button class="btn btn-link text-primary p-0 ms-1" onclick="viewFullNote(<?php echo htmlspecialchars(json_encode($note)); ?>)">
                Read more...
              </button>
              <?php endif; ?>
            </p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Note Modal -->
  <div class="modal fade custom-modal" id="editNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content p-4">
        <div class="modal-header border-0">
          <h5 class="fw-bold">Edit Note</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="note_id" id="edit_note_id">
            
            <div class="floating-input-group mb-3">
              <input type="text" name="title" id="edit_title" placeholder=" " required>
              <label>Note Title</label>
            </div>
            
            <div class="mb-3">
              <textarea name="content" id="edit_content" class="note-editor w-100" rows="8" required></textarea>
            </div>
            
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-glow flex-grow-1">
                <i class="fas fa-save me-2"></i> Update Note
              </button>
              <button type="button" class="btn btn-outline-glow" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- View Full Note Modal -->
  <div class="modal fade custom-modal" id="viewNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content p-4">
        <div class="modal-header border-0">
          <h5 class="fw-bold" id="viewNoteTitle"></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <small class="text-white-50 d-block mb-3" id="viewNoteDate"></small>
          <div class="bg-dark p-4 rounded" id="viewNoteContent" style="white-space: pre-wrap; word-wrap: break-word;"></div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function editNote(note) {
    document.getElementById('edit_note_id').value = note.id;
    document.getElementById('edit_title').value = note.title;
    document.getElementById('edit_content').value = note.content;
    new bootstrap.Modal(document.getElementById('editNoteModal')).show();
  }
  
  function viewFullNote(note) {
    document.getElementById('viewNoteTitle').textContent = note.title;
    document.getElementById('viewNoteDate').textContent = 'Last updated: ' + new Date(note.updated_at).toLocaleString();
    document.getElementById('viewNoteContent').textContent = note.content;
    new bootstrap.Modal(document.getElementById('viewNoteModal')).show();
  }
  </script>
</body>

</html>