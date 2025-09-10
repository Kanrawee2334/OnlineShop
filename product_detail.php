<?php
require_once 'config.php'; // เชื่อมฐานข้อมูล
session_start();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}
$product_id = $_GET['id'];

$stmt = $conn->prepare("SELECT p.*, c.category_name 
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.category_id
                        WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบว่ามีข้อมูล session หรือไม่
$isLoggedIn = isset($_SESSION['user_id']); 
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- ปุ่มย้อนกลับ -->
    <a href="index.php" class="btn btn-outline-secondary mb-4">← กลับหน้ารายการสินค้า</a>

    <!-- การ์ดรายละเอียดสินค้า -->
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body p-4">
            <h3 class="card-title fw-bold mb-3"><?= htmlspecialchars($product['product_name']) ?></h3>
            <h6 class="text-muted mb-3">หมวดหมู่: <?= htmlspecialchars($product['category_name']) ?></h6>
            
            <p class="fs-5 text-danger fw-semibold mb-2">
                ราคา: <?= number_format($product['price'], 2) ?> บาท
            </p>
            <p class="text-success mb-4">
                คงเหลือ: <?= htmlspecialchars($product['stock']) ?> ชิ้น
            </p>

            <!-- เช็คการล็อกอิน -->
            <?php if ($isLoggedIn): ?>
                <form action="cart.php" method="post" class="d-flex align-items-end gap-3">
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

                    <div>
                        <label for="quantity" class="form-label">จำนวน:</label>
                        <input type="number" name="quantity" id="quantity" 
                               class="form-control" value="1" min="1" max="<?= $product['stock'] ?>" required>
                    </div>

                    <button type="submit" class="btn btn-success">
                        🛒 เพิ่มในตะกร้า
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-info mt-4">
                    กรุณา <a href="login.php" class="alert-link">เข้าสู่ระบบ</a> เพื่อสั่งซื้อสินค้า
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
