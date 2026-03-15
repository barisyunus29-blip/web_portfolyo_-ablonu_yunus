<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    json_response(false, 'Oturum süresi doldu');
}

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'list':
        handleList();
        break;
    
    case 'add':
        handleAdd();
        break;
    
    case 'update':
        handleUpdate();
        break;
    
    case 'remove':
        handleRemove();
        break;
    
    case 'clear':
        handleClear();
        break;
    
    default:
        json_response(false, 'Geçersiz işlem');
}

function handleList() {
    global $conn, $user_id;
    
    $result = $conn->query("
        SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.stock, p.image_url
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = $user_id
    ");
    
    $items = [];
    $total = 0;
    
    while ($row = $result->fetch_assoc()) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total += $row['subtotal'];
        $items[] = $row;
    }
    
    json_response(true, 'Sepet listelendi', [
        'items' => $items,
        'total' => $total,
        'count' => count($items)
    ]);
}

function handleAdd() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $product_id = (int)($data['product_id'] ?? 0);
    $quantity = (int)($data['quantity'] ?? 1);
    
    if ($product_id <= 0 || $quantity <= 0) {
        json_response(false, 'Geçersiz ürün veya miktar');
    }
    
    // Ürün kontrolü
    $product_result = $conn->query("SELECT stock FROM products WHERE id = $product_id");
    if ($product_result->num_rows === 0) {
        json_response(false, 'Ürün bulunamadı');
    }
    
    $product = $product_result->fetch_assoc();
    if ($product['stock'] < $quantity) {
        json_response(false, 'Yeterli stok yok');
    }
    
    // Sepet kontrolü
    $cart_result = $conn->query("SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id");
    
    if ($cart_result->num_rows > 0) {
        $cart = $cart_result->fetch_assoc();
        $new_quantity = $cart['quantity'] + $quantity;
        $conn->query("UPDATE cart SET quantity = $new_quantity WHERE id = {$cart['id']}");
    } else {
        $conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)");
    }
    
    json_response(true, 'Ürün sepete eklendi');
}

function handleUpdate() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $cart_id = (int)($data['cart_id'] ?? 0);
    $quantity = (int)($data['quantity'] ?? 0);
    
    if ($cart_id <= 0 || $quantity <= 0) {
        json_response(false, 'Geçersiz sepet veya miktar');
    }
    
    // Sepet kontrolü
    $result = $conn->query("SELECT product_id FROM cart WHERE id = $cart_id AND user_id = $user_id");
    if ($result->num_rows === 0) {
        json_response(false, 'Sepet öğesi bulunamadı');
    }
    
    $cart = $result->fetch_assoc();
    $product_id = $cart['product_id'];
    
    // Stok kontrolü
    $product_result = $conn->query("SELECT stock FROM products WHERE id = $product_id");
    $product = $product_result->fetch_assoc();
    
    if ($product['stock'] < $quantity) {
        json_response(false, 'Yeterli stok yok');
    }
    
    $conn->query("UPDATE cart SET quantity = $quantity WHERE id = $cart_id");
    json_response(true, 'Miktar güncellendi');
}

function handleRemove() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $cart_id = (int)($data['cart_id'] ?? 0);
    
    if ($cart_id <= 0) {
        json_response(false, 'Geçersiz sepet ID');
    }
    
    $result = $conn->query("DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
    
    if ($conn->affected_rows > 0) {
        json_response(true, 'Ürün sepetten çıkarıldı');
    } else {
        json_response(false, 'Sepet öğesi bulunamadı');
    }
}

function handleClear() {
    global $conn, $user_id;
    
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");
    json_response(true, 'Sepet temizlendi');
}
?>
