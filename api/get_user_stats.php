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
    $stmt = $conn->prepare("SELECT COUNT(*) as okunan_kitap FROM kiralamalar WHERE uye_id = ? AND durum = 'completed'");
    $stmt->execute([$_SESSION['user_id']]);
    $okunan_kitap = $stmt->fetch(PDO::FETCH_ASSOC)['okunan_kitap'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as aktif_kiralama FROM kiralamalar WHERE uye_id = ? AND durum = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    $aktif_kiralama = $stmt->fetch(PDO::FETCH_ASSOC)['aktif_kiralama'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as favori_sayisi FROM favoriler WHERE uye_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $favori_sayisi = $stmt->fetch(PDO::FETCH_ASSOC)['favori_sayisi'];
    
    $cevrilen_sayfa = $okunan_kitap * 250;
    $okuma_saati = $okunan_kitap * 8;
    
    echo json_encode([
        "success" => true,
        "okunan_kitap" => $okunan_kitap,
        "aktif_kiralama" => $aktif_kiralama,
        "favori_sayisi" => $favori_sayisi,
        "cevrilen_sayfa" => $cevrilen_sayfa,
        "okuma_saati" => $okuma_saati
    ]);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "error" => "Veritabanı hatası: " . $e->getMessage()]);
}
?>