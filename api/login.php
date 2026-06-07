<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
session_start();

include 'connect.php';

error_log("Login attempt: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    
    error_log("Gelen JSON: " . $json);
    error_log("Decode edilmiş: " . print_r($input, true));
    
    if (!$input) {
        echo json_encode(["success" => false, "message" => "Geçersiz JSON verisi"]);
        exit;
    }
    
    $email = $input['email'];
    $sifre = $input['sifre'];
    
    error_log("Email: $email, Şifre: $sifre");
    
    if ($email === 'admin@admin.com' && $sifre === 'admin') {
        $_SESSION['user_id'] = 0;
        $_SESSION['user_name'] = 'Admin';
        $_SESSION['user_email'] = 'admin@admin.com';
        $_SESSION['is_admin'] = true;
        
        error_log("Admin girişi başarılı");
        echo json_encode([
            "success" => true, 
            "isAdmin" => true,
            "message" => "Admin girişi başarılı"
        ]);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM uyeler WHERE email = ? AND sifre = ?");
        $stmt->execute([$email, $sifre]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['ad_soyad'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = false;
            
            error_log("Kullanıcı girişi başarılı: " . $user['ad_soyad']);
            
            echo json_encode([
                "success" => true,
                "isAdmin" => false,
                "user" => [
                    "id" => $user['id'],
                    "ad_soyad" => $user['ad_soyad'],
                    "email" => $user['email']
                ],
                "message" => "Giriş başarılı"
            ]);
        } else {
            error_log("Kullanıcı bulunamadı veya şifre hatalı");
            echo json_encode([
                "success" => false,
                "message" => "E-posta veya şifre hatalı"
            ]);
        }
    } catch(PDOException $e) {
        error_log("PDO Hatası: " . $e->getMessage());
        echo json_encode([
            "success" => false,
            "message" => "Veritabanı hatası: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Sadece POST isteği kabul edilir"]);
}
?>