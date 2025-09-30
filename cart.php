<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ดึงรายการสินค้าในตะกร้า
$stmt = $conn->prepare("SELECT cart.cart_id, cart.quantity, products.product_name, products.price
                        FROM cart
                        JOIN products ON cart.product_id = products.product_id
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// เพิ่มสินค้าเข้าตะกร้า
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        $stmt->execute([$quantity, $item['cart_id']]);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    header("Location: cart.php");
    exit;
}

// คำนวณยอดรวม
$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['price'];
}

// ลบสินค้าออกจากตะกร้า
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ตะกร้าสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #ffe6f0, #e6f0ff, #fff9e6);
        font-family: "Prompt", sans-serif;
        min-height: 100vh;
    }
    h2 {
        color: #d63384;
        font-weight: bold;
    }
    .cart-table th {
        background: linear-gradient(90deg, #ffd6eb, #e1bee7, #b3e5fc, #fff9c4); /* ไล่ชมพู-ม่วง-ฟ้า-เหลืองอ่อน */
        color: #4a004d;
        font-weight: bold;
        text-align: center;
    }
    .cart-table tbody tr:hover {
        background-color: #ffe6f0 !important;
        transition: background-color 0.3s;
    }
    .btn-primary {
        background-color: #ff66b2;
        border: none;
        color: #fff;
    }
    .btn-primary:hover {
        background-color: #e60073;
    }
    .btn-secondary {
        background-color: #6c63ff;
        border: none;
        color: #fff;
    }
    .btn-secondary:hover {
        background-color: #4b42cc;
    }
    .btn-success {
        background: linear-gradient(90deg, #81d4fa, #42a5f5);
        border: none;
        color: #fff;
    }
    .btn-success:hover {
        background: linear-gradient(90deg, #42a5f5, #1e88e5);
    }
    .btn-danger {
        background-color: #ff4d94;
        border: none;
        color: #fff;
    }
    .btn-danger:hover {
        background-color: #cc0066;
    }
    .alert-warning {
        background-color: #fff0f6;
        color: #d63384;
        border: 1px solid #ffb6d6;
    }
</style>
</head>
<body class="container mt-4">

<h2>🛒 ตะกร้าสินค้า</h2>
<a href="index.php" class="btn btn-secondary mb-3">← กลับไปเลือกสินค้า</a>

<?php if (count($items) === 0): ?>
<div class="alert alert-warning">ตะกร้าของคุณยังว่างอยู่</div>
<?php else: ?>
<table class="table table-bordered text-center cart-table">
  <thead>
    <tr>
      <th>สินค้า</th>
      <th>จำนวน</th>
      <th>ราคาต่อหน่วย</th>
      <th>ราคารวม</th>
      <th>จัดการ</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $colors = ['#ffe6f0', '#e6e6ff', '#e6f7ff', '#fffbe6']; // ไล่สีชมพู-ม่วง-ฟ้า-เหลือง
    foreach ($items as $index => $item): 
        $rowColor = $colors[$index % count($colors)];
    ?>
    <tr style="background-color: <?= $rowColor ?>;">
      <td>🛍️ <?= htmlspecialchars($item['product_name']) ?></td>
      <td><?= $item['quantity'] ?></td>
      <td><?= number_format($item['price'], 2) ?> บาท</td>
      <td><?= number_format($item['price'] * $item['quantity'], 2) ?> บาท</td>
      <td>
        <a href="cart.php?remove=<?= $item['cart_id'] ?>" 
           class="btn btn-sm btn-danger"
           onclick="return confirm('คุณต้องการลบสินค้านี้ออกจากตะกร้าหรือไม่?')">
           🗑️ ลบ
        </a>
      </td>
    </tr>
    <?php endforeach; ?>
    <tr style="background-color: #ffd6eb; font-weight: bold;">
      <td colspan="3" class="text-end">รวมทั้งหมด:</td>
      <td colspan="2"><?= number_format($total, 2) ?> บาท</td>
    </tr>
  </tbody>
</table>

<a href="checkout.php" class="btn btn-success btn-lg">✅ สั่งซื้อสินค้า</a>
<?php endif; ?>

</body>
</html>
