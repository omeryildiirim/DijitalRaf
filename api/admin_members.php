<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'connect.php';

try {
    $sql = "SELECT u.*, 
                   (SELECT COUNT(*) FROM kiralamalar k WHERE k.email = u.email AND k.durum = 'active') as aktif_kiralama
            FROM uyeler u 
            ORDER BY u.kayit_tarihi DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($members);
} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>