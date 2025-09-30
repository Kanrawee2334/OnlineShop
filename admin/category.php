<?php
require '../config.php';
require 'auth_admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// เพิ่มหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if ($category_name) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->execute([$category_name]);
        $_SESSION['success'] = "เพิ่มหมวดหมู่เรียบร้อยแล้ว";
        header("Location: category.php");
        exit;
    }
}

// ลบหมวดหมู่
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $productCount = $stmt->fetchColumn();

    if ($productCount > 0) {
        $_SESSION['error'] = "ไม่สามารถลบได้ เนื่องจากยังมีสินค้าใช้งานหมวดหมู่นี้อยู่";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $_SESSION['success'] = "ลบหมวดหมู่เรียบร้อยแล้ว";
    }
    header("Location: category.php");
    exit;
}

// แก้ไขหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = trim($_POST['new_name']);
    if ($category_name) {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        $stmt->execute([$category_name, $category_id]);
        $_SESSION['success'] = "แก้ไขหมวดหมู่เรียบร้อยแล้ว";
        header("Location: category.php");
        exit;
    } else {
        $_SESSION['error'] = "กรุณากรอกชื่อใหม่";
        header("Location: category.php");
        exit;
    }
}

// ดึงหมวดหมู่ทั้งหมด
$categories = [];
try {
    $categories = $conn->query("SELECT * FROM categories ORDER BY category_id ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการหมวดหมู่</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: #fff0f6;
    min-height: 100vh;
    padding: 30px;
    font-family: 'Kanit', sans-serif;
}
h2, h5 { 
    color: #d63384;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}
.btn-primary { background-color: #ba68c8; border: none; font-weight: bold; }
.btn-primary:hover { background-color: #9c27b0; }
.btn-secondary { background-color: #81d4fa; border: none; color: #333; }
.btn-secondary:hover { background-color: #4fc3f7; }
.btn-warning { background-color: #ffeb3b; border: none; color: #333; }
.btn-warning:hover { background-color: #fdd835; }
.btn-danger { background-color: #ff66b2; border: none; color: #fff; }
.btn-danger:hover { background-color: #e0559d; }
.form-control { border-radius: 12px; border: 2px solid #ba68c8; transition: 0.3s; }
.form-control:focus { border-color: #81d4fa; box-shadow: 0 0 8px rgba(129,212,250,0.5); }
.form-control-sm { padding: .25rem .5rem; font-size: .875rem; }
table.table { background: #fdfdfd; width: 100%; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
table th { background: #f3e6f7; color: #6c3483; }
table td { vertical-align: middle; }
.alert-danger { background: #ffd6e8; color: #9b1c6d; border-radius: 12px; border: 1px solid #ffb3d9; }
.alert-success { background: #d4edda; color: #155724; border-radius: 12px; border: 1px solid #b3ffcc; }
td form { display: flex; gap: 5px; justify-content: center; align-items: center; margin-bottom: 0; }
</style>
</head>
<body class="container mt-4">

<h2>จัดการหมวดหมู่สินค้า</h2>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>

<!-- ฟอร์มเพิ่มหมวดหมู่ -->
<form method="post" class="row g-3 mb-4">
    <div class="col-md-6">
        <input type="text" name="category_name" class="form-control" placeholder="ชื่อหมวดหมู่ใหม่" required>
    </div>
    <div class="col-md-2">
        <button type="submit" name="add_category" class="btn btn-primary">เพิ่มหมวดหมู่</button>
    </div>
</form>

<h5>รายการหมวดหมู่</h5>
<table class="table table-bordered text-center">
    <thead>
        <tr>
            <th>ชื่อหมวดหมู่</th>
            <th>แก้ไขชื่อ</th>
            <th>จัดการ</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['category_name']) ?></td>
                <td>
                    <form method="post" class="d-flex gap-2 justify-content-center align-items-center mb-0">
                        <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                        <input type="text" class="form-control form-control-sm" name="new_name" value="<?= htmlspecialchars($cat['category_name']) ?>" required>
                        <button type="submit" name="update_category" class="btn btn-sm btn-warning">แก้ไข</button>
                    </form>
                </td>
                <td>
                    <a href="category.php?delete=<?= $cat['category_id'] ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('คุณต้องการลบหมวดหมู่นี้หรือไม่?')">ลบ</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="3" class="text-center">ยังไม่มีหมวดหมู่</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

</body>
</html>
