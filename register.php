<?php 
require_once 'config.php';

$error = []; //Array to hold error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if(empty($username)||empty($fullname)||empty($email)||empty($password)||empty($confirm_password)){
        $error[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){  
        $error[] = "กรุณากรอกอีเมลให้ถูกต้อง";
    } elseif ($password !== $confirm_password) {
        $error[] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    } else {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $email]);

        if($stmt->rowCount() > 0){
            $error[] = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปแล้ว";
        }
    }

    if(empty($error)){
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, full_name, email, password, role) VALUES (?, ?, ?, ?, 'member')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $hashedPassword]);

        header("Location: login.php?register=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>สมัครสมาชิก</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #ffe6f2, #f2e6ff, #e6f7ff, #fff9e6);
      min-height: 100vh;
      display: flex;
      align-items: center;
      font-family: 'Kanit', sans-serif;
    }
    .card {
      border-radius: 20px;
      background: #fff;
      box-shadow: 0 8px 18px rgba(0,0,0,0.1);
    }
    h3 {
      font-weight: 700;
      color: #d63384; /* ชมพูสดใส */
    }
    .btn-primary {
      background: linear-gradient(45deg, #ff66b2, #cc66ff, #66ccff, #ffdb66);
      border: none;
      border-radius: 30px;
      font-weight: 600;
      transition: transform .2s ease, opacity .2s ease;
    }
    .btn-primary:hover {
      transform: scale(1.05);
      opacity: 0.9;
    }
    .btn-outline-secondary {
      border-radius: 30px;
      font-weight: 600;
      border-color: #ff99cc;
      color: #cc66cc;
    }
    .btn-outline-secondary:hover {
      background: #ffe6f2;
      border-color: #ff66b2;
      color: #b30086;
    }
    .form-control {
      border-radius: 12px;
      border: 1.5px solid #f5c2e7;
    }
    .form-control:focus {
      border-color: #d63384;
      box-shadow: 0 0 6px rgba(214, 51, 132, 0.4);
    }
    .alert-danger {
      border-radius: 15px;
      background: #ffe6eb;
      color: #cc0033;
      border: none;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-lg p-4">
        <h3 class="text-center mb-4">🌸 สมัครสมาชิก 🌈</h3>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($error as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="" method="post">
          <div class="mb-3">
            <label for="username" class="form-label">ชื่อผู้ใช้</label>
            <input type="text" name="username" id="username" class="form-control"
              placeholder="ชื่อผู้ใช้"
              value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
          </div>
          <div class="mb-3">
            <label for="fullname" class="form-label">ชื่อ-สกุล</label>
            <input type="text" name="fullname" id="fullname" class="form-control"
              placeholder="ชื่อ-สกุล"
              value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">อีเมล</label>
            <input type="email" name="email" id="email" class="form-control"
              placeholder="อีเมล"
              value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control"
              placeholder="รหัสผ่าน" required>
          </div>
          <div class="mb-3">
            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control"
              placeholder="ยืนยันรหัสผ่าน" required>
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

</body>
</html>
