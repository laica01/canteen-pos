<?php
session_start();
include 'db.php';

/* FIX: was checking for 'student' role — should be 'staff' */
if(!isset($_SESSION['student']) || $_SESSION['student']['role'] != 'staff'){
    header("Location: index.php"); exit();
}

$id      = (int)$_SESSION['student']['id'];
$staff   = $conn->query("SELECT * FROM students WHERE id=$id")->fetch_assoc();

/* Staff can see all current orders */
$orders  = $conn->query("
    SELECT orders.*, students.fullname
    FROM orders
    JOIN students ON students.id = orders.student_id
    WHERE orders.status = 'Preparing'
    ORDER BY orders.id ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Dashboard — Mura-Mura Canteen</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-brand"><span class="emoji">👨‍🍳</span> Staff View</div>
  <div class="topbar-right">
    <span style="opacity:.8;font-size:.9rem;">Hi, <?php echo htmlspecialchars($staff['fullname']); ?>!</span>
    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</div>

<div class="page-wrap">

  <div class="section-title">⏳ Preparing Orders</div>
  <div class="section-sub">These orders are currently being prepared</div>

  <?php if($orders->num_rows == 0): ?>
  <div class="empty-state card" style="padding:48px;">
    <div class="emoji">✅</div>
    <p style="font-size:1.1rem;font-weight:700;margin-top:8px;">All clear! No pending orders right now.</p>
  </div>
  <?php else: ?>

  <div class="card" style="overflow:hidden;">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Student</th>
          <th>Food</th>
          <th>Qty</th>
          <th>Total</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($o=$orders->fetch_assoc()): ?>
        <tr>
          <td style="color:var(--muted);font-size:0.82rem;">#<?php echo $o['id']; ?></td>
          <td><strong><?php echo htmlspecialchars($o['fullname']); ?></strong></td>
          <td><?php echo htmlspecialchars($o['product_name']); ?></td>
          <td>x<?php echo $o['quantity']; ?></td>
          <td style="color:var(--orange);font-weight:800;">₱<?php echo number_format($o['total'],2); ?></td>
          <td>
            <a href="complete.php?id=<?php echo $o['id']; ?>" class="btn btn-success btn-sm">✓ Mark Done</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <?php endif; ?>

  <p style="margin-top:16px;font-size:0.82rem;color:var(--muted);">
    💡 This page shows orders for the current queue. Refresh to see new orders.
  </p>

</div>

<script>
/* Auto-refresh every 30 seconds */
setTimeout(()=>location.reload(), 30000);
</script>
</body>
</html>
