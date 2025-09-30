<?php 
session_start(); 
require 'config.php'; 

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือยัง
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];  
$errors = []; 

// ดึงรายการสินค้าในตะกร้า
$stmt = $conn->prepare("SELECT cart.cart_id, cart.user_id, cart.product_id, cart.quantity, products.product_name, products.price 
                        FROM cart  
                        JOIN products ON cart.product_id = products.product_id 
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]); 
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// รวมราคารวมทั้งหมด
$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['price'];
}

// เมื่อผู้ใช้กดยืนยันคำสั่งซื้อ (method POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address     = trim($_POST['address']);      
    $city        = trim($_POST['city']);         
    $postal_code = trim($_POST['postal_code']);  
    $phone       = trim($_POST['phone']);        

    // ตรวจสอบการกรอกข้อมูล
    if (empty($address) || empty($city) || empty($postal_code) || empty($phone)) {
        $errors[] = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }

    if (empty($errors)) {
        $conn->beginTransaction();
        try {
            // บันทึกข้อมูลการสั่งซื้อ
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$user_id, $total]);
            $order_id = $conn->lastInsertId();

            // บันทึกรายการสินค้าใน order_items
            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmtItem->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            }

            // บันทึกข้อมูลการจัดส่ง
            $stmt = $conn->prepare("INSERT INTO shipping (order_id, address, city, postal_code, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$order_id, $address, $city, $postal_code, $phone]);

            // ล้างตะกร้าสินค้า
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);

            $conn->commit();
            header("Location: orders.php?success=1");
            exit;

        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html> 
<html lang="th"> 
<head> 
    <meta charset="UTF-8"> 
    <title>สั่งซื้อสินค้า</title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #ffe6f0, #e6f0ff, #fff9e6);
            min-height: 100vh;
            font-family: "Prompt", sans-serif;
        }
        h2, h5 {
            color: #d63384;
            font-weight: bold;
        }
        .list-group-item {
            border: 1px solid #ffcce6;
            background-color: #fff0f6;
        }
        .list-group-item.text-end {
            background-color: #fffbe6;
        }
        .form-control, .form-select, textarea {
            border: 2px solid #ffcce6;
        }
        .form-control:focus, .form-select:focus, textarea:focus {
            border-color: #ff66b2;
            box-shadow: 0 0 5px rgba(255,102,178,0.5);
        }
        .btn-success {
            background-color: #94df5bff;
            border: none;
        }
        .btn-success:hover {
            background-color: #3cd74cff;
        }
        .btn-secondary {
            background-color: #ba68c8;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #9c27b0;
        }
    </style>
</head> 
<body class="container mt-4"> 

<h2>ยืนยันการสั่งซื้อ</h2> 

<?php if (!empty($errors)): ?> 
    <div class="alert alert-danger"> 
        <ul> 
            <?php foreach ($errors as $e): ?> 
                <li><?= htmlspecialchars($e) ?></li> 
            <?php endforeach; ?> 
        </ul> 
    </div> 
<?php endif; ?> 

<!-- แสดงรายการสินค้าในตะกร้า --> 
<h5>รายการสินค้าในตะกร้า</h5> 
<ul class="list-group mb-4"> 
    <?php foreach ($items as $item): ?> 
        <li class="list-group-item"> 
            <?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?> = <?= number_format($item['quantity'] * $item['price'], 2) ?> บาท
        </li> 
    <?php endforeach; ?> 
    <li class="list-group-item text-end"><strong>รวมทั้งหมด: <?= number_format($total, 2) ?> บาท</strong></li> 
</ul> 

<!-- ฟอร์มกรอกข้อมูลการจัดส่ง --> 
<form method="post" class="row g-3"> 
    <div class="col-md-6"> 
        <label for="address" class="form-label">ที่อยู่</label> 
        <input type="text" name="address" id="address" class="form-control" required> 
    </div> 
    <div class="col-md-4"> 
        <label for="city" class="form-label">จังหวัด</label> 
        <input type="text" name="city" id="city" class="form-control" required> 
    </div> 
    <div class="col-md-2"> 
        <label for="postal_code" class="form-label">รหัสไปรษณีย์</label> 
        <input type="text" name="postal_code" id="postal_code" class="form-control" required> 
    </div> 
    <div class="col-md-6"> 
        <label for="phone" class="form-label">เบอร์โทรศัพท์</label> 
        <input type="text" name="phone" id="phone" class="form-control" required> 
    </div> 

   <!-- ปุ่มยืนยันและกลับตะกร้า --> 
<div class="col-12 d-flex gap-4 mt-4 flex-column flex-sm-row">
    <button type="submit" class="btn btn-success w-100 py-2">
        <i class="fas fa-check-circle"></i> ยืนยันการสั่งซื้อ
    </button>
    <a href="cart.php" class="btn btn-secondary w-100 py-2 mt-4 mt-sm-0">
        <i class="fas fa-arrow-left"></i> กลับตะกร้าสินค้า
    </a>
</div>

</form> 

</body> 
</html>
