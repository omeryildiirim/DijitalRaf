<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    
    error_log("Register attempt: " . print_r($input, true));
    
    if (!$input) {
        echo json_encode(["success" => false, "message" => "Geçersiz JSON verisi"]);
        exit;
    }
    
    $ad_soyad = $input['ad_soyad'] ?? '';
    $email = $input['email'] ?? '';
    $sifre = $input['sifre'] ?? '';
    
    if (empty($ad_soyad) || empty($email) || empty($sifre)) {
        echo json_encode(["success" => false, "message" => "Tüm zorunlu alanları doldurun"]);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Geçersiz e-posta adresi"]);
        exit;
    }
    
    try {
        $checkStmt = $conn->prepare("SELECT id FROM uyeler WHERE email = ?");
        $checkStmt->execute([$email]);
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode(["success" => false, "message" => "Bu e-posta adresi zaten kayıtlı"]);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO uyeler (ad_soyad, email, sifre, bakiye) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $ad_soyad,
            $email,
            $sifre, 
            0.00 
        ]);
        
        $user_id = $conn->lastInsertId();
        
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $ad_soyad;
        $_SESSION['user_email'] = $email;
        $_SESSION['is_admin'] = false;
        
        error_log("Yeni kullanıcı kaydedildi: " . $ad_soyad . " - " . $email);
        
        echo json_encode([
            "success" => true,
            "message" => "Kayıt başarılı! Hoş geldiniz.",
            "user" => [
                "id" => $user_id,
                "ad_soyad" => $ad_soyad,
                "email" => $email
            ]
        ]);
        
    } catch(PDOException $e) {
        error_log("Kayıt hatası: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Kayıt sırasında bir hata oluştu: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Sadece POST isteği kabul edilir"]);
}
?>