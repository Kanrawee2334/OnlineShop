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

$isLoggedIn = isset($_SESSION['user_id']); 
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #ffe6f2, #f2e6ff, #e6faff, #fffbe6);
            display: flex;
            align-items: center;
            font-family: 'Kanit', sans-serif;
        }
        .card {
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        h3.card-title {
            color: #d63384; /* ชมพูสดใส */
        }
        h6.text-muted {
            color: #9966cc !important; /* ม่วงอ่อน */
        }
        .price-tag {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ff66b2; /* ชมพูเข้ม */
        }
        .stock-tag {
            color: #33cc99; /* เขียวพาสเทล */
            font-weight: 600;
        }
        .btn-success {
            background: linear-gradient(45deg, #ff66b2, #cc66ff, #66ccff, #ffdb66);
            border: none;
            border-radius: 30px;
            font-weight: 600;
            transition: transform .2s ease, opacity .2s ease;
        }
        .btn-success:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
        .btn-outline-secondary {
            border-radius: 30px;
            border-color: #ff99cc;
            color: #cc66cc;
            font-weight: 600;
        }
        .btn-outline-secondary:hover {
            background: #ffe6f2;
            border-color: #ff66b2;
            color: #b30086;
        }
        .form-control {
            border-radius: 12px;
            border: 1.5px solid #f5c2e7;
        }
        .form-control:focus {
            border-color: #d63384;
            box-shadow: 0 0 6px rgba(214, 51, 132, 0.4);
        }
        .alert-info {
            border-radius: 15px;
            background: #e6f0ff;
            border: none;
            color: #004085;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <!-- ปุ่มย้อนกลับ -->
    <a href="index.php" class="btn btn-outline-secondary mb-4">← กลับหน้ารายการสินค้า</a>

    <!-- การ์ดรายละเอียดสินค้า -->
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body p-4">
            <h3 class="card-title fw-bold mb-3"><?= htmlspecialchars($product['product_name']) ?></h3>
            <h6 class="mb-3">หมวดหมู่: <?= htmlspecialchars($product['category_name']) ?></h6>
            
            <p class="price-tag mb-2">
                ราคา: <?= number_format($product['price'], 2) ?> บาท
            </p>
            <p class="stock-tag mb-4">
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
