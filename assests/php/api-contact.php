<?php
require_once 'config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'submit':
        handleSubmit();
        break;
    
    case 'list':
        handleList();
        break;
    
    case 'mark_read':
        handleMarkRead();
        break;
    
    default:
        json_response(false, 'Geçersiz işlem');
}

function handleSubmit() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = sanitize($data['name'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $phone = sanitize($data['phone'] ?? '');
    $subject = sanitize($data['subject'] ?? '');
    $message = sanitize($data['message'] ?? '');
    
    if (!$name || !$email || !$subject || !$message) {
        json_response(false, 'Gerekli alanlar eksik');
    }
    
    if (!validate_email($email)) {
        json_response(false, 'Geçersiz e-posta adresi');
    }
    
    $sql = "INSERT INTO contact_messages (name, email, phone, subject, message) 
            VALUES ('$name', '$email', '$phone', '$subject', '$message')";
    
    if ($conn->query($sql)) {
        // E-posta gönder
        sendContactEmail($name, $email, $subject, $message);
        json_response(true, 'Mesajınız başarıyla gönderildi');
    } else {
        json_response(false, 'Mesaj gönderilemedi: ' . $conn->error);
    }
}

function handleList() {
    if (!is_admin()) {
        json_response(false, 'Yetkisiz erişim');
    }
    
    global $conn;
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    $result = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    
    $total_result = $conn->query("SELECT COUNT(*) as count FROM contact_messages");
    $total = $total_result->fetch_assoc()['count'];
    $total_pages = ceil($total / $limit);
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    json_response(true, 'Mesajlar listelendi', [
        'messages' => $messages,
        'page' => $page,
        'total_pages' => $total_pages,
        'total' => $total
    ]);
}

function handleMarkRead() {
    if (!is_admin()) {
        json_response(false, 'Yetkisiz erişim');
    }
    
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $message_id = (int)($data['message_id'] ?? 0);
    
    if ($message_id <= 0) {
        json_response(false, 'Geçersiz mesaj ID');
    }
    
    $conn->query("UPDATE contact_messages SET status = 'read' WHERE id = $message_id");
    json_response(true, 'Mesaj okundu işaretle');
}

function sendContactEmail($name, $email, $subject, $message) {
    $admin_email = ADMIN_EMAIL;
    
    $admin_subject = "Yeni İletişim Mesajı - $subject";
    
    $admin_message = "
    <html>
    <head>
        <title>Yeni İletişim Mesajı</title>
    </head>
    <body>
        <h2>Yeni İletişim Mesajı</h2>
        <p><strong>Ad:</strong> $name</p>
        <p><strong>E-posta:</strong> $email</p>
        <p><strong>Konu:</strong> $subject</p>
        <p><strong>Mesaj:</strong></p>
        <p>" . nl2br($message) . "</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: $email\r\n";
    
    mail($admin_email, $admin_subject, $admin_message, $headers);
    
    // Müşteriye onay e-postası gönder
    $user_subject = "İletişim Mesajınız Alındı";
    
    $user_message = "
    <html>
    <head>
        <title>İletişim Mesajı Onayı</title>
    </head>
    <body>
        <h2>İletişim Mesajınız Alındı</h2>
        <p>Sayın $name,</p>
        <p>Mesajınız başarıyla alınmıştır. En kısa sürede sizinle iletişime geçeceğiz.</p>
        <p>Teşekkür ederiz!</p>
    </body>
    </html>
    ";
    
    mail($email, $user_subject, $user_message, $headers);
}
?>
