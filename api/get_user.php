<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Oturum açılmamış"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM uyeler WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode([
            "success" => true,
            "ad_soyad" => $user['ad_soyad'],
            "email" => $user['email'],
            "telefon" => $user['telefon'] ?? '',
            "bakiye" => $user['bakiye'] ?? 0.00 
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Kullanıcı bulunamadı"]);
    }
} catch(PDOException $e) {
    echo json_encode(["success" => false, "error" => "Veritabanı hatası: " . $e->getMessage()]);
}
?>