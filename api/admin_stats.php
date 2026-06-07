<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'connect.php';

try {
    $stats = [];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM kitaplar");
    $stmt->execute();
    $stats['total_books'] = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM kiralamalar WHERE durum = 'active'");
    $stmt->execute();
    $stats['active_rentals'] = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM kiralamalar WHERE durum = 'late'");
    $stmt->execute();
    $stats['late_returns'] = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM uyeler");
    $stmt->execute();
    $stats['total_members'] = $stmt->fetch()['total'];
    
    echo json_encode($stats);
} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>