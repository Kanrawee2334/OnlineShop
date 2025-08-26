<?php 
require_once 'config.php';

$error = []; //Array to hold error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

        // ตรวจสอบว่ำกรอกข้อมูลมำครบหรือไม่ (empty)
    if(empty($username)||empty($fullname)||empty($email)||empty($password)||empty($confirm_password)){
        $error[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    }elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){  
        // ตรวจสอบว่าอีเมลถูกต้องหรือไม่ (filter_var)
        $error[] = "กรุณากรอกอีเมลให้ถูกต้อง";

        // ตรวจสอบว่ำรหัสผ่ำนและกำรยืนยันตรงกันหรือไม
    } elseif ($password !== $confirm_password) {
        $error[] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
        }else{
        // ตรวจสอบว่าชื่อผู้ใช้หรืออีเมลถูกใช้ไปแล้วหรือไม่
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $email]);

        if($stmt->rowCount() > 0){
            $error[] = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปแล้ว";
            }
        }

        if(empty($error)){  //ถ้าไม่มีข้อผิดพลาดใดๆ
            // นำข้อมูลบันทึกลงฐานข้อมูล
            if ($password === $confirm_password) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    $sql = "INSERT INTO users (username, full_name, email, password, role) VALUES (?, ?, ?, ?, 'member')";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$username, $fullname, $email, $hashedPassword]);

                // ถ้บันทึกสำเร็จ ให้เปลี่ยนเส้นทางไปหน้า login
                header("Location: login.php?register=success");
                exit(); // หยุดงานทำงานของสคริปต์หลังจากเปลี่ยนเส้นทาง
                   
            }


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
</head>
<style>
        body {
            background-color: #fe8ebaff; 
        }
    </style>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4 text-primary">สมัครสมาชิก</h3>

    <?php if (!empty($error)): // ถ้ามีข้อผิดพลาด ให้แสดงข ้อควำม ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($error as $e): ?>
                  <li><?= htmlspecialchars($e) ?></li>
                  <!--ใช ้ htmlspecialchars เพื่อป้องกัน XSS -->
                  <!-- < ? = คือ short echo tag ?> -->
                  <!-- ถ ้ำเขียนเต็ม จะได ้แบบด ้ำนล่ำง -->
                  <?php // echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                            <input type="text" name="username" id="username" class="form-control" 
                            placeholder="ชื่อผู้ใช้" value ="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="fullname" class="form-label">ชื่อ-สกุล</label>
                            <input type="text" name="fullname" id="fullname" class="form-control" 
                            placeholder="ชื่อ-สกุล" value ="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล</label>
                            <input type="email" name="email" id="email" class="form-control" 
                            placeholder="อีเมล"  value ="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">รหัสผ่าน</label>
                            <input type="password" name="password" id="password" class="form-control" 
                            placeholder="รหัสผ่าน" require>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                            placeholder="ยืนยันรหัสผ่าน" require>
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