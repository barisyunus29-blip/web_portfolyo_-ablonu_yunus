<?php
/**
 * Araba Parçaları E-Ticaret Web Sitesi - Konfigürasyon
 */

// Veritabanı bilgileri
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'car_parts_store');
define('DB_PORT', 3306);

// Site bilgileri
define('SITE_NAME', 'Araba Parçaları Mağazası');
define('SITE_URL', 'http://localhost/car_parts_website_flat');
define('ADMIN_EMAIL', 'admin@arabaparçalari.com');

// Oturum başlat
session_start();

// Veritabanı bağlantısı
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Hata: " . $e->getMessage());
}

// Güvenlik fonksiyonları
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function json_response($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . SITE_URL . '/login.html');
        exit;
    }
}

function require_admin() {
    if (!is_admin()) {
        header('Location: ' . SITE_URL . '/index.html');
        exit;
    }
}

function format_price($price) {
    return number_format($price, 2, ',', '.') . ' ₺';
}

function create_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = preg_replace('~-+~', '-', $text);
    $text = trim($text, '-');
    return strtolower($text);
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function log_error($message) {
    $log_file = __DIR__ . '/error.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}
?>
