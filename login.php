<?php
session_start();
require_once 'config.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $usernameOremail = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usernameOremail, $usernameOremail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        if($user['role'] === 'admin'){
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #ffe6f2, #f2e6ff, #e6faff, #fffbe6);
      font-family: 'Kanit', sans-serif;
    }
    .card {
      border-radius: 20px;
      background: #fff;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    h3 {
      font-weight: 700;
      color: #d63384; /* ชมพูสดใส */
    }
    .btn-success {
      background: linear-gradient(45deg, #ff66b2, #cc66ff, #66ccff, #ffdb66);
      border: none;
      border-radius: 30px;
      font-weight: 600;
      transition: transform .2s ease, opacity .2s ease;
    }
    .btn-success:hover {
      transform: scale(1.05);
      opacity: 0.9;
    }
    .btn-link {
      color: #cc66cc;
      font-weight: 600;
      text-decoration: none;
    }
    .btn-link:hover {
      text-decoration: underline;
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
    .alert-success {
      background: #e6fffa;
      border: none;
      border-radius: 15px;
      color: #0f5132;
    }
    .alert-danger {
      background: #ffe6eb;
      border: none;
      border-radius: 15px;
      color: #842029;
    }
  </style>
</head>
<body>

<div class="container">
  <?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
    <div class="alert alert-success text-center mb-4">
      สมัครสมาชิกสำเร็จ 🎉 กรุณาเข้าสู่ระบบ
    </div>
  <?php endif; ?>
  
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger text-center mb-4">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card p-4">
        <h3 class="text-center mb-4">🌸 เข้าสู่ระบบ 🌈</h3>
        <form method="post" class="row g-3">
          <div class="col-12">
            <label for="username_or_email" class="form-label">ชื่อผู้ใช้ หรืออีเมล</label>
            <input type="text" name="username_or_email" id="username_or_email" class="form-control" required>
          </div>
          <div class="col-12">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control" required>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-success w-100 mb-2">เข้าสู่ระบบ</button>
            <a href="register.php" class="btn btn-link">ยังไม่มีบัญชี? สมัครสมาชิก</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
