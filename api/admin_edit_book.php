<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !isset($input['baslik']) || !isset($input['yazar']) || !isset($input['kategori']) || !isset($input['stok'])) {
        echo json_encode(["success" => false, "message" => "Eksik bilgi gönderildi"]);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("UPDATE kitaplar SET 
            baslik = ?, 
            yazar = ?, 
            kategori = ?, 
            stok = ?, 
            durum = ?,
            aciklama = ?,
            kapak_resmi = ?
            WHERE id = ?");
        
        $stmt->execute([
            $input['baslik'],
            $input['yazar'],
            $input['kategori'],
            $input['stok'],
            $input['stok'] > 0 ? 'available' : 'rented',
            $input['aciklama'] ?? null, 
            $input['kapak_resmi'] ?? null, 
            $input['id']
        ]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "success" => true, 
                "message" => "Kitap başarıyla güncellendi",
                "updated_data" => [
                    "id" => $input['id'],
                    "baslik" => $input['baslik'],
                    "yazar" => $input['yazar'],
                    "kategori" => $input['kategori'],
                    "stok" => $input['stok']
                ]
            ]);
        } else {
            echo json_encode([
                "success" => false, 
                "message" => "Kitap bulunamadı veya değişiklik yapılmadı"
            ]);
        }
        
    } catch(PDOException $e) {
        error_log("Kitap güncelleme hatası: " . $e->getMessage());
        echo json_encode([
            "success" => false, 
            "message" => "Veritabanı hatası: " . $e->getMessage(),
            "error_code" => $e->getCode()
        ]);
    }
} else {
    echo json_encode([
        "success" => false, 
        "message" => "Geçersiz istek metodu. Sadece POST kabul edilir."
    ]);
}

$conn = null;
?>