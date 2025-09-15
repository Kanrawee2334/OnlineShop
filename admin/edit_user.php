<?php
require '../config.php';
require 'auth_admin.php';

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$user_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'member'");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<h3>ไม่พบสมาชิก</h3>";
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($username === '' || $email === '') {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    }

    if (!$error) {
        $chk = $conn->prepare("SELECT 1 FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $chk->execute([$username, $email, $user_id]);
        if ($chk->fetch()) {
            $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้วในระบบ";
        }
    }

    $updatePassword = false;
    $hashed = null;
    if (!$error && ($password !== '' || $confirm !== '')) {
        if (strlen($password) < 6) {
            $error = "รหัสผ่านต้องยาวอย่างน้อย 6 อักขระ";
        } elseif ($password !== $confirm) {
            $error = "รหัสผ่านใหม่กับยืนยันรหัสผ่านไม่ตรงกัน";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $updatePassword = true;
        }
    }

    if (!$error) {
        if ($updatePassword) {
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, password = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $hashed, $user_id];
        } else {
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $user_id];
        }
        $upd = $conn->prepare($sql);
        $upd->execute($args);
        header("Location: users.php");
        exit;
    }

    $user['username'] = $username;
    $user['full_name'] = $full_name;
    $user['email'] = $email;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แก้ไขสมาชิก</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #ffe6f2, #f2e6ff, #e6faff, #fffbe6);
    min-height: 100vh;
    font-family: 'Kanit', sans-serif;
    padding-top: 50px;
}
.card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    padding: 30px;
}
h2 { 
    color: #d63384; 
    text-align: center; 
    margin-bottom: 25px; 
}
.btn-primary { 
    background-color: #ba68c8; 
    border: none; 
    font-weight: bold;
}
.btn-primary:hover { 
    background-color: #9c27b0; 
}
.btn-secondary { 
    background-color: #81d4fa; 
    border: none; 
    color: #333;
}
.btn-secondary:hover {
    background-color: #4fc3f7;
}
.form-control {
    border-radius: 12px;
    border: 2px solid #ba68c8;
    transition: 0.3s;
}
.form-control:focus { 
    border-color: #81d4fa; 
    box-shadow: 0 0 8px rgba(129,212,250,0.5);
}
.alert-danger { 
    background: #ffd6e8; 
    color: #9b1c6d; 
    border-radius: 12px; 
    border: 1px solid #ffb3d9;
}
label { font-weight: 600; color: #6c3483; }
</style>
</head>
<body>
<div class="container d-flex justify-content-center">
    <div class="card col-md-8 col-lg-6">
        <h2>แก้ไขข้อมูลสมาชิก</h2>
        <?php if (isset($error) && $error !== null): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" class="row g-3">
            <div class="col-12">
                <label class="form-label">ชื่อผู้ใช้</label>
                <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($user['username']) ?>">
            </div>
            <div class="col-12">
                <label class="form-label">ชื่อ - นามสกุล</label>
                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>">
            </div>
            <div class="col-12">
                <label class="form-label">อีเมล</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">รหัสผ่านใหม่ <small class="text-muted">(เว้นว่างถ้าไม่ต้องการเปลี่ยน)</small></label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                <input type="password" name="confirm_password" class="form-control">
            </div>
            <div class="col-12 d-flex justify-content-between mt-3">
                <a href="users.php" class="btn btn-secondary">← กลับ</a>
                <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
