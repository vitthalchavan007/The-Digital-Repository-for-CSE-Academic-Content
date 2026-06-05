<?php
require_once 'config.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] == 'faculty') {
        header("Location: faculty-dashboard.php");
        exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT u.*, f.* FROM users u 
                           JOIN faculty f ON u.id = f.user_id 
                           WHERE u.email = ? AND u.user_type = 'faculty'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Check approval status
        if ($user['status'] == 'pending') {
            $error = 'Your account is pending approval. Please wait for admin approval.';
        } elseif ($user['status'] == 'rejected') {
            $error = 'Your registration has been rejected. Please contact administration.';
        } else {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_type'] = 'faculty';
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['faculty_id'] = $user['faculty_id'];
            $_SESSION['department'] = $user['department'];
            $_SESSION['subject'] = $user['subject'];
            
            header("Location: faculty-dashboard.php");
            exit();
        }
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Login - GN Group</title>
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

  <div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
      <div class="col-md-6 col-lg-5 mx-auto">
        <div class="glass-panel p-4 p-md-5">
          <div class="text-center mb-4">
            <div class="display-4 text-primary mb-3">
              <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h2 class="fw-bold">Faculty Login</h2>
            <p class="text-white-50">Upload and manage course materials</p>
          </div>

          <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="floating-input-group">
              <input type="email" name="email" id="email" placeholder=" " required>
              <label>Faculty Email</label>
            </div>
            <div class="floating-input-group">
              <input type="password" name="password" id="password" placeholder=" " required>
              <label>Password</label>
            </div>

            <button type="submit" class="btn btn-glow w-100 py-3 mb-3">
              <i class="fas fa-sign-in-alt me-2"></i>Access Faculty Dashboard
            </button>

            <p class="text-center text-white-50 mb-0">
              Don't have an account?
              <a href="faculty-register.php" class="text-primary">Register as Faculty</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>