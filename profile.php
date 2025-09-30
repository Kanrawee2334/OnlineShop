<?php 
session_start(); 
require 'config.php'; 

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit; 
} 

$user_id = $_SESSION['user_id']; 
$errors = []; 
$success = ""; 

// ดึงข้อมูลสมาชิก
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?"); 
$stmt->execute([$user_id]); 
$user = $stmt->fetch(PDO::FETCH_ASSOC); 

// เมื่อมีกำรส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $full_name = trim($_POST['full_name']); 
    $email = trim($_POST['email']); 
    $current_password = $_POST['current_password'] ?? ''; 
    $new_password = $_POST['new_password'] ?? ''; 
    $confirm_password = $_POST['confirm_password'] ?? ''; 

    // ตรวจสอบชื่อและอีเมล
    if (empty($full_name) || empty($email)) { 
        $errors[] = "กรุณากรอกชื่อ-นามสกุลและอีเมล"; 
    } 

    // ตรวจสอบอีเมลซ้ำ
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_id != ?"); 
    $stmt->execute([$email, $user_id]); 
    if ($stmt->rowCount() > 0) { 
        $errors[] = "อีเมลนี้ถูกใช้งานแล้ว"; 
    } 

    // ตรวจสอบรหัสผ่านใหม่
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) { 
        if (!password_verify($current_password, $user['password'])) { 
            $errors[] = "รหัสผ่านเดิมไม่ถูกต้อง"; 
        } elseif (strlen($new_password) < 6) { 
            $errors[] = "รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร"; 
        } elseif ($new_password !== $confirm_password) { 
            $errors[] = "รหัสผ่านใหม่และการยืนยันไม่ตรงกัน"; 
        } else { 
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT); 
        } 
    } 

    // อัปเดตข้อมูลหากไม่มี error
    if (empty($errors)) { 
        if (!empty($new_hashed)) { 
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE user_id = ?"); 
            $stmt->execute([$full_name, $email, $new_hashed, $user_id]); 
        } else { 
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?"); 
            $stmt->execute([$full_name, $email, $user_id]); 
        } 

        $success = "บันทึกข้อมูลเรียบร้อยแล้ว"; 
        $_SESSION['username'] = $user['username']; 
        $user['full_name'] = $full_name; 
        $user['email'] = $email; 
    } 
} 
?> 

<!DOCTYPE html> 
<html lang="th"> 
<head> 
<meta charset="UTF-8"> 
<title>โปรไฟล์สมาชิก</title> 
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 
<style>
    body {
        background: linear-gradient(135deg, #ffe6f0, #e6f0ff, #fff9e6);
        font-family: "Prompt", sans-serif;
        min-height: 100vh;
    }
    h2, h5 {
        color: #d63384;
        font-weight: bold;
    }
    .btn-primary {
        background-color: #ff66b2;
        border: none;
        color: #fff;
    }
    .btn-primary:hover {
        background-color: #e60073;
    }
    .btn-secondary {
        background-color: #6c63ff;
        border: none;
        color: #fff;
    }
    .btn-secondary:hover {
        background-color: #4b42cc;
    }
    .alert-success {
        background-color: #e6ffe6;
        color: #2e7d32;
        border: 1px solid #b2fab4;
    }
    .alert-danger {
        background-color: #ffe6e6;
        color: #b71c1c;
        border: 1px solid #ff99a1;
    }
    .form-control, .form-select, textarea {
        border: 2px solid #ffcce6;
        transition: all 0.2s;
    }
    .form-control:focus, .form-select:focus, textarea:focus {
        border-color: #ff66b2;
        box-shadow: 0 0 5px rgba(255, 102, 178, 0.5);
    }
</style>
</head> 
<body> 
<div class="container mt-4"> 
    <h2>👤 โปรไฟล์ของคุณ</h2> 
    <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าหลัก</a> 

    <?php if (!empty($errors)): ?> 
        <div class="alert alert-danger"> 
            <ul> 
                <?php foreach ($errors as $e): ?> 
                    <li><?= htmlspecialchars($e) ?></li> 
                <?php endforeach; ?> 
            </ul> 
        </div> 
    <?php elseif (!empty($success)): ?> 
        <div class="alert alert-success"><?= $success ?></div> 
    <?php endif; ?> 

    <form method="post" class="row g-3"> 
        <div class="col-md-6"> 
            <label for="full_name" class="form-label">ชื่อ-นามสกุล</label> 
            <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name']) ?>"> 
        </div> 
        <div class="col-md-6"> 
            <label for="email" class="form-label">อีเมล</label> 
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>"> 
        </div> 

        <div class="col-12"> 
            <hr> 
            <h5>เปลี่ยนรหัสผ่าน (ไม่จำเป็น)</h5> 
        </div> 

        <div class="col-md-6"> 
            <label for="current_password" class="form-label">รหัสผ่านเดิม</label> 
            <input type="password" name="current_password" id="current_password" class="form-control"> 
        </div> 
        <div class="col-md-6"> 
            <label for="new_password" class="form-label">รหัสผ่านใหม่ (≥ 6 ตัวอักษร)</label> 
            <input type="password" name="new_password" id="new_password" class="form-control"> 
        </div> 
        <div class="col-md-6"> 
            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label> 
            <input type="password" name="confirm_password" id="confirm_password" class="form-control"> 
        </div> 
        <div class="col-12"> 
            <button type="submit" class="btn btn-primary">💾 บันทึกการเปลี่ยนแปลง</button> 
        </div> 
    </form> 
</div> 
</body> 
</html>
