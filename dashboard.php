<?php
session_start();
include 'db.php';

if(!isset($_SESSION['student']) || $_SESSION['student']['role'] != 'student'){
    header("Location: index.php"); exit();
}

/* Refresh student data */
$id = (int)$_SESSION['student']['id'];
$student = $conn->query("SELECT * FROM students WHERE id=$id")->fetch_assoc();
$_SESSION['student'] = $student;

$products = $conn->query("SELECT * FROM products ORDER BY status='Available' DESC, product_name ASC");

/* Flash message from order.php */
$flash = '';
if(isset($_SESSION['flash'])){
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
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

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-brand">
    <span class="emoji">🍱</span>
    Mura-Mura Canteen
  </div>
  <div class="topbar-right">
    <div class="balance-chip">
      💰 ₱<?php echo number_format($student['balance'],2); ?>
    </div>
    <button class="btn btn-dark btn-sm" onclick="openModal('modal-history')">📋 Orders</button>
    <a href="logout.php" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:white;">Logout</a>
  </div>
</div>

<div class="page-wrap">

  <!-- GREETING -->
  <div style="margin-bottom:18px;">
    <div class="section-title">👋 Hi, <?php echo htmlspecialchars(explode(' ',$student['fullname'])[0]); ?>!</div>
    <div class="section-sub">What would you like to eat today? 😋</div>
  </div>

  <?php if($flash): ?>
  <div class="alert alert-<?php echo strpos($flash,'✅')!==false?'success':'warning'; ?>" id="flash-msg">
    <?php echo $flash; ?>
  </div>
  <?php endif; ?>

  <!-- FOOD GRID -->
  <div class="food-grid">
    <?php while($p = $products->fetch_assoc()):
      $isOut = $p['stock'] <= 0;
      $isLow = !$isOut && $p['stock'] <= 5;
      $onclick = $isOut ? '' : "openOrderModal({$p['id']},'" . addslashes($p['product_name']) . "',{$p['price']},{$p['stock']})";
    ?>
    <div class="food-card" onclick="<?php echo $onclick; ?>">

      <?php if(!empty($p['image']) && file_exists("images/".$p['image'])): ?>
        <img src="images/<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['product_name']); ?>" loading="lazy">
      <?php else: ?>
        <div class="food-img-placeholder">🍽️</div>
      <?php endif; ?>

      <?php if($isOut): ?>
        <div class="sold-out-overlay"><span class="sold-out-tag">SOLD OUT</span></div>
      <?php endif; ?>

      <div class="food-card-body">
        <div class="food-name"><?php echo htmlspecialchars($p['product_name']); ?></div>

        <?php if($isOut): ?>
          <span class="stock-badge out">Out of stock</span>
        <?php elseif($isLow): ?>
          <span class="stock-badge low">⚡ Only <?php echo $p['stock']; ?> left!</span>
        <?php else: ?>
          <span class="stock-badge ok">✓ In stock (<?php echo $p['stock']; ?>)</span>
        <?php endif; ?>

        <div class="food-price">₱<?php echo number_format($p['price'],2); ?></div>

        <?php if(!$isOut): ?>
          <button class="btn btn-primary btn-w100" style="margin-top:8px;" onclick="event.stopPropagation();openOrderModal(<?php echo $p['id']; ?>,'<?php echo addslashes($p['product_name']); ?>',<?php echo $p['price']; ?>,<?php echo $p['stock']; ?>)">
            🛒 Order Now
          </button>
        <?php else: ?>
          <button class="btn btn-disabled btn-w100" style="margin-top:8px;" disabled>Unavailable</button>
        <?php endif; ?>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

  <?php
  /* Re-query for history */
  $orders = $conn->query("SELECT * FROM orders WHERE student_id=$id ORDER BY id DESC");
  $now = date("Y-m-d H:i:s");
  ?>

  <!-- ORDER HISTORY -->
  <div class="history-panel">
    <div class="history-header">
      📋 My Order History
      <span style="font-size:0.8rem;opacity:0.8;">All your orders</span>
    </div>
    <div style="overflow-x:auto;">
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
          <?php if($orders->num_rows == 0): ?>
          <tr><td colspan="5" style="text-align:center;padding:32px;color:#aaa;">No orders yet. Go grab some food! 🍱</td></tr>
          <?php else: while($o = $orders->fetch_assoc()):
            $canCancel = ($o['is_cancelled']==0 && $now <= $o['cancel_until'] && $o['status']=='Preparing');
            $sClass = ['Preparing'=>'preparing','Completed'=>'completed','Cancelled'=>'cancelled'][$o['status']] ?? 'preparing';
          ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($o['product_name']); ?></strong></td>
            <td>x<?php echo $o['quantity']; ?></td>
            <td style="color:var(--orange);font-weight:800;">₱<?php echo number_format($o['total'],2); ?></td>
            <td><span class="status-badge status-<?php echo $sClass; ?>"><?php echo $o['status']; ?></span></td>
            <td>
              <?php if($canCancel): ?>
                <a href="cancel_order.php?id=<?php echo $o['id']; ?>"
                   class="btn btn-danger btn-xs"
                   onclick="return confirm('Cancel this order? You will get a refund.')">
                  Cancel
                </a>
                <?php
                  $diff = strtotime($o['cancel_until']) - strtotime($now);
                  $mins = floor($diff/60); $secs = $diff%60;
                ?>
                <small style="display:block;color:#e74c3c;font-size:0.7rem;margin-top:2px;">
                  ⏱ <?php echo "{$mins}m {$secs}s"; ?>
                </small>
              <?php elseif($o['status']=='Preparing'): ?>
                <span style="color:#aaa;font-size:0.78rem;">Locked</span>
              <?php else: ?>
                <span style="color:#aaa;font-size:0.78rem;">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- /page-wrap -->

<!-- ORDER MODAL -->
<div class="modal-overlay" id="modal-order">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('modal-order')">✕</button>
    <div style="font-size:2rem;margin-bottom:4px;" id="modal-emoji">🍽️</div>
    <h3 id="modal-name">Product Name</h3>
    <div class="modal-price-row">
      <span style="color:var(--muted);font-size:0.88rem;">Unit Price</span>
      <span style="color:var(--orange);font-weight:900;font-size:1.1rem;" id="modal-price">₱0.00</span>
    </div>

    <div class="qty-picker">
      <button class="qty-btn" onclick="changeQty(-1)">−</button>
      <span class="qty-num" id="qty-display">1</span>
      <button class="qty-btn" onclick="changeQty(1)">+</button>
    </div>

    <div class="modal-price-row" style="margin-top:0;">
      <span style="font-weight:700;">Total</span>
      <span style="color:var(--orange);font-weight:900;font-size:1.2rem;" id="modal-total">₱0.00</span>
    </div>

    <div id="balance-warning" class="alert alert-danger" style="display:none;margin-top:12px;">
      😢 Insufficient balance! Please top up your wallet.
    </div>

    <form method="POST" action="order.php" id="order-form" style="margin-top:16px;">
      <input type="hidden" name="product_id" id="form-pid">
      <input type="hidden" name="qty" id="form-qty" value="1">
      <button class="btn btn-primary btn-w100" type="submit" id="pay-btn">
        💳 Pay Now
      </button>
    </form>
  </div>
</div>

<!-- HISTORY MODAL (kept for mobile topbar button) -->
<div class="modal-overlay" id="modal-history">
  <div class="modal-box" style="max-width:560px;width:95vw;">
    <button class="modal-close" onclick="closeModal('modal-history')">✕</button>
    <h3 style="margin-bottom:14px;">📋 Order History</h3>
    <p style="color:var(--muted);font-size:0.88rem;margin-bottom:12px;">Your full order history is shown below on this page. Scroll down to see it!</p>
    <button class="btn btn-primary btn-w100" onclick="closeModal('modal-history');document.querySelector('.history-panel').scrollIntoView({behavior:'smooth'})">
      Scroll to History ↓
    </button>
  </div>
</div>

<!-- TOAST -->
<div id="toast"></div>

<script>
const balance = <?php echo (float)$student['balance']; ?>;
let currentPrice = 0;
let currentStock = 0;
let qty = 1;

function openOrderModal(pid, name, price, stock){
  currentPrice = price;
  currentStock = stock;
  qty = 1;
  document.getElementById('modal-name').textContent = name;
  document.getElementById('modal-price').textContent = '₱' + price.toFixed(2);
  document.getElementById('form-pid').value = pid;
  document.getElementById('form-qty').value = 1;
  document.getElementById('qty-display').textContent = 1;
  updateTotal();
  openModal('modal-order');
}

function changeQty(d){
  qty = Math.max(1, Math.min(currentStock, qty + d));
  document.getElementById('qty-display').textContent = qty;
  document.getElementById('form-qty').value = qty;
  updateTotal();
}

function updateTotal(){
  const total = currentPrice * qty;
  document.getElementById('modal-total').textContent = '₱' + total.toFixed(2);
  const warn = document.getElementById('balance-warning');
  const btn = document.getElementById('pay-btn');
  if(total > balance){
    warn.style.display = 'flex';
    btn.disabled = true;
    btn.classList.add('btn-disabled');
  } else {
    warn.style.display = 'none';
    btn.disabled = false;
    btn.classList.remove('btn-disabled');
  }
}

function openModal(id){ document.getElementById(id).classList.add('active'); }
function closeModal(id){ document.getElementById(id).classList.remove('active'); }

/* Close on overlay click */
document.querySelectorAll('.modal-overlay').forEach(m=>{
  m.addEventListener('click', e=>{ if(e.target===m) m.classList.remove('active'); });
});

/* Flash auto-hide */
const flash = document.getElementById('flash-msg');
if(flash) setTimeout(()=>flash.style.display='none', 4500);

/* Toast helper */
function showToast(msg, type=''){
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'show ' + type;
  setTimeout(()=>t.className='', 3000);
}

<?php if($flash && strpos($flash,'✅')!==false): ?>
setTimeout(()=>showToast('<?php echo addslashes($flash); ?>','success'),300);
<?php elseif($flash): ?>
setTimeout(()=>showToast('<?php echo addslashes($flash); ?>','error'),300);
<?php endif; ?>
</script>
</body>
</html>