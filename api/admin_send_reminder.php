<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'connect.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $rentalId = $data['rental_id'];
        
    echo json_encode(["success" => true, "message" => "Hatırlatma gönderildi"]);
} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>