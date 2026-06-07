<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        $rentalStmt = $conn->prepare("SELECT kitap_id FROM kiralamalar WHERE id = ?");
        $rentalStmt->execute([$input['rental_id']]);
        $rental = $rentalStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$rental) {
            echo json_encode(["success" => false, "message" => "Kiralama kaydı bulunamadı"]);
            exit;
        }
        
        $updateRentalStmt = $conn->prepare("UPDATE kiralamalar SET durum = 'returned' WHERE id = ?");
        $updateRentalStmt->execute([$input['rental_id']]);
        
        $updateBookStmt = $conn->prepare("UPDATE kitaplar SET stok = stok + 1, durum = 'available' WHERE id = ?");
        $updateBookStmt->execute([$rental['kitap_id']]);
        
        echo json_encode(["success" => true, "message" => "İade işlemi başarıyla tamamlandı"]);
    } catch(PDOException $e) {
        echo json_encode(["success" => false, "message" => "Hata: " . $e->getMessage()]);
    }
}
?>