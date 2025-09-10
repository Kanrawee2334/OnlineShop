<?php 
session_start(); 
require_once '../config.php';  

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../login.php"); 
    exit; 
} 
?> 

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แผงควบคุมผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <!-- Header -->
    <div class="text-center mb-5">
        <h2 class="fw-bold">ระบบผู้ดูแลระบบ</h2>
        <p class="text-muted">ยินดีต้อนรับ, 
            <span class="fw-semibold text-primary">
                <?= htmlspecialchars($_SESSION['username']) ?>
            </span>
        </p>
    </div>

    <!-- Dashboard Menu -->
    <div class="row g-4">
        <div class="col-md-3">
            <a href="users.php" class="text-decoration-none">
                <div class="card shadow-sm border-warning">
                    <div class="card-body text-center">
                        <h5 class="card-title text-warning">👥 จัดการสมาชิก</h5>
                        <p class="card-text small text-muted">เพิ่ม ลบ แก้ไข ผู้ใช้งาน</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="orders.php" class="text-decoration-none">
                <div class="card shadow-sm border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary">📦 จัดการคำสั่งซื้อ</h5>
                        <p class="card-text small text-muted">ตรวจสอบและอัปเดตคำสั่งซื้อ</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="categories.php" class="text-decoration-none">
                <div class="card shadow-sm border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success">🗂️ จัดการหมวดหมู่</h5>
                        <p class="card-text small text-muted">จัดการประเภทสินค้า</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="products.php" class="text-decoration-none">
                <div class="card shadow-sm border-dark">
                    <div class="card-body text-center">
                        <h5 class="card-title text-dark">🛒 จัดการสินค้า</h5>
                        <p class="card-text small text-muted">เพิ่ม แก้ไข ลบ สินค้า</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Logout Button -->
    <div class="text-center mt-5">
        <a href="../logout.php" class="btn btn-outline-secondary px-4">ออกจากระบบ</a>
    </div>
</div>

</body>
</html>
