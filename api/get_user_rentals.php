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
    $sql = "SELECT k.*, kit.baslik, kit.yazar, kit.kapak_resmi 
            FROM kiralamalar k 
            JOIN kitaplar kit ON k.kitap_id = kit.id 
            WHERE k.uye_id = ? AND k.durum = 'active'
            ORDER BY k.kiralama_tarihi DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["success" => true, "data" => $rentals]);
    
} catch(PDOException $e) {
    echo json_encode(["success" => false, "error" => "Veritabanı hatası: " . $e->getMessage()]);
}
?>