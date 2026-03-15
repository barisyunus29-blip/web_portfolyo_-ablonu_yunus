<?php
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        handleList();
        break;
    
    case 'get':
        handleGet();
        break;
    
    case 'search':
        handleSearch();
        break;
    
    case 'featured':
        handleFeatured();
        break;
    
    case 'by_category':
        handleByCategory();
        break;
    
    case 'categories':
        handleCategories();
        break;
    
    case 'category':
        handleCategory();
        break;
    
    default:
        json_response(false, 'Geçersiz işlem');
}

function handleList() {
    global $conn;
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = 12;
    $offset = ($page - 1) * $limit;
    
    $result = $conn->query("SELECT * FROM products LIMIT $limit OFFSET $offset");
    
    $total_result = $conn->query("SELECT COUNT(*) as count FROM products");
    $total = $total_result->fetch_assoc()['count'];
    $total_pages = ceil($total / $limit);
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    json_response(true, 'Ürünler listelendi', [
        'products' => $products,
        'page' => $page,
        'total_pages' => $total_pages,
        'total' => $total
    ]);
}

function handleGet() {
    global $conn;
    
    $id = (int)($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        json_response(false, 'Geçersiz ürün ID');
    }
    
    $result = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = $id");
    
    if ($result->num_rows === 0) {
        json_response(false, 'Ürün bulunamadı');
    }
    
    $product = $result->fetch_assoc();
    json_response(true, 'Ürün detayı', $product);
}

function handleSearch() {
    global $conn;
    
    $query = sanitize($_GET['query'] ?? '');
    
    if (strlen($query) < 2) {
        json_response(false, 'Arama terimi en az 2 karakter olmalıdır');
    }
    
    $result = $conn->query("SELECT * FROM products WHERE name LIKE '%$query%' OR description LIKE '%$query%' LIMIT 20");
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    json_response(true, 'Arama sonuçları', ['products' => $products]);
}

function handleFeatured() {
    global $conn;
    
    $result = $conn->query("SELECT * FROM products WHERE featured = TRUE LIMIT 12");
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    json_response(true, 'Öne çıkan ürünler', ['products' => $products]);
}

function handleByCategory() {
    global $conn;
    
    $category_id = (int)($_GET['category_id'] ?? 0);
    $page = (int)($_GET['page'] ?? 1);
    $limit = 12;
    $offset = ($page - 1) * $limit;
    
    if ($category_id <= 0) {
        json_response(false, 'Geçersiz kategori ID');
    }
    
    $result = $conn->query("SELECT * FROM products WHERE category_id = $category_id LIMIT $limit OFFSET $offset");
    
    $total_result = $conn->query("SELECT COUNT(*) as count FROM products WHERE category_id = $category_id");
    $total = $total_result->fetch_assoc()['count'];
    $total_pages = ceil($total / $limit);
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    json_response(true, 'Kategori ürünleri', [
        'products' => $products,
        'page' => $page,
        'total_pages' => $total_pages,
        'total' => $total
    ]);
}

function handleCategories() {
    global $conn;
    
    $result = $conn->query("SELECT * FROM categories ORDER BY name");
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    json_response(true, 'Kategoriler', ['categories' => $categories]);
}

function handleCategory() {
    global $conn;
    
    $id = (int)($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        json_response(false, 'Geçersiz kategori ID');
    }
    
    $result = $conn->query("SELECT * FROM categories WHERE id = $id");
    
    if ($result->num_rows === 0) {
        json_response(false, 'Kategori bulunamadı');
    }
    
    $category = $result->fetch_assoc();
    json_response(true, 'Kategori detayı', $category);
}
?>
