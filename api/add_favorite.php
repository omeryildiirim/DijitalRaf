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
        $checkStmt = $conn->prepare("SELECT id FROM favoriler WHERE uye_id = ? AND kitap_id = ?");
        $checkStmt->execute([$_SESSION['user_id'], $kitap_id]);
        
        if($checkStmt->rowCount() > 0) {
            echo json_encode(["success" => false, "error" => "Bu kitap zaten favorilerinizde"]);
        } else {
            $stmt = $conn->prepare("INSERT INTO favoriler (uye_id, kitap_id) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $kitap_id]);
            echo json_encode(["success" => true, "message" => "Kitap favorilere eklendi"]);
        }
    } catch(PDOException $e) {
        echo json_encode(["success" => false, "error" => "Veritabanı hatası: " . $e->getMessage()]);
    }
}
?>