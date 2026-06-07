<?php
ini_set('max_execution_time', 300); 
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'api/connect.php'; 

echo "<h2>Gerçek Kaggle Puanları Yükleniyor...</h2>";

if (($handle = fopen("ratings.csv", "r")) !== FALSE) {
    $header = fgetcsv($handle, 1000, ","); 
    
    $eklenen_puan = 0;
    $stmt = $conn->prepare("INSERT INTO degerlendirmeler (uye_id, kitap_id, puan) VALUES (?, ?, ?)");

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Eğer satırda yeterli veri yoksa atla
        if (!isset($data[0], $data[1], $data[2])) continue;

        $user_id = $data[0];
        $book_id = $data[1];
        $rating = $data[2];

        // Sadece ilk 500 kitabı alıyoruz
        if (!is_numeric($book_id) || $book_id > 500) {
            continue;
        }

        try {
            $stmt->execute([$user_id, $book_id, $rating]);
            $eklenen_puan++;
        } catch (Exception $e) {
            // HATA VARSA SESSİZCE GEÇME, EKRANA BAS VE DUR!
            die("<h3 style='color:red;'>🛑 VERİTABANI HATASI: " . $e->getMessage() . "</h3><p>Hata veren satır: User=$user_id, Book=$book_id, Rating=$rating</p>");
        }

        if ($eklenen_puan >= 5000) {
            break;
        }
    }
    fclose($handle);
    echo "<h3 style='color:green;'>Başarılı! Kaggle'dan $eklenen_puan adet GERÇEK kullanıcı değerlendirmesi çekildi. 🚀</h3>";
} else {
    echo "<h3 style='color:red;'>🛑 HATA: ratings.csv dosyası okunamadı. Klasörde olduğundan emin ol!</h3>";
}
?>