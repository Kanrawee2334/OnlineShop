<?php 
session_start(); // เริ่มต้น session เพื่อจัดการการเข้าสู่ระบบ

// ตรวจสอบว่ามีข้อมูล session หรือไม่
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
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
    <style>
        body {
            background-color: pink;
            font-family: 'Prompt', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .welcome-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="welcome-card">
    <h1 class="mb-3">ยินดีต้อนรับ 🎉</h1>
    <p class="fs-5"><strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
    <p class="text-muted">สิทธิ์การใช้งาน: <?= htmlspecialchars($_SESSION['role']) ?></p>
    <a href="logout.php" class="btn btn-danger mt-3">ออกจากระบบ</a>
</div>

</body>
</html>
