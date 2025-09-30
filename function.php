<?php
// -----------------------------
// ฟังก์ชันดึงรายการสินค้าในคำสั่งซื้อ
// -----------------------------
function getOrderItems($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT oi.quantity, oi.price, p.product_name
                           FROM order_items oi
                           JOIN products p ON oi.product_id = p.product_id
                           WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// -----------------------------
// ฟังก์ชันดึงข้อมูลจัดส่ง
// -----------------------------
function getShippingInfo($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT * FROM shipping WHERE order_id = ?"); // shipping table
    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>