<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

include 'connect.php';

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "logged_in" => true,
        "user" => [
            "id" => $_SESSION['user_id'],
            "ad_soyad" => $_SESSION['user_name'],
            "email" => $_SESSION['user_email']
        ]
    ]);
} else {
    echo json_encode(["logged_in" => false]);
}
?>