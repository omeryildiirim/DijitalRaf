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
    $sql = "SELECT k.* FROM kitaplar k 
            INNER JOIN favoriler f ON k.id = f.kitap_id 
            WHERE f.uye_id = ? 
            ORDER BY f.eklenme_tarihi DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["success" => true, "data" => $favorites]);
} catch(PDOException $e) {
    echo json_encode(["success" => true, "data" => []]);
}
?>