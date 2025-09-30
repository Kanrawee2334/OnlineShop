<?php
require_once '../config.php';

// --- ดึงข้อมูลหมวดหมู่ ---
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// --- ดึงข้อมูลสินค้าเดิม ---
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("❌ ไม่พบสินค้านี้");
    }
}

// --- อัปเดตข้อมูลเมื่อกดบันทึก ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'] ?? $product['product_name'];
    $price        = $_POST['price'] ?? $product['price'];
    $stock        = $_POST['stock'] ?? $product['stock'];
    $category_id  = $_POST['category_id'] ?? $product['category_id'];
    $description  = $_POST['description'] ?? $product['description'];
    $image        = $product['image'];

    // ลบรูปเดิม
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == "1") {
        $image = null;
    }

    // อัปโหลดรูปใหม่
    if (!empty($_FILES['product_image']['name'])) {
        $targetDir = "../product_images/";
        $fileName = time() . "_" . basename($_FILES["product_image"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $targetFilePath)) {
            $image = $fileName;
        }
    }

    // อัปเดตข้อมูล
    $stmt = $conn->prepare("UPDATE products 
                            SET product_name=?, price=?, stock=?, category_id=?, description=?, image=? 
                            WHERE product_id=?");
    $stmt->execute([$product_name, $price, $stock, $category_id, $description, $image, $id]);

    // redirect ทันที
    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แก้ไขสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #ffe6f0, #e6f0ff, #fff9e6);
    min-height: 100vh;
    font-family: "Prompt", sans-serif;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 40px;
}
.card-form {
    background: #fff0f6;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    border-radius: 16px;
    padding: 30px;
    width: 100%;
    max-width: 800px;
    animation: fadeIn 0.6s ease;
}
@keyframes fadeIn {
    from {opacity: 0; transform: translateY(20px);}
    to {opacity: 1; transform: translateY(0);}
}
h2 {
    color: #d63384;
    font-weight: bold;
    text-align: center;
    margin-bottom: 20px;
}
.btn-primary {
    background: linear-gradient(90deg,#ff66b2,#ff3399);
    border: none;
    font-weight: 600;
}
.btn-primary:hover {
    background: linear-gradient(90deg,#e60073,#cc0052);
}
.btn-secondary {
    background: linear-gradient(90deg,#6c63ff,#4b42cc);
    border: none;
    font-weight: 600;
}
.btn-secondary:hover {
    background: linear-gradient(90deg,#4b42cc,#2c2399);
}
label {
    font-weight: 600;
    color: #9c27b0;
}
.form-control, .form-select, textarea {
    border: 2px solid #ffcce6;
    border-radius: 10px;
}
.form-control:focus, .form-select:focus, textarea:focus {
    border-color: #ff66b2;
    box-shadow: 0 0 8px rgba(255, 102, 178, 0.5);
}
img.rounded {
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
</style>
</head>
<body>
<div class="card-form">
    <h2>🌸 แก้ไขสินค้า</h2>
    <a href="products.php" class="btn btn-secondary mb-3">← กลับไปยังรายการสินค้า</a>

    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">ชื่อสินค้า</label>
            <input type="text" name="product_name" class="form-control" 
                   value="<?= htmlspecialchars($product['product_name'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">ราคา</label>
            <input type="number" step="0.01" name="price" class="form-control" 
                   value="<?= $product['price'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">จำนวนในคลัง</label>
            <input type="number" name="stock" class="form-control" 
                   value="<?= $product['stock'] ?? '' ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">หมวดหมู่</label>
            <select name="category_id" class="form-select">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" 
                        <?= ($product['category_id'] ?? '') == $cat['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">รายละเอียดสินค้า</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label d-block">รูปปัจจุบัน</label>
            <?php if (!empty($product['image'])): ?>
                <img src="../product_images/<?= htmlspecialchars($product['image']) ?>" width="150" height="150" class="rounded mb-2 border">
            <?php else: ?>
                <span class="text-muted d-block mb-2">ไม่มีรูป</span>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label">อัปโหลดรูปใหม่ (jpg, png)</label>
            <input type="file" name="product_image" class="form-control">
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                <label class="form-check-label" for="remove_image">ลบรูปเดิม</label>
            </div>
        </div>

        <div class="col-12 text-center mt-3">
            <button type="submit" class="btn btn-primary px-4 py-2">💾 บันทึกการแก้ไข</button>
        </div>
    </form>
</div>
</body>
</html>
