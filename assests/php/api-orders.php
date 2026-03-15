<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    json_response(false, 'Oturum süresi doldu');
}

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'create':
        handleCreate();
        break;
    
    case 'list':
        handleList();
        break;
    
    case 'get':
        handleGet();
        break;
    
    default:
        json_response(false, 'Geçersiz işlem');
}

function handleCreate() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $shipping_address = sanitize($data['shipping_address'] ?? '');
    $shipping_city = sanitize($data['shipping_city'] ?? '');
    $shipping_postal_code = sanitize($data['shipping_postal_code'] ?? '');
    $payment_method = sanitize($data['payment_method'] ?? 'credit_card');
    $notes = sanitize($data['notes'] ?? '');
    
    if (!$shipping_address || !$shipping_city || !$shipping_postal_code) {
        json_response(false, 'Kargo bilgileri eksik');
    }
    
    // Sepeti kontrol et
    $cart_result = $conn->query("SELECT c.product_id, c.quantity, p.price, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
    
    if ($cart_result->num_rows === 0) {
        json_response(false, 'Sepet boş');
    }
    
    $total_price = 0;
    $cart_items = [];
    
    while ($item = $cart_result->fetch_assoc()) {
        if ($item['stock'] < $item['quantity']) {
            json_response(false, 'Yeterli stok yok: ' . $item['product_id']);
        }
        $cart_items[] = $item;
        $total_price += $item['price'] * $item['quantity'];
    }
    
    // Sipariş oluştur
    $order_number = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);
    
    $sql = "INSERT INTO orders (user_id, order_number, total_price, shipping_address, shipping_city, shipping_postal_code, payment_method, notes) 
            VALUES ($user_id, '$order_number', $total_price, '$shipping_address', '$shipping_city', '$shipping_postal_code', '$payment_method', '$notes')";
    
    if (!$conn->query($sql)) {
        json_response(false, 'Sipariş oluşturulamadı: ' . $conn->error);
    }
    
    $order_id = $conn->insert_id;
    
    // Sipariş öğelerini ekle
    foreach ($cart_items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $product_id, $quantity, $price)");
        
        // Stok güncelle
        $conn->query("UPDATE products SET stock = stock - $quantity WHERE id = $product_id");
    }
    
    // Sepeti temizle
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");
    
    // E-posta gönder
    sendOrderEmail($order_id, $order_number, $total_price);
    
    json_response(true, 'Sipariş başarıyla oluşturuldu', [
        'order_id' => $order_id,
        'order_number' => $order_number,
        'total_price' => $total_price
    ]);
}

function handleList() {
    global $conn, $user_id;
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $result = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    
    $total_result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id");
    $total = $total_result->fetch_assoc()['count'];
    $total_pages = ceil($total / $limit);
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    json_response(true, 'Siparişler listelendi', [
        'orders' => $orders,
        'page' => $page,
        'total_pages' => $total_pages,
        'total' => $total
    ]);
}

function handleGet() {
    global $conn, $user_id;
    
    $order_id = (int)($_GET['id'] ?? 0);
    
    if ($order_id <= 0) {
        json_response(false, 'Geçersiz sipariş ID');
    }
    
    $result = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id");
    
    if ($result->num_rows === 0) {
        json_response(false, 'Sipariş bulunamadı');
    }
    
    $order = $result->fetch_assoc();
    
    // Sipariş öğelerini getir
    $items_result = $conn->query("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $order_id");
    
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    $order['items'] = $items;
    
    json_response(true, 'Sipariş detayı', $order);
}

function sendOrderEmail($order_id, $order_number, $total_price) {
    global $conn;
    
    $result = $conn->query("SELECT u.email, u.name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = $order_id");
    
    if ($result->num_rows === 0) {
        return;
    }
    
    $order = $result->fetch_assoc();
    $email = $order['email'];
    $name = $order['name'];
    
    $subject = "Siparişiniz Oluşturuldu - $order_number";
    
    $message = "
    <html>
    <head>
        <title>Sipariş Onayı</title>
    </head>
    <body>
        <h2>Sipariş Onayı</h2>
        <p>Sayın $name,</p>
        <p>Siparişiniz başarıyla oluşturulmuştur.</p>
        <p><strong>Sipariş Numarası:</strong> $order_number</p>
        <p><strong>Toplam Tutar:</strong> " . format_price($total_price) . "</p>
        <p>Siparişiniz kısa süre içinde kargoya verilecektir.</p>
        <p>Teşekkür ederiz!</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . ADMIN_EMAIL . "\r\n";
    
    mail($email, $subject, $message, $headers);
    
    // Admin'e bildir
    $admin_subject = "Yeni Sipariş - $order_number";
    $admin_message = "
    <html>
    <head>
        <title>Yeni Sipariş</title>
    </head>
    <body>
        <h2>Yeni Sipariş Alındı</h2>
        <p><strong>Müşteri:</strong> $name ($email)</p>
        <p><strong>Sipariş Numarası:</strong> $order_number</p>
        <p><strong>Toplam Tutar:</strong> " . format_price($total_price) . "</p>
    </body>
    </html>
    ";
    
    mail(ADMIN_EMAIL, $admin_subject, $admin_message, $headers);
}
?>
