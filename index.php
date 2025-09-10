<?php 
session_start(); // เริ่มต้น session เพื่อจัดการการเข้าสู่ระบบ

require_once 'config.php'; //เชื่อมฐานข้อมูล

$isLoggedIn = isset($_SESSION['user_id']); //เช็คล็อคอิน

//ดึงข้อมูล
$stmt = $conn->query("SELECT p.*, c.category_name FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ตรวจสอบว่ามีข้อมูล session หรือไม่
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    // header("Location: login.php");
    // exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- <div style="background:white; padding:40px; border-radius:20px; box-shadow:0 8px 20px rgba(0,0,0,0.2); text-align:center; max-width:400px; width:100%;"> -->
            <h1 style="color:#333; margin-bottom:20px;">รายการสินค้า</h1>
            <div>
                <?php
                    if ($isLoggedIn):  ?>
                    <span class="me-3">ยินดีต้อนรับ<?= htmlspecialchars($_SESSION['username']) ?> (<?=
                    $_SESSION['role'] ?>)</span>
                        <a href="profile.php" class="btn btn-info">ข้อมูลส่วนตัว</a>
                        <a href="cart.php" class="btn btn-warning">ดูตะกร้า</a>
                        <a href="logout.php" class="btn btn-secondary">ออกจาดระบบ</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-success">เข้าสู่ระบบ</a>
                        <a href="register.php" class="btn btn-primary">สมัครสมาชิก</a>
                <?php endif?>
            </div>
        </div>
    </div>


        <!-- รายการสินค้าที่ต้องแสดง-->
        <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($product['category_name']) ?></h6>
                            <p class="card-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                            <p><strong>ราคา:</strong> <?= number_format($product['price'], 2) ?> บาท</p>
                            <?php if ($isLoggedIn): ?>
                            <form action="cart.php" method="post" class="d-inline">
                                <input type="hidden" name="product_id" value="<?= $product['product_name'] ?>">
                                <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-sm btn-success">เพิ่มในตะกร้า</button>
                            </form>
                            <?php else: ?>
                                <small class="text-muted">เข้าสู่ระบบเพื่อสั่งซื้อ </small>
                                <?php endif; ?>
                            <a href="product_detail.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-outline-primary float-end">ดูรายละเอียด</a>
                    </div>
                </div>
            </div>
                    <?php endforeach; ?>
        </div>

</body>
</html>
