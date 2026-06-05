<?php
require_once 'config.php';

if (isLoggedIn()) {
    if (isStudent()) redirect('student-dashboard.php');
    if (isFaculty()) redirect('faculty-dashboard.php');
}

$error = '';
$success = '';
$branches = ['CSE', 'IT', 'AIDS', 'AIML', 'CSEDS', 'CE', 'ME', 'ECE'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $email = $_POST['email'] ?? '';
    $branch = $_POST['branch'] ?? '';
    $year = $_POST['year'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($student_id) || empty($email) || empty($branch) || empty($year) || empty($semester) || empty($password)) {
        $error = 'All fields are required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Insert into users table
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (user_type, email, password, first_name, last_name) VALUES ('student', ?, ?, ?, ?)");
            $stmt->execute([$email, $hashed_password, $first_name, $last_name]);
            
            $user_id = $pdo->lastInsertId();
            
            // Insert into students table
            $stmt = $pdo->prepare("INSERT INTO students (user_id, student_id, branch, year, semester, college) VALUES (?, ?, ?, ?, ?, 'GNIT')");
            $stmt->execute([$user_id, $student_id, $branch, $year, $semester]);
            
            $pdo->commit();
            $success = 'Registration successful! You can now login.';
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            if ($e->errorInfo[1] == 1062) {
                $error = 'Email or Student ID already exists';
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
  <title>Student Registration - GN Group</title>
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
            <h2 class="fw-bold">Student Registration</h2>
            <p class="text-white-50">Create your account to access learning materials</p>
          </div>

          <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
          <?php endif; ?>

          <?php if ($success): ?>
          <div class="alert alert-success"><?php echo $success; ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="row">
              <div class="col-md-6">
                <div class="floating-input-group">
                  <input type="text" name="first_name" id="first_name" placeholder=" " required>
                  <label>First Name</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group">
                  <input type="text" name="last_name" id="last_name" placeholder=" " required>
                  <label>Last Name</label>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="floating-input-group">
                  <input type="text" name="student_id" id="student_id" placeholder=" " required>
                  <label>Student ID</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group">
                  <input type="email" name="email" id="email" placeholder=" " required>
                  <label>Email Address</label>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="floating-input-group">
                  <select name="branch" id="branch" required>
                    <option value="" disabled selected></option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b; ?>"><?php echo $b; ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label>Branch</label>
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
                  <label>Year</label>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="floating-input-group">
                  <select name="semester" id="semester" required>
                    <option value="" disabled selected>Select Year first</option>
                  </select>
                  <label>Semester</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group">
                  <input type="password" name="password" id="password" placeholder=" " required>
                  <label>Password</label>
                </div>
              </div>
            </div>

            <button type="submit" class="btn btn-glow w-100 py-3">Register</button>

            <p class="text-center text-white-50 mt-3">
              Already have an account? <a href="login.php" class="text-primary">Login here</a>
            </p>
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
  </script>
</body>

</html>