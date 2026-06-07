<?php
// Hataları gizle ve SADECE JSON formatında çıktı ver (Sitenin tasarımı okuyabilsin diye)
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

$kitap_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Python dosyasının yolunu bul
$script_yolu = realpath(__DIR__ . '/../tavsiye.py');

// Python'u temiz bir şekilde çalıştır
$komut = escapeshellcmd("python \"$script_yolu\" $kitap_id");
$cevap = shell_exec($komut);

// Sadece yapay zekadan gelen saf veriyi ekrana bas
echo $cevap;
?>