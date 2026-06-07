<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM kiralamalar WHERE kitap_id = ? AND durum = 'active'");
        $checkStmt->execute([$input['id']]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            echo json_encode(["success" => false, "message" => "Bu kitap aktif kiralama kaydına sahip. Önce kiralama kayıtlarını silin."]);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM kitaplar WHERE id = ?");
        $stmt->execute([$input['id']]);
        
        echo json_encode(["success" => true, "message" => "Kitap başarıyla silindi"]);
    } catch(PDOException $e) {
        echo json_encode(["success" => false, "message" => "Hata: " . $e->getMessage()]);
    }
}
?>