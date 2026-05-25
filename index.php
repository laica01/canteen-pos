<?php
session_start();
include 'db.php';

$error = '';
$success = '';
$active_tab = 'login';

/* ---- LOGIN ---- */
if(isset($_POST['login'])){
    $u = $conn->real_escape_string(trim($_POST['username']));
    $res = $conn->query("SELECT * FROM students WHERE username='$u'");
    $user = $res->fetch_assoc();

    if($user && password_verify($_POST['password'], $user['password'])){
        $_SESSION['student'] = $user;
        if($user['role'] == 'admin')        header("Location: admin.php");
        elseif($user['role'] == 'staff')    header("Location: staff_dashboard.php");
        else                                header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}

/* ---- REGISTER ---- */
if(isset($_POST['register'])){
    $active_tab = 'register';
    $n  = $conn->real_escape_string(trim($_POST['fullname']));
    $u  = $conn->real_escape_string(trim($_POST['username']));
    $pw = $_POST['password'];
    $r  = in_array($_POST['role'],['student','staff']) ? $_POST['role'] : 'student';

    if(strlen($pw) < 6){
        $error = "Password must be at least 6 characters.";
    } else {
        $check = $conn->query("SELECT id FROM students WHERE username='$u'");
        if($check->num_rows > 0){
            $error = "Username already taken. Try another.";
        } else {
            $p = password_hash($pw, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO students(fullname,username,password,balance,role)
                          VALUES('$n','$u','$p',0,'$r')");
            $success = "Account created! You can now log in.";
            $active_tab = 'login';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mura-Mura Canteen 🍱</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-wrap">
<div class="login-card">

  <div class="login-logo">
    <span class="big-emoji">🍱</span>
    <h1>Mura-Mura Canteen</h1>
    <p>Your school's favorite food spot 🧡</p>
  </div>

  <?php if($error): ?>
  <div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if($success): ?>
  <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <!-- TABS -->
  <div class="tab-btns">
    <button class="tab-btn <?php echo $active_tab=='login'?'active':''; ?>" onclick="switchTab('login')">Login</button>
    <button class="tab-btn <?php echo $active_tab=='register'?'active':''; ?>" onclick="switchTab('register')">Register</button>
  </div>

  <!-- LOGIN -->
  <div class="tab-pane <?php echo $active_tab=='login'?'active':''; ?>" id="tab-login">
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Username</label>
        <input class="form-control" name="username" placeholder="Enter username" required autocomplete="username">
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" placeholder="Enter password" required autocomplete="current-password">
      </div>
      <button class="btn btn-primary btn-w100" style="margin-top:6px;" name="login">Login 🚀</button>
    </form>
  </div>

  <!-- REGISTER -->
  <div class="tab-pane <?php echo $active_tab=='register'?'active':''; ?>" id="tab-register">
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input class="form-control" name="fullname" placeholder="Your full name" required>
      </div>
      <div class="form-group">
        <label class="form-label">Username</label>
        <input class="form-control" name="username" placeholder="Choose a username" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" placeholder="Min. 6 characters" required>
      </div>
      <div class="form-group">
        <label class="form-label">Role</label>
        <select class="form-control" name="role" required>
          <option value="student">Student</option>
          <option value="staff">Staff</option>
        </select>
      </div>
      <button class="btn btn-success btn-w100" style="margin-top:6px;" name="register">Create Account ✨</button>
    </form>
  </div>

</div>
</div>

<script>
function switchTab(tab){
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
  event.target.classList.add('active');
  document.getElementById('tab-'+tab).classList.add('active');
}
</script>
</body>
</html>
