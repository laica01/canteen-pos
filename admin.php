<?php
session_start();
include 'db.php';

if(!isset($_SESSION['student']) || $_SESSION['student']['role'] != 'admin'){
    header("Location: index.php"); exit();
}

/* Stats */
$totalOrders   = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$todayRevenue  = $conn->query("SELECT COALESCE(SUM(total),0) s FROM orders WHERE DATE(created_at)=CURDATE() AND status='Completed'")->fetch_assoc()['s'] ?? 0;
$pending       = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='Preparing'")->fetch_assoc()['c'];
$totalStudents = $conn->query("SELECT COUNT(*) c FROM students WHERE role='student'")->fetch_assoc()['c'];

/* Orders grouped by student */
$orders = $conn->query("
    SELECT orders.*, students.fullname, students.username
    FROM orders
    JOIN students ON students.id = orders.student_id
    ORDER BY orders.id DESC
");

$data = [];
while($row = $orders->fetch_assoc()){
    $key = $row['student_id'];
    $data[$key]['info'] = ['fullname'=>$row['fullname'], 'username'=>$row['username']];
    $data[$key]['orders'][] = $row;
}

/* Products list */
$products = $conn->query("SELECT * FROM products ORDER BY product_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — Mura-Mura Canteen</title>
<link rel="stylesheet" href="style.css">
<style>
.admin-tabs { display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; }
.admin-tab  { padding:9px 22px; border-radius:50px; border:2px solid #e0e0e0; background:white; font-weight:700; font-size:0.88rem; cursor:pointer; transition:0.2s; }
.admin-tab.active { border-color:var(--orange); background:var(--orange); color:white; }
.tab-section { display:none; } .tab-section.active { display:block; }
.product-row { display:flex; align-items:center; gap:14px; padding:12px 16px; border-bottom:1px solid #f0f0f0; }
.product-row:last-child { border-bottom:none; }
.product-thumb { width:52px; height:52px; object-fit:cover; border-radius:8px; background:#ffe0cc; display:flex; align-items:center; justify-content:center; font-size:1.6rem; flex-shrink:0; }
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-brand"><span class="emoji">🍱</span> Admin Panel</div>
  <div class="topbar-right">
    <a href="add_product.php" class="btn btn-sm" style="background:rgba(255,255,255,0.22);color:white;">+ Add Food</a>
    <a href="add_balance.php" class="btn btn-sm" style="background:rgba(255,255,255,0.22);color:white;">+ Top Up</a>
    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</div>

<div class="page-wrap">

  <!-- STATS -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-label">Total Orders</div>
      <div class="stat-val">📦 <?php echo $totalOrders; ?></div>
    </div>
    <div class="stat-card green">
      <div class="stat-label">Today's Revenue</div>
      <div class="stat-val" style="color:var(--green2)">₱<?php echo number_format($todayRevenue,0); ?></div>
    </div>
    <div class="stat-card" style="border-color:#E67E22;">
      <div class="stat-label">Pending Orders</div>
      <div class="stat-val" style="color:#E67E22;">⏳ <?php echo $pending; ?></div>
    </div>
    <div class="stat-card blue">
      <div class="stat-label">Students</div>
      <div class="stat-val" style="color:var(--blue);">👤 <?php echo $totalStudents; ?></div>
    </div>
  </div>

  <!-- TABS -->
  <div class="admin-tabs">
    <button class="admin-tab active" onclick="switchAdminTab('orders',this)">📋 All Orders</button>
    <button class="admin-tab" onclick="switchAdminTab('products',this)">🍔 Products</button>
  </div>

  <!-- ORDERS TAB -->
  <div class="tab-section active" id="tab-orders">
    <?php if(empty($data)): ?>
    <div class="empty-state"><div class="emoji">📭</div><p>No orders yet!</p></div>
    <?php else: foreach($data as $sid => $group): ?>

    <div class="order-group">
      <div class="order-group-header">
        <div>
          <strong><?php echo htmlspecialchars($group['info']['fullname']); ?></strong>
          <small style="color:var(--muted);margin-left:6px;">@<?php echo htmlspecialchars($group['info']['username']); ?></small>
        </div>
        <span style="font-size:0.78rem;color:var(--muted);"><?php echo count($group['orders']); ?> orders</span>
      </div>
      <div class="order-group-body">
        <table class="data-table">
          <thead>
            <tr>
              <th>Food</th>
              <th>Qty</th>
              <th>Total</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($group['orders'] as $o):
              $sClass = ['Preparing'=>'preparing','Completed'=>'completed','Cancelled'=>'cancelled'][$o['status']] ?? 'preparing';
            ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($o['product_name']); ?></strong></td>
              <td>x<?php echo $o['quantity']; ?></td>
              <td style="color:var(--orange);font-weight:800;">₱<?php echo number_format($o['total'],2); ?></td>
              <td><span class="status-badge status-<?php echo $sClass; ?>"><?php echo $o['status']; ?></span></td>
              <td>
                <?php if($o['status']=='Preparing'): ?>
                <a href="complete.php?id=<?php echo $o['id']; ?>" class="btn btn-success btn-xs">✓ Done</a>
                <?php else: ?>
                <span style="color:#aaa;font-size:0.78rem;">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php endforeach; endif; ?>
  </div>

  <!-- PRODUCTS TAB -->
  <div class="tab-section" id="tab-products">

    <?php if(isset($_GET['deleted'])): ?>
    <div class="alert alert-success">✅ Product deleted successfully.</div>
    <?php endif; ?>
    <?php if(isset($_GET['updated'])): ?>
    <div class="alert alert-success">✅ Stock updated successfully.</div>
    <?php endif; ?>

    <div class="card" style="overflow:hidden;">
      <?php $products->data_seek(0); while($p=$products->fetch_assoc()): ?>
      <div class="product-row" style="flex-wrap:wrap;gap:10px;">

        <?php if(!empty($p['image']) && file_exists("images/".$p['image'])): ?>
          <img class="product-thumb" src="images/<?php echo htmlspecialchars($p['image']); ?>" alt="">
        <?php else: ?>
          <div class="product-thumb">🍽️</div>
        <?php endif; ?>

        <div style="flex:1;min-width:120px;">
          <div style="font-weight:800;"><?php echo htmlspecialchars($p['product_name']); ?></div>
          <div style="font-size:0.83rem;color:var(--muted);">Stock: <?php echo $p['stock']; ?></div>
        </div>

        <div style="font-weight:900;color:var(--orange);">₱<?php echo number_format($p['price'],2); ?></div>

        <span class="status-badge <?php echo $p['stock']>0?'status-completed':'status-cancelled'; ?>">
          <?php echo $p['stock']>0?'Available':'Sold Out'; ?>
        </span>

        <!-- EDIT STOCK -->
        <button class="btn btn-outline btn-xs" onclick="openStockModal(<?php echo $p['id']; ?>,'<?php echo addslashes($p['product_name']); ?>',<?php echo $p['stock']; ?>)">
          ✏️ Stock
        </button>

        <!-- DELETE -->
        <a href="delete_product.php?id=<?php echo $p['id']; ?>"
           class="btn btn-danger btn-xs"
           onclick="return confirm('Delete \'<?php echo addslashes($p['product_name']); ?>\'? This cannot be undone.')">
          🗑️ Delete
        </a>

      </div>
      <?php endwhile; ?>
    </div>

    <div style="margin-top:14px;">
      <a href="add_product.php" class="btn btn-primary">+ Add New Food Item</a>
    </div>
  </div>

  <!-- EDIT STOCK MODAL -->
  <div class="modal-overlay" id="modal-stock">
    <div class="modal-box" style="max-width:360px;">
      <button class="modal-close" onclick="closeStockModal()">✕</button>
      <h3 style="margin-bottom:14px;">✏️ Update Stock</h3>
      <p id="stock-product-name" style="font-weight:700;color:var(--orange);margin-bottom:14px;"></p>
      <form method="POST" action="update_stock.php">
        <input type="hidden" name="product_id" id="stock-pid">
        <div class="form-group">
          <label class="form-label">New Stock Quantity</label>
          <input class="form-control" type="number" name="stock" id="stock-input" min="0" required>
        </div>
        <button class="btn btn-primary btn-w100" type="submit">Save Stock</button>
      </form>
    </div>
  </div>

</div>

<script>
function switchAdminTab(id, el){
  document.querySelectorAll('.admin-tab').forEach(b=>b.classList.remove('active'));
  document.querySelectorAll('.tab-section').forEach(s=>s.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('tab-'+id).classList.add('active');
}

function openStockModal(id, name, stock){
  document.getElementById('stock-pid').value = id;
  document.getElementById('stock-product-name').textContent = name;
  document.getElementById('stock-input').value = stock;
  document.getElementById('modal-stock').classList.add('active');
}

function closeStockModal(){
  document.getElementById('modal-stock').classList.remove('active');
}

document.getElementById('modal-stock').addEventListener('click', function(e){
  if(e.target === this) closeStockModal();
});

/* Auto-open products tab if redirected after delete/update */
const params = new URLSearchParams(window.location.search);
if(params.has('deleted') || params.has('updated')){
  document.querySelectorAll('.admin-tab')[1].click();
}
</script>
</body>
</html>