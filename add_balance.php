<?php
session_start();
include 'db.php';

if(!isset($_SESSION['student']) || $_SESSION['student']['role'] != 'admin'){
    header("Location: index.php"); exit();
}

$msg = '';
$msgType = '';

if(isset($_POST['add'])){
    $username = $conn->real_escape_string(trim($_POST['username']));
    $amount   = (float)$_POST['amount'];

    if($amount <= 0){
        $msg = "Amount must be greater than 0."; $msgType = 'danger';
    } else {
        $user = $conn->query("SELECT * FROM students WHERE username='$username' AND role='student'")->fetch_assoc();
        if(!$user){
            $msg = "Student '$username' not found."; $msgType = 'danger';
        } else {
            $conn->query("UPDATE students SET balance=balance+$amount WHERE username='$username'");
            $newBal = $conn->query("SELECT balance FROM students WHERE username='$username'")->fetch_assoc()['balance'];
            $msg = "✅ ₱".number_format($amount,2)." added to {$user['fullname']}. New balance: ₱".number_format($newBal,2);
            $msgType = 'success';
        }
    }
}

/* Get all students for datalist */
$students = $conn->query("SELECT username, fullname FROM students WHERE role='student' ORDER BY fullname");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Top Up Balance — Mura-Mura Canteen</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-brand"><span class="emoji">🍱</span> Top Up Wallet</div>
  <div class="topbar-right">
    <a href="admin.php" class="btn btn-sm" style="background:rgba(255,255,255,0.22);color:white;">← Back</a>
    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</div>

<div class="page-wrap" style="max-width:560px;">

  <div class="section-title">💰 Add Student Balance</div>
  <div class="section-sub">Deposit money into a student's wallet (parent top-up)</div>

  <?php if($msg): ?>
  <div class="alert alert-<?php echo $msgType; ?>"><?php echo $msg; ?></div>
  <?php endif; ?>

  <div class="card" style="padding:28px;">
    <form method="POST">

      <div class="form-group">
        <label class="form-label">Student Username</label>
        <input class="form-control" name="username" placeholder="Type username..." list="student-list" required autocomplete="off">
        <datalist id="student-list">
          <?php while($s=$students->fetch_assoc()): ?>
          <option value="<?php echo htmlspecialchars($s['username']); ?>"><?php echo htmlspecialchars($s['fullname']); ?></option>
          <?php endwhile; ?>
        </datalist>
        <small style="color:var(--muted);font-size:0.78rem;">Start typing to search students</small>
      </div>

      <div class="form-group">
        <label class="form-label">Amount to Add (₱)</label>
        <input class="form-control" name="amount" type="number" min="1" step="0.01" placeholder="e.g. 1000" required>
      </div>

      <!-- Quick amount buttons -->
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
        <span style="font-size:0.82rem;color:var(--muted);width:100%;margin-bottom:2px;">Quick amounts:</span>
        <?php foreach([50,100,200,500,1000] as $amt): ?>
        <button type="button" class="btn btn-outline btn-sm" onclick="document.querySelector('[name=amount]').value=<?php echo $amt; ?>">
          ₱<?php echo $amt; ?>
        </button>
        <?php endforeach; ?>
      </div>

      <button class="btn btn-success btn-w100" name="add">💰 Add Balance</button>
    </form>
  </div>

</div>
</body>
</html>
