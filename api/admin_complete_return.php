<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'connect.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $rentalId = $data['rental_id'];
    
    $stmt = $conn->prepare("UPDATE kiralamalar SET durum = 'completed', gerceklesen_iade_tarihi = NOW() WHERE id = ?");
    $stmt->execute([$rentalId]);
    
    $stmt = $conn->prepare("
        UPDATE kitaplar k 
        JOIN kiralamalar r ON k.id = r.kitap_id 
        SET k.durum = 'available' 
        WHERE r.id = ?
    ");
    $stmt->execute([$rentalId]);
    
    echo json_encode(["success" => true, "message" => "İade tamamlandı"]);
} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>