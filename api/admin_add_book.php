<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $conn->prepare("INSERT INTO kitaplar (baslik, yazar, kategori, aciklama, stok, kapak_resmi, durum) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $input['baslik'],
            $input['yazar'],
            $input['kategori'],
            $input['aciklama'],
            $input['stok'],
            $input['kapak_resmi'],
            $input['stok'] > 0 ? 'available' : 'rented'
        ]);
        
        echo json_encode(["success" => true, "message" => "Kitap başarıyla eklendi"]);
    } catch(PDOException $e) {
        echo json_encode(["success" => false, "message" => "Hata: " . $e->getMessage()]);
    }
}
?>