<?php
session_start();
require '../config.php';
require '../function.php';   // ดึงฟังก์ชันที่เก็บไว้

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ดึงคำสั่งซื้อทั้งหมด
$stmt = $conn->query("
    SELECT o.*, u.username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// อัปเดตสถานะคำสั่งซื้อ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        $_SESSION['swal_success'] = "อัปเดตสถานะคำสั่งซื้อเรียบร้อยแล้ว!";
        header("Location: orders.php");
        exit;
    }
    if (isset($_POST['update_shipping'])) {
        $stmt = $conn->prepare("UPDATE shipping SET shipping_status = ? WHERE shipping_id = ?");
        $stmt->execute([$_POST['shipping_status'], $_POST['shipping_id']]);
        $_SESSION['swal_success'] = "อัปเดตสถานะการจัดส่งเรียบร้อยแล้ว!";
        header("Location: orders.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการคำสั่งซื้อ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    .accordion-item {
        border: 2px solid #ffd6eb;
        border-radius: 12px;
        margin-bottom: 12px;
        overflow: hidden;
        transition: transform 0.2s;
    }
    .accordion-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }
    .accordion-button {
        background-color: #fff0f6;
        color: #d63384;
        font-weight: bold;
    }
    .accordion-button:not(.collapsed) {
        background: linear-gradient(90deg, #f8bbd0, #ba68c8, #81d4fa, #fff176);
        color: #4a004d;
    }
    .accordion-body {
        background: #fff;
    }
    .list-group-item {
        border: none;
        border-bottom: 1px solid #f3e5f5;
        color: #555;
    }
    .btn-primary {
        background-color: #ff66b2;
        border: none;
        color: #fff;
    }
    .btn-primary:hover {
        background-color: #e60073;
    }
    .btn-warning {
        background-color: #ffcc00;
        border: none;
        color: #4a004d;
    }
    .btn-warning:hover {
        background-color: #e6b800;
    }
    .btn-danger {
        background-color: #ff4d94;
        border: none;
        color: #fff;
    }
    .btn-danger:hover {
        background-color: #cc0066;
    }
    .btn-secondary {
        background-color: #6c63ff;
        border: none;
        color: #fff;
    }
    .btn-secondary:hover {
        background-color: #4b42cc;
    }
    .btn-success {
        background-color: #ba68c8;
        border: none;
        color: #fff;
    }
    .btn-success:hover {
        background-color: #8e24aa;
    }
    table thead {
        background-color: #ffd6eb;
        color: #4a004d;
    }
    table tbody tr:hover {
        background-color: #fff0f6;
    }
    .form-control, .form-select, textarea {
        border: 2px solid #ffcce6;
    }
    .form-control:focus, .form-select:focus, textarea:focus {
        border-color: #ff66b2;
        box-shadow: 0 0 5px rgba(255, 102, 178, 0.5);
    }
    /* Badge สีและ icon */
    .badge-pending { background: #ffe0e6; color: #b71c1c; }
    .badge-processing { background: #ffd6eb; color: #ad1457; }
    .badge-shipped { background: #e6e6ff; color: #1a237e; }
    .badge-completed { background: #e6ffe6; color: #2e7d32; }
    .badge-cancelled { background: #ffd6d6; color: #c62828; }
</style>
</head>
<body class="container mt-4">

<h2>📦 จัดการคำสั่งซื้อ</h2>
<a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>

<div class="accordion" id="ordersAccordion">
<?php foreach ($orders as $index => $order): ?>
    <?php $shipping = getShippingInfo($conn, $order['order_id']); ?>

    <?php
    $statusInfo = match($order['status']) {
        'pending' => ['class' => 'badge-pending', 'icon' => '⏳'],
        'processing' => ['class' => 'badge-processing', 'icon' => '🔄'],
        'shipped' => ['class' => 'badge-shipped', 'icon' => '🚚'],
        'completed' => ['class' => 'badge-completed', 'icon' => '✅'],
        'cancelled' => ['class' => 'badge-cancelled', 'icon' => '❌'],
        default => ['class' => 'badge bg-secondary', 'icon' => 'ℹ️']
    };
    ?>

    <div class="accordion-item">
        <h2 class="accordion-header" id="heading<?= $index ?>">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="false" aria-controls="collapse<?= $index ?>">
                คำสั่งซื้อ #<?= $order['order_id'] ?> | <?= htmlspecialchars($order['username']) ?> | <?= $order['order_date'] ?> | 
                <span class="badge <?= $statusInfo['class'] ?>"><?= $statusInfo['icon'] ?> <?= ucfirst($order['status']) ?></span>
            </button>
        </h2>
        <div id="collapse<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $index ?>" data-bs-parent="#ordersAccordion">
            <div class="accordion-body">
                <h5>🛒 รายการสินค้า</h5>
                <ul class="list-group mb-3">
                    <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?>
                            <span><?= number_format($item['quantity'] * $item['price'], 2) ?> บาท</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>💰 ยอดรวม:</strong> <?= number_format($order['total_amount'], 2) ?> บาท</p>

                <form method="post" class="row g-2 mb-3 update-form">
                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <?php
                            $statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
                            foreach ($statuses as $status) {
                                $selected = ($order['status'] === $status) ? 'selected' : '';
                                echo "<option value=\"$status\" $selected>$status</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="update_status" class="btn btn-primary">อัปเดตสถานะ</button>
                    </div>
                </form>

                <?php if ($shipping): ?>
                    <?php
                    $s_statusInfo = match($shipping['shipping_status']) {
                        'not_shipped' => ['class' => 'badge-pending', 'icon' => '📦'],
                        'shipped' => ['class' => 'badge-shipped', 'icon' => '🚚'],
                        'delivered' => ['class' => 'badge-completed', 'icon' => '🏠'],
                        default => ['class' => 'badge bg-secondary', 'icon' => 'ℹ️']
                    };
                    ?>
                    <h5>🚚 ข้อมูลจัดส่ง</h5>
                    <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($shipping['address']) ?>, <?= htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?></p>
                    <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?></p>
                    <form method="post" class="row g-2">
                        <input type="hidden" name="shipping_id" value="<?= $shipping['shipping_id'] ?>">
                        <div class="col-md-4">
                            <select name="shipping_status" class="form-select">
                                <?php
                                $s_statuses = ['not_shipped', 'shipped', 'delivered'];
                                foreach ($s_statuses as $s) {
                                    $selected = ($shipping['shipping_status'] === $s) ? 'selected' : '';
                                    echo "<option value=\"$s\" $selected>$s</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="update_shipping" class="btn btn-success">อัปเดตการจัดส่ง</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
