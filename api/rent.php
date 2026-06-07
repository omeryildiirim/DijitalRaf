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
    $kitap_id = $input['kitap_id'];
    
    try {
        $checkStmt = $conn->prepare("SELECT stok, durum FROM kitaplar WHERE id = ?");
        $checkStmt->execute([$kitap_id]);
        $kitap = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$kitap) {
            echo json_encode(["success" => false, "error" => "Kitap bulunamadı"]);
            exit;
        }
        
        if ($kitap['stok'] <= 0) {
            echo json_encode(["success" => false, "error" => "Kitap stokta bulunmuyor"]);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO kiralamalar (uye_id, kitap_id, durum) VALUES (?, ?, 'active')");
        $stmt->execute([$_SESSION['user_id'], $kitap_id]);
        
        $updateStmt = $conn->prepare("UPDATE kitaplar SET stok = stok - 1 WHERE id = ?");
        $updateStmt->execute([$kitap_id]);
        
        if ($kitap['stok'] - 1 <= 0) {
            $statusStmt = $conn->prepare("UPDATE kitaplar SET durum = 'rented' WHERE id = ?");
            $statusStmt->execute([$kitap_id]);
        }
        
        echo json_encode(["success" => true, "message" => "Kitap başarıyla kiralandı"]);
        
    } catch(PDOException $e) {
        echo json_encode(["success" => false, "error" => "Veritabanı hatası: " . $e->getMessage()]);
    }
}
?>