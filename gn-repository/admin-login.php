<?php
session_start();
require_once 'config.php';

// Agar already logged in hai toh dashboard pe bhejo
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin-dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';
    
    // ✅ Fixed Admin Password - "admin123"
    $admin_password = 'admin123';
    
    if ($password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = 'Administrator';
        header("Location: admin-dashboard.php");
        exit();
    } else {
        $error = 'Invalid admin password!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - GNIT Repository</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
  .admin-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #dc3545, #ff6b6b);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
  }

  .admin-icon i {
    font-size: 2.5rem;
    color: white;
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
      <a href="index.html" class="btn btn-outline-glow">Back to Home</a>
    </div>
  </nav>

  <div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
      <div class="col-md-5 col-lg-4 mx-auto">
        <div class="glass-panel p-4 p-md-5">
          <div class="text-center mb-4">
            <div class="admin-icon">
              <i class="fas fa-user-shield"></i>
            </div>
            <h2 class="fw-bold">Admin Login</h2>
            <p class="text-white-50">Enter admin password to access dashboard</p>
          </div>

          <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="floating-input-group">
              <input type="password" name="password" id="password" placeholder=" " required>
              <label>Admin Password</label>
            </div>

            <button type="submit" class="btn btn-danger w-100 py-3">
              <i class="fas fa-sign-in-alt me-2"></i>Login to Admin Panel
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>