<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'connect.php';

try {
    $sql = "SELECT 
                k.id,
                k.kitap_id,
                k.kiralama_tarihi,
                k.iade_tarihi,
                k.durum,
                uy.ad_soyad,
                kit.baslik as kitap_adi
            FROM kiralamalar k 
            LEFT JOIN uyeler uy ON k.uye_id = uy.id
            LEFT JOIN kitaplar kit ON k.kitap_id = kit.id
            ORDER BY k.kiralama_tarihi DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($rentals);
} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>