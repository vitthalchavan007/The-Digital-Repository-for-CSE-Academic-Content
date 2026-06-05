<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'faculty') {
        header("Location: faculty-dashboard.php");
        exit();
    }
}

$error = '';
$success = '';
$branches = ['CSE', 'IT', 'AIDS', 'AIML', 'CSEDS', 'CE', 'ME', 'ECE', 'HUM'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $faculty_id = $_POST['faculty_id'] ?? '';
    $email = $_POST['email'] ?? '';
    $department = $_POST['department'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($first_name) || empty($last_name) || empty($faculty_id) || empty($email) || empty($department) || empty($subject) || empty($password)) {
        $error = 'All fields are required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        try {
            $pdo->beginTransaction();
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Status 'pending' se save hoga
            $stmt = $pdo->prepare("INSERT INTO users (user_type, email, password, first_name, last_name, status) VALUES ('faculty', ?, ?, ?, ?, 'pending')");
            $stmt->execute([$email, $hashed_password, $first_name, $last_name]);
            
            $user_id = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("INSERT INTO faculty (user_id, faculty_id, department, subject, college) VALUES (?, ?, ?, ?, 'GNIT')");
            $stmt->execute([$user_id, $faculty_id, $department, $subject]);
            
            $pdo->commit();
            $success = 'Registration submitted! Waiting for admin approval.';
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            if ($e->errorInfo[1] == 1062) {
                $error = 'Email or Faculty ID already exists';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Registration - GN Group</title>
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

  <div class="container py-5 mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="glass-panel p-4">
          <div class="text-center mb-4">
            <h2 class="fw-bold">Faculty Registration</h2>
            <p class="text-white-50">Join as a faculty member (Approval Required)</p>
          </div>

          <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
          <?php endif; ?>

          <?php if ($success): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <br><small>You will be able to login after admin approval.</small>
          </div>
          <?php endif; ?>

          <form method="POST">
            <div class="row">
              <div class="col-md-6">
                <div class="floating-input-group">
                  <input type="text" name="first_name" id="first_name" placeholder=" " required>
                  <label>First Name *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group">
                  <input type="text" name="last_name" id="last_name" placeholder=" " required>
                  <label>Last Name *</label>
                </div>
              </div>
            </div>

            <div class="floating-input-group">
              <input type="text" name="faculty_id" id="faculty_id" placeholder=" " required>
              <label>Faculty ID Number *</label>
            </div>

            <div class="floating-input-group">
              <input type="email" name="email" id="email" placeholder=" " required>
              <label>Email Address *</label>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="floating-input-group">
                  <select name="department" id="department" required>
                    <option value="" disabled selected></option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b; ?>"><?php echo $b; ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label>Department *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group">
                  <input type="text" name="subject" id="subject" placeholder=" " required>
                  <label>Primary Subject *</label>
                </div>
              </div>
            </div>

            <div class="floating-input-group">
              <input type="password" name="password" id="password" placeholder=" " required>
              <label>Create Password *</label>
            </div>

            <button type="submit" class="btn btn-glow w-100 py-3">Submit Registration</button>

            <p class="text-center text-white-50 mt-3">
              Already have an account? <a href="faculty-login.php" class="text-primary">Login here</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>