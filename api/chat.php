<?php

ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
session_start();

$apiKey = "AIzaSyBCL6hTZ0kEv6oCsZlcKGf0_9IdYIQ01y8"; 

$modelName = "gemini-2.5-flash";

$dbPath = __DIR__ . '/../connect.php'; 
if (file_exists($dbPath)) {
    include $dbPath;
} elseif (file_exists('connect.php')) {
    include 'connect.php';
} else {
    echo json_encode(['success' => false, 'reply' => 'Veritabanı bağlantı dosyası bulunamadı.']);
    exit;
}

$jsonInput = file_get_contents('php://input');
$input = json_decode($jsonInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'reply' => 'Veri formatı hatalı.']);
    exit;
}

$userMessage = $input['message'] ?? '';
$history = $input['history'] ?? [];

if (!$userMessage) {
    echo json_encode(['success' => false, 'reply' => 'Lütfen bir mesaj yazın.']);
    exit;
}

try {
    $userContext = "Kullanıcı: Misafir.";
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['user_name'] ?? 'Misafir';
        
        try {
            $sqlRentals = "SELECT k.kiralama_tarihi, b.baslik 
                           FROM kiralamalar k 
                           JOIN kitaplar b ON k.kitap_id = b.id 
                           WHERE k.uye_id = ? AND k.durum = 'active'";
            $stmt = $conn->prepare($sqlRentals);
            $stmt->execute([$userId]);
            $rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $rentalList = [];
            foreach($rentals as $r) $rentalList[] = $r['baslik'];
            $rentalsStr = empty($rentalList) ? "Yok" : implode(", ", $rentalList);
            
            $userContext = "Kullanıcı Adı: $userName. Şu an kiraladığı kitaplar: $rentalsStr.";
        } catch (Exception $e) {
            $userContext = "Kullanıcı Adı: $userName (Veritabanı okunamadı).";
        }
    }

    $inventory = "Kitap listesi yüklenemedi.";
    try {
        $sqlBooks = "SELECT baslik, yazar, stok, kategori FROM kitaplar LIMIT 50";
        $stmtBooks = $conn->prepare($sqlBooks);
        $stmtBooks->execute();
        $books = $stmtBooks->fetchAll(PDO::FETCH_ASSOC);

        $inventory = "KÜTÜPHANE ENVANTERİ:\n";
        foreach($books as $b) {
            $status = $b['stok'] > 0 ? "Mevcut" : "Tükendi";
            $inventory .= "- {$b['baslik']} ({$b['yazar']}, {$b['kategori']}) -> $status\n";
        }
    } catch (Exception $e) { }

    $systemInstruction = "Sen 'DijitalRaf' kütüphanesinin yardımsever asistanısın. " .
                         "Kullanıcı 'macera' gibi bir tür isterse envanterdeki 'Kategori'lere bakarak öner. " .
                         "Stokta olmayan kitaplar için nazikçe alternatif öner. " .
                         "--- BİLGİLER ---\n" . $userContext . "\n" . $inventory;

    $url = "https://generativelanguage.googleapis.com/v1beta/models/$modelName:generateContent?key=" . $apiKey;

    $cleanHistory = [];
    foreach ($history as $msg) {
        if (!empty($msg['role']) && !empty($msg['content'])) {
            $cleanHistory[] = ["role" => $msg['role'], "parts" => [["text" => strval($msg['content'])]]];
        }
    }
    $cleanHistory[] = ["role" => "user", "parts" => [["text" => $userMessage]]];

    $postData = [
        "contents" => $cleanHistory,
        "systemInstruction" => ["parts" => [["text" => $systemInstruction]]]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);

    if ($curlError) {
        echo json_encode(['success' => false, 'reply' => "Bağlantı hatası: $curlError"]);
        exit;
    }

    $responseData = json_decode($response, true);

    if ($httpCode !== 200) {
        $apiMessage = $responseData['error']['message'] ?? 'Bilinmeyen API hatası';
        echo json_encode(['success' => false, 'reply' => "Sistem hatası ($httpCode): $apiMessage"]);
        exit;
    }

    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $botReply = $responseData['candidates'][0]['content']['parts'][0]['text'];
        echo json_encode(['success' => true, 'reply' => $botReply]);
    } else {
        echo json_encode(['success' => false, 'reply' => 'Cevap üretilemedi.', 'debug' => $responseData]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'reply' => 'Sunucu hatası.', 'error_detail' => $e->getMessage()]);
}
?>