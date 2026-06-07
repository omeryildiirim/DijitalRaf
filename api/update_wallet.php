<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
session_start();

include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Oturum açılmamış"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $amount = floatval($input['amount']);
    
    try {
        $stmt = $conn->prepare("SELECT bakiye FROM uyeler WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $current_balance = $stmt->fetch(PDO::FETCH_ASSOC)['bakiye'];
        
        $new_balance = $current_balance + $amount;
        
        $update_stmt = $conn->prepare("UPDATE uyeler SET bakiye = ? WHERE id = ?");
        $update_stmt->execute([$new_balance, $_SESSION['user_id']]);
        
        echo json_encode([
            "success" => true, 
            "new_balance" => $new_balance,
            "message" => "Bakiye başarıyla güncellendi"
        ]);
    } catch(PDOException $e) {
        echo json_encode(["success" => false, "error" => "Veritabanı hatası: " . $e->getMessage()]);
    }
}
?>