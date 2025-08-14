<?php 
require_once 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];


    if ($password === $confirm_password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, full_name, email, password, role) VALUES (?, ?, ?, ?, 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $hashedPassword]);

        echo "<div class='alert alert-success text-center'>สมัครสมาชิกสำเร็จ!</div>";
    } else {
        echo "<div class='alert alert-danger text-center'>รหัสผ่านไม่ตรงกัน</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #fe8ebaff; 
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4 text-primary">สมัครสมาชิก</h3>
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                            <input type="text" name="username" id="username" class="form-control" placeholder="ชื่อผู้ใช้" required>
                        </div>
                        <div class="mb-3">
                            <label for="fullname" class="form-label">ชื่อ-สกุล</label>
                            <input type="text" name="fullname" id="fullname" class="form-control" placeholder="ชื่อ-สกุล" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="อีเมล" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">รหัสผ่าน</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">สมัครสมาชิก</button>
                            <a href="login.php" class="btn btn-outline-secondary">เข้าสู่ระบบ</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
