<?php
session_start();
include 'db.php';

if(!isset($_SESSION['student']) || $_SESSION['student']['role'] != 'admin'){
    header("Location: index.php"); exit();
}

$msg = '';
$msgType = '';

if(isset($_POST['add'])){
    $name  = $conn->real_escape_string(trim($_POST['product_name']));
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];

    if($price <= 0 || $stock < 0 || empty($name)){
        $msg = "Please fill in all fields correctly."; $msgType = 'danger';
    } else {

        $image = '';
        if(!empty($_FILES['image']['name'])){
            if(!is_dir("images")) mkdir("images", 0755, true);

            $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed   = ['jpg','jpeg','png','gif','webp'];

            if(!in_array($ext, $allowed)){
                $msg = "Image must be JPG, PNG, GIF or WEBP."; $msgType = 'danger';
            } else {
                $image = time() . '_' . preg_replace('/[^a-z0-9_.]/','',$_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], "images/".$image);
            }
        }

        if($msgType != 'danger'){
            $status = $stock > 0 ? 'Available' : 'Sold Out';
            $conn->query("INSERT INTO products(product_name,price,image,stock,status)
                          VALUES('$name',$price,'$image',$stock,'$status')");
            $msg = "✅ '{$name}' added successfully!"; $msgType = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Food — Mura-Mura Canteen</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-brand"><span class="emoji">🍱</span> Add Food Item</div>
  <div class="topbar-right">
    <a href="admin.php" class="btn btn-sm" style="background:rgba(255,255,255,0.22);color:white;">← Back</a>
    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</div>

<div class="page-wrap" style="max-width:520px;">

  <div class="section-title">🍔 Add New Food Item</div>
  <div class="section-sub">Add a new menu item for students to order</div>

  <?php if($msg): ?>
  <div class="alert alert-<?php echo $msgType; ?>"><?php echo $msg; ?></div>
  <?php endif; ?>

  <div class="card" style="padding:28px;">
    <form method="POST" enctype="multipart/form-data">

      <div class="form-group">
        <label class="form-label">Food Name</label>
        <input class="form-control" name="product_name" placeholder="e.g. Sinigang, Adobo Rice..." required>
      </div>

      <div class="form-group">
        <label class="form-label">Price (₱)</label>
        <input class="form-control" name="price" type="number" min="1" step="0.01" placeholder="e.g. 45" required>
      </div>

      <div class="form-group">
        <label class="form-label">Stock Quantity</label>
        <input class="form-control" name="stock" type="number" min="0" placeholder="e.g. 50" required>
      </div>

      <div class="form-group">
        <label class="form-label">Food Image <small style="color:var(--muted);">(optional)</small></label>
        <input class="form-control" type="file" name="image" accept="image/*">
        <small style="color:var(--muted);font-size:0.78rem;">JPG, PNG, WEBP — a nice photo helps students choose!</small>
      </div>

      <button class="btn btn-primary btn-w100" name="add" style="margin-top:8px;">🍔 Add Food Item</button>
    </form>
  </div>

</div>
</body>
</html>
