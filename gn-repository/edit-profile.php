<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.html');
}

$message = '';
$error = '';

// Get current user data
if (isStudent()) {
    $stmt = $pdo->prepare("SELECT u.*, s.* FROM users u 
                           JOIN students s ON u.id = s.user_id 
                           WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_type = 'student';
} else if (isFaculty()) {
    $stmt = $pdo->prepare("SELECT u.*, f.* FROM users u 
                           JOIN faculty f ON u.id = f.user_id 
                           WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_type = 'faculty';
} else {
    redirect('admin-dashboard.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Student specific fields
    $student_id = $_POST['student_id'] ?? '';
    $branch = $_POST['branch'] ?? '';
    $year = $_POST['year'] ?? '';
    $semester = $_POST['semester'] ?? '';
    
    // Faculty specific fields
    $faculty_id = $_POST['faculty_id'] ?? '';
    $department = $_POST['department'] ?? '';
    $subject = $_POST['subject'] ?? '';
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = 'Name and email are required fields.';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Update users table
            if (!empty($password)) {
                if ($password !== $confirm_password) {
                    $error = 'Passwords do not match.';
                    throw new Exception($error);
                }
                if (strlen($password) < 6) {
                    $error = 'Password must be at least 6 characters.';
                    throw new Exception($error);
                }
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $email, $hashed_password, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $email, $_SESSION['user_id']]);
            }
            
            // Update role-specific table
            if ($user_type == 'student') {
                $stmt = $pdo->prepare("UPDATE students SET student_id = ?, branch = ?, year = ?, semester = ? WHERE user_id = ?");
                $stmt->execute([$student_id, $branch, $year, $semester, $_SESSION['user_id']]);
                
                // Update session data
                $_SESSION['name'] = $first_name . ' ' . $last_name;
                $_SESSION['student_id'] = $student_id;
                $_SESSION['branch'] = $branch;
                $_SESSION['year'] = $year;
                $_SESSION['semester'] = $semester;
            } else if ($user_type == 'faculty') {
                $stmt = $pdo->prepare("UPDATE faculty SET faculty_id = ?, department = ?, subject = ? WHERE user_id = ?");
                $stmt->execute([$faculty_id, $department, $subject, $_SESSION['user_id']]);
                
                // Update session data
                $_SESSION['name'] = $first_name . ' ' . $last_name;
                $_SESSION['faculty_id'] = $faculty_id;
                $_SESSION['department'] = $department;
                $_SESSION['subject'] = $subject;
            }
            
            $pdo->commit();
            $message = 'Profile updated successfully!';
            
            // Refresh user data
            if (isStudent()) {
                $stmt = $pdo->prepare("SELECT u.*, s.* FROM users u 
                                       JOIN students s ON u.id = s.user_id 
                                       WHERE u.id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else if (isFaculty()) {
                $stmt = $pdo->prepare("SELECT u.*, f.* FROM users u 
                                       JOIN faculty f ON u.id = f.user_id 
                                       WHERE u.id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            if (empty($error)) {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}

$branches = ['CSE', 'IT', 'AIDS', 'AIML', 'CSEDS', 'CE', 'ME', 'ECE'];
$departments = ['CSE', 'IT', 'AIDS', 'AIML', 'CSEDS', 'CE', 'ME', 'ECE', 'HUM'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile - GNIT Repository</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
  .profile-avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--primary), var(--purple));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
  }

  .profile-avatar i {
    font-size: 3rem;
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
      <div class="ms-auto d-flex align-items-center">
        <div class="bg-gradient rounded-circle me-2 d-flex align-items-center justify-content-center"
          style="width:35px; height:35px; background: var(--purple);">
          <i class="fas fa-user-edit small"></i>
        </div>
        <span class="text-white me-3"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
        <a href="<?php echo isStudent() ? 'student-dashboard.php' : (isFaculty() ? 'faculty-dashboard.php' : 'admin-dashboard.php'); ?>"
          class="btn btn-sm btn-outline-glow me-2">
          <i class="fas fa-arrow-left"></i> Back
        </a>
        <a href="logout.php" class="btn btn-sm btn-danger rounded-pill px-3">
          <i class="fas fa-power-off"></i>
        </a>
      </div>
    </div>
  </nav>

  <div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="glass-panel p-4">
          <div class="text-center mb-4">
            <div class="profile-avatar">
              <i class="fas fa-user-edit"></i>
            </div>
            <h2 class="fw-bold">Edit Profile</h2>
            <p class="text-white-50">Update your personal information</p>
          </div>

          <?php if ($message): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>

          <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>

          <form method="POST">
            <div class="row">
              <div class="col-md-6">
                <div class="floating-input-group mb-3">
                  <input type="text" name="first_name" id="first_name" placeholder=" "
                    value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                  <label>First Name *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group mb-3">
                  <input type="text" name="last_name" id="last_name" placeholder=" "
                    value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                  <label>Last Name *</label>
                </div>
              </div>
            </div>

            <div class="floating-input-group mb-3">
              <input type="email" name="email" id="email" placeholder=" "
                value="<?php echo htmlspecialchars($user['email']); ?>" required>
              <label>Email Address *</label>
            </div>

            <?php if ($user_type == 'student'): ?>
            <div class="row">
              <div class="col-md-6">
                <div class="floating-input-group mb-3">
                  <input type="text" name="student_id" id="student_id" placeholder=" "
                    value="<?php echo htmlspecialchars($user['student_id']); ?>" required>
                  <label>Student ID *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group mb-3">
                  <select name="branch" id="branch" required>
                    <option value="" disabled>Select Branch</option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b; ?>" <?php echo ($user['branch'] == $b) ? 'selected' : ''; ?>>
                      <?php echo $b; ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                  <label>Branch *</label>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="floating-input-group mb-3">
                  <select name="year" id="year" required onchange="updateSemesters()">
                    <option value="" disabled>Select Year</option>
                    <option value="1" <?php echo ($user['year'] == 1) ? 'selected' : ''; ?>>1st Year</option>
                    <option value="2" <?php echo ($user['year'] == 2) ? 'selected' : ''; ?>>2nd Year</option>
                    <option value="3" <?php echo ($user['year'] == 3) ? 'selected' : ''; ?>>3rd Year</option>
                    <option value="4" <?php echo ($user['year'] == 4) ? 'selected' : ''; ?>>4th Year</option>
                  </select>
                  <label>Year *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group mb-3">
                  <select name="semester" id="semester" required>
                    <option value="" disabled>Select Semester</option>
                  </select>
                  <label>Semester *</label>
                </div>
              </div>
            </div>
            <?php elseif ($user_type == 'faculty'): ?>
            <div class="row">
              <div class="col-md-6">
                <div class="floating-input-group mb-3">
                  <input type="text" name="faculty_id" id="faculty_id" placeholder=" "
                    value="<?php echo htmlspecialchars($user['faculty_id']); ?>" required>
                  <label>Faculty ID *</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="floating-input-group mb-3">
                  <select name="department" id="department" required>
                    <option value="" disabled>Select Department</option>
                    <?php foreach ($departments as $d): ?>
                    <option value="<?php echo $d; ?>" <?php echo ($user['department'] == $d) ? 'selected' : ''; ?>>
                      <?php echo $d; ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                  <label>Department *</label>
                </div>
              </div>
            </div>

            <div class="floating-input-group mb-3">
              <input type="text" name="subject" id="subject" placeholder=" "
                value="<?php echo htmlspecialchars($user['subject']); ?>" required>
              <label>Primary Subject *</label>
            </div>
            <?php endif; ?>

            <div class="border-top border-secondary pt-3 mt-3">
              <h5 class="text-primary mb-3">Change Password (Optional)</h5>
              <div class="row">
                <div class="col-md-6">
                  <div class="floating-input-group mb-3">
                    <input type="password" name="password" id="password" placeholder=" ">
                    <label>New Password</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group mb-3">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder=" ">
                    <label>Confirm New Password</label>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex gap-2 mt-4">
              <button type="submit" class="btn btn-glow flex-grow-1">
                <i class="fas fa-save me-2"></i> Save Changes
              </button>
              <a href="<?php echo isStudent() ? 'student-dashboard.php' : 'faculty-dashboard.php'; ?>"
                class="btn btn-outline-glow">
                Cancel
              </a>
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
    const currentSemester = <?php echo json_encode($user['semester'] ?? ''); ?>;

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

  // Call updateSemesters on page load to populate semester dropdown
  document.addEventListener('DOMContentLoaded', function() {
    updateSemesters();
  });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>