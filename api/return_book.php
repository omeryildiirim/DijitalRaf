<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Oturum açılmamış"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $kiralama_id = $input['kiralama_id'];
    
    try {
        $stmt = $conn->prepare("UPDATE kiralamalar SET durum = 'completed', iade_tarihi = NOW() WHERE id = ? AND uye_id = ?");
        $stmt->execute([$kiralama_id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            $update_kitap = $conn->prepare("
                UPDATE kitaplar k 
                JOIN kiralamalar kr ON k.id = kr.kitap_id 
                SET k.stok = k.stok + 1, k.durum = 'available' 
                WHERE kr.id = ?
            ");
            $update_kitap->execute([$kiralama_id]);
            
            echo json_encode(["success" => true, "message" => "Kitap başarıyla iade edildi"]);
        } else {
            echo json_encode(["success" => false, "error" => "Kiralama bulunamadı"]);
        }
    } catch(PDOException $e) {
        echo json_encode(["success" => false, "error" => "Veritabanı hatası: " . $e->getMessage()]);
    }
}
?>