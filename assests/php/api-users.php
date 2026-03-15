<?php
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegister();
        break;
    
    case 'login':
        handleLogin();
        break;
    
    case 'logout':
        handleLogout();
        break;
    
    case 'profile':
        handleProfile();
        break;
    
    default:
        json_response(false, 'Geçersiz işlem');
}

function handleRegister() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = sanitize($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $password_confirm = $data['password_confirm'] ?? '';
    $name = sanitize($data['name'] ?? '');
    
    if (!$email || !$password || !$password_confirm || !$name) {
        json_response(false, 'Tüm alanlar gereklidir');
    }
    
    if (!validate_email($email)) {
        json_response(false, 'Geçersiz e-posta adresi');
    }
    
    if ($password !== $password_confirm) {
        json_response(false, 'Şifreler eşleşmiyor');
    }
    
    if (strlen($password) < 6) {
        json_response(false, 'Şifre en az 6 karakter olmalıdır');
    }
    
    // E-posta kontrolü
    $result = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        json_response(false, 'Bu e-posta adresi zaten kayıtlı');
    }
    
    $hashed_password = hash_password($password);
    
    $sql = "INSERT INTO users (email, password, name, role) VALUES ('$email', '$hashed_password', '$name', 'user')";
    
    if ($conn->query($sql)) {
        json_response(true, 'Kayıt başarılı');
    } else {
        json_response(false, 'Kayıt başarısız: ' . $conn->error);
    }
}

function handleLogin() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = sanitize($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    if (!$email || !$password) {
        json_response(false, 'E-posta ve şifre gereklidir');
    }
    
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    
    if ($result->num_rows === 0) {
        json_response(false, 'E-posta veya şifre yanlış');
    }
    
    $user = $result->fetch_assoc();
    
    if (!verify_password($password, $user['password'])) {
        json_response(false, 'E-posta veya şifre yanlış');
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    
    json_response(true, 'Giriş başarılı', [
        'id' => $user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'role' => $user['role']
    ]);
}

function handleLogout() {
    session_destroy();
    json_response(true, 'Çıkış başarılı');
}

function handleProfile() {
    if (!is_logged_in()) {
        json_response(false, 'Oturum süresi doldu');
    }
    
    global $conn;
    
    $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT id, email, name, phone, address, city, postal_code, role FROM users WHERE id = $user_id");
    
    if ($result->num_rows === 0) {
        json_response(false, 'Kullanıcı bulunamadı');
    }
    
    $user = $result->fetch_assoc();
    json_response(true, 'Profil bilgisi', $user);
}
?>
