<?php 
session_start();
require_once 'config.php';

$isLoggedIn = isset($_SESSION['user_id']);

// ดึงข้อมูลสินค้า
$stmt = $conn->query("SELECT p.*, c.category_name FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>หน้าหลัก</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #ffe6f2, #f2e6ff, #e6faff, #fffbe6);
      font-family: 'Kanit', sans-serif;
      min-height: 100vh;
    }
    h1 {
      color: #d63384; /* ชมพูสด */
      font-weight: 700;
    }
    .card {
      border-radius: 16px;
      border: 2px solid #f8bbd0; /* ชมพูพาสเทล */
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .card-title {
      color: #9c27b0; /* ม่วง */
    }
    .card-subtitle {
      color: #03a9f4; /* ฟ้า */
    }
    .btn-success {
      background-color: #ff66b2; /* ชมพูสด */
      border: none;
    }
    .btn-success:hover {
      background-color: #e0559d;
    }
    .btn-outline-primary {
      border-color: #ba68c8; /* ม่วงอ่อน */
      color: #9c27b0;
    }
    .btn-outline-primary:hover {
      background-color: #ba68c8;
      color: #fff;
    }
    .btn-warning {
      background-color: #ffeb3b; /* เหลืองสด */
      border: none;
      color: #333;
    }
    .btn-warning:hover {
      background-color: #fdd835;
    }
    .btn-info {
      background-color: #4dd0e1; /* ฟ้าอมเขียว */
      border: none;
      color: #fff;
    }
    .btn-info:hover {
      background-color: #26c6da;
    }
    .btn-secondary {
      background-color: #bdbdbd;
      border: none;
    }
    .welcome {
      font-weight: 600;
      color: #6f42c1; /* ม่วงเข้ม */
    }
  </style>
</head>
<body class="container mt-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>🌸 รายการสินค้า 🌈</h1>
    <div>
      <?php if ($isLoggedIn): ?>
        <span class="me-3 welcome">
          ยินดีต้อนรับ <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] ?>)
        </span>
        <a href="profile.php" class="btn btn-info">ข้อมูลส่วนตัว</a>
        <a href="cart.php" class="btn btn-warning">ดูตะกร้า</a>
        <a href="logout.php" class="btn btn-secondary">ออกจากระบบ</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-success">เข้าสู่ระบบ</a>
        <a href="register.php" class="btn btn-outline-primary">สมัครสมาชิก</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- รายการสินค้า -->
  <div class="row">
    <?php foreach ($products as $product): ?>
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h5>
            <h6 class="card-subtitle mb-2"><?= htmlspecialchars($product['category_name']) ?></h6>
            <p class="card-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <p><strong>ราคา:</strong> <?= number_format($product['price'], 2) ?> บาท</p>

            <?php if ($isLoggedIn): ?>
              <form action="cart.php" method="post" class="d-inline">
                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-sm btn-success">เพิ่มในตะกร้า</button>
              </form>
            <?php else: ?>
              <small class="text-muted">เข้าสู่ระบบเพื่อสั่งซื้อ </small>
            <?php endif; ?>

            <a href="product_detail.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-outline-primary float-end">
              ดูรายละเอียด
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</body>
</html>
