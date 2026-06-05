<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('index.html');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    
    if (($handle = fopen($file, "r")) !== FALSE) {
        $success = 0;
        $failed = 0;
        
        // Skip header row if exists
        fgetcsv($handle);
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $subject_code = trim($data[0]);
            $subject_name = trim($data[1]);
            $branch = trim($data[2]);
            $semester = trim($data[3]);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, branch, semester) VALUES (?, ?, ?, ?)");
                $stmt->execute([$subject_code, $subject_name, $branch, $semester]);
                $success++;
            } catch(PDOException $e) {
                $failed++;
            }
        }
        fclose($handle);
        
        $message = "Import completed! Added: $success, Failed: $failed";
    } else {
        $error = "Could not open file";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bulk Import Subjects</title>
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
    </div>
  </nav>

  <div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="glass-panel p-4">
          <h4 class="fw-bold mb-3">Bulk Import Subjects (CSV)</h4>

          <?php if ($message): ?>
          <div class="alert alert-success"><?php echo $message; ?></div>
          <?php endif; ?>

          <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
          <?php endif; ?>

          <div class="alert alert-info">
            <h6>CSV Format:</h6>
            <code>subject_code,subject_name,branch,semester</code><br>
            <small>Example:</small><br>
            <code>CS101,Engineering Mathematics-I,CSE,1</code><br>
            <code>CS102,Engineering Physics,CSE,1</code>
          </div>

          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label text-white">CSV File</label>
              <input type="file" name="csv_file" class="form-control bg-dark text-white border-secondary" accept=".csv"
                required>
            </div>
            <button type="submit" class="btn btn-glow w-100">
              <i class="fas fa-upload me-2"></i>Import Subjects
            </button>
          </form>

          <div class="mt-3">
            <a href="add-subjects.php" class="btn btn-outline-glow w-100">
              <i class="fas fa-arrow-left me-2"></i>Back to Add Subjects
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>