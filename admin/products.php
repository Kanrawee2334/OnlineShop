<?php
require '../config.php'; // เชื่อมต่อฐานข้อมูลด้วย PDO
require 'auth_admin.php'; // ตรวจสอบสิทธิ์ Admin

/// เพิ่มสินค้าใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name        = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price       = floatval($_POST['price']);
    $stock       = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);

    if (!empty($name) && $price > 0) {

        $imageName = "no-image.jpg"; // ค่าเริ่มต้น

        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            $allowedExt = ['jpg', 'jpeg', 'png']; // นามสกุลที่อนุญาต
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowedExt)) {
                $imageName = 'product_' . time() . '.' . $ext;
                $path = realpath(__DIR__ . '/../product_images') . '/' . $imageName;
                if (move_uploaded_file($file['tmp_name'], $path)) {
                    // success
                } else {
                    $imageName = "no-image.jpg"; // ถ้าย้ายไฟล์ไม่สำเร็จ
                }
            }
        }

        $stmt = $conn->prepare("INSERT INTO products 
            (product_name, description, price, stock, category_id, image, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $description, $price, $stock, $category_id, $imageName]);

        header("Location: products.php");
        exit;
    }
}

// // ลบสินค้า
// if (isset($_GET['delete'])) {
//     $product_id = intval($_GET['delete']);
//     $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
//     $stmt->execute([$product_id]);
//     header("Location: products.php");
//     exit;
// }

    // ลบสนิ คำ้ (ลบไฟลร์ปู ดว้ย)
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete']; // แคสต์เป็น int
// 1) ดงึชอื่ ไฟลร์ปู จำก DB ก่อน
    $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $imageName = $stmt->fetchColumn(); // null ถ ้ำไม่มีรูป
// 2) ลบใน DB ด ้วย Transaction
    try {
        $conn->beginTransaction();
        $del = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $del->execute([$product_id]);
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
    // ใส่ flash message หรือ log ได ้ตำมต ้องกำร
        header("Location: products.php");
        exit;
      }
// 3) ลบไฟล์รูปหลัง DB ลบส ำเร็จ
if ($imageName) {
    $baseDir = realpath(__DIR__ . '/../product_images'); // โฟลเดอร์เก็บรูป
    $filePath = realpath($baseDir . '/' . $imageName);
// กัน path traversal: ต ้องอยู่ใต้ $baseDir จริง ๆ
    if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
        @unlink($filePath); // ใช ้@ กัน warning ถำ้ลบไมส่ ำเร็จ
    }
}
    header("Location: products.php");
    exit;
}



// ดึงรายการสินค้า
$stmt = $conn->query("SELECT p.*, c.category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.category_id 
                      ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงหมวดหมู่
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">
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
        .btn-primary {
            background-color: #ff66b2;
            border: none;
        }
        .btn-primary:hover {
            background-color: #e60073;
        }
        .btn-warning {
            background-color: #ffcc00;
            border: none;
        }
        .btn-warning:hover {
            background-color: #e6b800;
        }
        .btn-danger {
            background-color: #ff4d94;
            border: none;
        }
        .btn-danger:hover {
            background-color: #cc0066;
        }
        .btn-secondary {
            background-color: #6c63ff;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #4b42cc;
        }
        table thead {
            background-color: #ffd6eb;
            color: #4a004d;
        }
        table tbody tr:hover {
            background-color: #fff0f6;
        }
        .form-control, .form-select, textarea {
            border: 2px solid #ffcce6;
        }
        .form-control:focus, .form-select:focus, textarea:focus {
            border-color: #ff66b2;
            box-shadow: 0 0 5px rgba(255, 102, 178, 0.5);
        }
    </style>
</head>
<body class="container mt-4">
    <h2 class="mb-4 ">🌸 จัดการสินค้า</h2>
    <a href="index.php" class="btn btn-secondary mb-4">← กลับหน้าผู้ดูแล</a>

    <!-- ฟอร์มเพิ่มสินค้าใหม่ -->
    <form method="post" enctype="multipart/form-data" class="row g-3 mb-4 p-3  mt-10 rounded" style="background:#fff0f6; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
        <h5>➕ เพิ่มสินค้าใหม่</h5>
        <div class="col-md-4">
            <input type="text" name="product_name" class="form-control" placeholder="ชื่อสินค้า" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="price" class="form-control" placeholder="ราคา" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="stock" class="form-control" placeholder="จำนวน" required>
        </div>
        <div class="col-md-2">
            <select name="category_id" class="form-select" required>
                <option value="">เลือกหมวดหมู่</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>">
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <textarea name="description" class="form-control" placeholder="รายละเอียดสินค้า" rows="2"></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">รูปสินค้า(jpg, png)</label>
            <input type="file" name="product_image" class="form-control">
        </div>

        <div class="col-12">
            <button type="submit" name="add_product" class="btn btn-primary">เพิ่มสินค้า</button>
        </div>
    </form>

    
        <!-- แสดงรายการสินค้า -->
    <h5>📋 รายการสินค้า</h5>
    <table class="table table-bordered rounded shadow-sm bg-white align-middle">
        <thead>
            <tr>
                <th style="width:120px; text-align:center;">รูป</th>
                <th>ชื่อสินค้า</th>
                <th>หมวดหมู่</th>
                <th>ราคา</th>
                <th>คงเหลือ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
                <?php
                    $uploadDir = __DIR__ . '/../product_images/';
                    $uploadUrl = '../product_images/';
                    if (!empty($p['image']) && file_exists($uploadDir . $p['image'])) {
                        $img = $uploadUrl . rawurlencode($p['image']);
                    } else {
                        $img = $uploadUrl . 'no-image.jpg';
                    }
                ?>
                <tr>
                    <td style="text-align:center;">
                        <img src="<?= htmlspecialchars($img) ?>" 
                             alt="<?= htmlspecialchars($p['product_name']) ?>" 
                             class="img-thumbnail" style="width:100px; height:100px; object-fit:cover;">
                    </td>
                    <td><?= htmlspecialchars($p['product_name']) ?></td>
                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                    <td><?= number_format($p['price'], 2) ?> บาท</td>
                    <td><?= $p['stock'] ?></td>
                    <td>
                        <a href="products.php?delete=<?= $p['product_id'] ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('ยืนยันการลบสินค้านี้?')">ลบ</a>
                        <a href="edit_products.php?id=<?= $p['product_id'] ?>" 
                           class="btn btn-sm btn-warning">แก้ไข</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
