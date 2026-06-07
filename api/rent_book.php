<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

include 'connect.php';

error_log("Rent book API çağrıldı. User ID: " . $_SESSION['user_id']);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Oturum açılmamış"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    
    if (!$input || !isset($input['kitap_id'])) {
        echo json_encode(["success" => false, "error" => "Geçersiz veri"]);
        exit;
    }
    
    $kitap_id = $input['kitap_id'];
    $user_id = $_SESSION['user_id'];
    
    error_log("Kiralama işlemi - Kullanıcı: $user_id, Kitap: $kitap_id");
    
    try {
        $tableCheck = $conn->query("DESCRIBE kiralamalar");
        $columns = $tableCheck->fetchAll(PDO::FETCH_COLUMN);
        error_log("Kiralamalar tablo sütunları: " . implode(', ', $columns));
        
        $user_id_column = 'uye_id';
        
        if (in_array('kullanici_id', $columns)) {
            $user_id_column = 'kullanici_id';
        } elseif (in_array('user_id', $columns)) {
            $user_id_column = 'user_id';
        } elseif (in_array('email', $columns)) {
            $user_id_column = 'email';
            $user_id = $_SESSION['user_email'];
        }
        
        error_log("Kullanılacak kullanıcı sütunu: " . $user_id_column);
        
        $checkStmt = $conn->prepare("SELECT id, baslik, stok, durum FROM kitaplar WHERE id = ?");
        $checkStmt->execute([$kitap_id]);
        $kitap = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$kitap) {
            error_log("Kitap bulunamadı: $kitap_id");
            echo json_encode(["success" => false, "error" => "Kitap bulunamadı"]);
            exit;
        }
        
        if ($kitap['stok'] <= 0) {
            error_log("Kitap stokta yok: " . $kitap['baslik']);
            echo json_encode(["success" => false, "error" => "Kitap stokta bulunmuyor"]);
            exit;
        }
        
        $existingRental = $conn->prepare("SELECT id FROM kiralamalar WHERE $user_id_column = ? AND kitap_id = ? AND durum = 'active'");
        $existingRental->execute([$user_id, $kitap_id]);
        
        if ($existingRental->rowCount() > 0) {
            error_log("Kullanıcı zaten bu kitabı kiralamış: $user_id, $kitap_id");
            echo json_encode(["success" => false, "error" => "Bu kitabı zaten kiraladınız"]);
            exit;
        }
        
        if ($user_id_column === 'email') {
            $stmt = $conn->prepare("INSERT INTO kiralamalar (email, kitap_id, durum) VALUES (?, ?, 'active')");
        } else {
            $stmt = $conn->prepare("INSERT INTO kiralamalar ($user_id_column, kitap_id, durum) VALUES (?, ?, 'active')");
        }
        $stmt->execute([$user_id, $kitap_id]);
        
        $updateStmt = $conn->prepare("UPDATE kitaplar SET stok = stok - 1 WHERE id = ?");
        $updateStmt->execute([$kitap_id]);
        
        if ($kitap['stok'] - 1 <= 0) {
            $statusStmt = $conn->prepare("UPDATE kitaplar SET durum = 'rented' WHERE id = ?");
            $statusStmt->execute([$kitap_id]);
        }
        
        error_log("Kiralama başarılı: Kullanıcı $user_id, Kitap $kitap_id kiralandı");
        
        echo json_encode([
            "success" => true, 
            "message" => "Kitap başarıyla kiralandı! Profilinizden takip edebilirsiniz."
        ]);
        
    } catch(PDOException $e) {
        error_log("Veritabanı hatası: " . $e->getMessage());
        echo json_encode(["success" => false, "error" => "Veritabanı hatası: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Sadece POST isteği kabul edilir"]);
}
?>