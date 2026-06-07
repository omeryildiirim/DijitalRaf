<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'connect.php';

try {
    $stmt = $conn->prepare("SELECT * FROM kitaplar ORDER BY id DESC");
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($books);
} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>