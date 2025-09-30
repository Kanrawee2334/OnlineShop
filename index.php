<?php  
session_start();  
require_once 'config.php';  

$isLoggedIn = isset($_SESSION['user_id']);  


$stmt = $conn->query("SELECT p.*, c.category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.category_id 
                      ORDER BY p.created_at DESC"); 
$products = $stmt->fetchAll(PDO::FETCH_ASSOC); 
?> 

<!DOCTYPE html> 
<html lang="th"> 
<head>  
  <meta charset="UTF-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1.0">  
  <title>หน้าหลัก</title>  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">  
  <style>  
    .product-card { border: 1; background:#fff; }  
    .product-thumb { height: 180px; object-fit: cover; border-radius:.5rem; }  
    .product-meta { font-size:.75rem; letter-spacing:.05em; color:#8a8f98; text-transform:uppercase; }  
    .product-title { font-size:1rem; margin:.25rem 0 .5rem; font-weight:600; color:#222; }  
    .price { font-weight:700; }  
    .rating i { color:#ffc107; } 
    .wishlist { color:#b9bfc6; }  
    .wishlist:hover { color:#ff5b5b; }  
    .badge-top-left {  
      position:absolute; top:.5rem; left:.5rem; z-index:2;  
      border-radius:.375rem ;  
    }  
    body {  
      background: linear-gradient(135deg, #ffe6f2, #f2e6ff, #e6faff, #fffbe6);  
      font-family: 'Kanit', sans-serif;  
      min-height: 100vh;  
    }  
    h1 {  
      color: #d63384; /* ชมพูสด */  
      font-weight: 700;  
    }  
    .card {  
      border-radius: 16px;  
      border: 2px solid #f8bbd0; /* ชมพูพาสเทล */  
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);  
    }  
    .card-title {  
      color: #9c27b0; /* ม่วง */  
    }  
    .card-subtitle {  
      color: #03a9f4; /* ฟ้า */  
    }  
    .btn-success {  
      background-color: #ff66b2; /* ชมพูสด */  
      border: none;  
    }  
    .btn-success:hover {  
      background-color: #e0559d;  
    }  
    .btn-outline-primary {  
      border-color: #ba68c8; /* ม่วงอ่อน */  
      color: #9c27b0;  
    }  
    .btn-outline-primary:hover {  
      background-color: #ba68c8;  
      color: #fff;  
    }  
    .btn-warning {  
      background-color: #ffeb3b; /* เหลืองสด */  
      border: none;  
      color: #333;  
    }  
    .btn-warning:hover {  
      background-color: #fdd835;  
    }  
    .btn-info {  
      background-color: #4dd0e1; /* ฟ้าอมเขียว */  
      border: none;  
      color: #fff;  
    }  
    .btn-info:hover {  
      background-color: #26c6da;  
    }  
    .btn-secondary {  
      background-color: #bdbdbd;  
      border: none;  
    }  
    .welcome {  
      font-weight: 600;  
      color: #6f42c1; /* ม่วงเข้ม */  
    }  
  </style> 
</head> 
<body class="container mt-4">  

  <div class="d-flex justify-content-between align-items-center mb-4">  
    <h1>🌸 รายการสินค้า 🌈</h1>  
    <div>  
      <?php if ($isLoggedIn): ?>  
        <span class="me-3 welcome">  
          ยินดีต้อนรับ <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] ?>)  
        </span>  
        <a href="profile.php" class="btn btn-info">ข้อมูลส่วนตัว</a>  
        <a href="cart.php" class="btn btn-warning">ดูตะกร้า</a>  
        <a href="orders.php" class="btn btn-primary">ประวัติคำสั่งซื้อ</a>  
        <a href="logout.php" class="btn btn-secondary">ออกจากระบบ</a>  
      <?php else: ?>  
        <a href="login.php" class="btn btn-success">เข้าสู่ระบบ</a>  
        <a href="register.php" class="btn btn-outline-primary">สมัครสมาชิก</a>  
      <?php endif; ?>  
    </div>  
  </div>  

  <!-- ===== ส่วนแสดงสินค้า ===== -->  
  <div class="row g-4">  
    <?php foreach ($products as $p): ?>  
    <?php  
      // เตรียมรูป
      $uploadDir = __DIR__ . '/product_images/'; 
      $uploadUrl = 'product_images/';  

      if (!empty($p['image']) && file_exists($uploadDir . $p['image'])) {  
          $img = $uploadUrl . rawurlencode($p['image']);  
      } else {  
          $img = $uploadUrl . 'no-image.jpg';  
      }  

      // Badge: NEW ภายใน 7 วัน / HOT ถ้าสต็อกน้อยกว่า 5
      $isNew = isset($p['created_at']) && (time() - strtotime($p['created_at']) <= 7*24*3600);  
      $isHot = (int)$p['stock'] > 0 && (int)$p['stock'] < 5;  

      // ดาวรีวิว
      $rating = isset($p['rating']) ? (float)$p['rating'] : 4.5;  
      $full = floor($rating);  
      $half = ($rating - $full) >= 0.5 ? 1 : 0;  
    ?>  

    <div class="col-12 col-sm-6 col-lg-3">  
      <div class="card product-card h-100 position-relative">  

        <?php if ($isNew): ?>  
          <span class="badge bg-success badge-top-left">NEW</span>  
        <?php elseif ($isHot): ?>  
          <span class="badge bg-danger badge-top-left">HOT</span>  
        <?php endif; ?>  

        <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="p-3 d-block">  
          <img src="<?= htmlspecialchars($img) ?>"  
               alt="<?= htmlspecialchars($p['product_name']) ?>"  
               class="img-fluid w-100 product-thumb">  
        </a>  

        <div class="px-3 pb-3 d-flex flex-column">  
          <div class="d-flex justify-content-between align-items-center mb-1">  
            <div class="product-meta">  
              <?= htmlspecialchars($p['category_name'] ?? 'Category') ?>  
            </div>  
            <button class="btn btn-link p-0 wishlist" title="Add to wishlist" type="button">  
              <i class="bi bi-heart"></i>  
            </button>  
          </div>  

          <a class="text-decoration-none" href="product_detail.php?id=<?= (int)$p['product_id'] ?>">  
            <div class="product-title">  
              <?= htmlspecialchars($p['product_name']) ?>  
            </div>  
          </a>  

          <div class="rating mb-2">  
            <?php for ($i=0; $i<$full; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>  
            <?php if ($half): ?><i class="bi bi-star-half"></i><?php endif; ?>  
            <?php for ($i=0; $i<5-$full-$half; $i++): ?><i class="bi bi-star"></i><?php endfor; ?>  
          </div>  

          <div class="price mb-3">  
            <?= number_format((float)$p['price'], 2) ?> บาท  
          </div>  

          <div class="mt-auto d-flex gap-2">  
            <?php if ($isLoggedIn): ?>  
              <form action="cart.php" method="post" class="d-inline-flex gap-2">  
                <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">  
                <input type="hidden" name="quantity" value="1">  
                <button type="submit" class="btn btn-sm btn-success">เพิ่มในตะกร้า</button>  
              </form>  
            <?php else: ?>  
              <small class="text-muted">เข้าสู่ระบบเพื่อสั่งซื้อ </small>  
            <?php endif; ?>  

            <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>"  
               class="btn btn-sm btn-outline-primary ms-auto">ดูรายละเอียด</a>  
          </div>  
        </div>  
      </div>  
    </div>  
    <?php endforeach; ?>  
  </div>  

</body> 
</html>
