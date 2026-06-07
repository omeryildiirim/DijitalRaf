<?php
include 'api/connect.php'; 

echo "<h2>Kapak Resimleri Çekiliyor... Lütfen Bekleyin...</h2>";

if (($handle = fopen("books.csv", "r")) !== FALSE) {
    $header = fgetcsv($handle, 10000, ","); 
    
    // CSV'deki 'image_url' (resim linki) sütununu bul
    $image_col = array_search('image_url', $header);
    
    if ($image_col === false) {
        die("<h3 style='color:red;'>Hata: CSV dosyasında resim sütunu bulunamadı.</h3>");
    }

    $guncellenen = 0;
    $id = 1; // Veritabanındaki kitap ID'lerimiz 1'den başlıyor

    $stmt = $conn->prepare("UPDATE kitaplar SET kapak_resmi = ? WHERE id = ?");

    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
        $image_url = $data[$image_col];

        try {
            // Kitabın ID'sine göre kapağı güncelle
            $stmt->execute([$image_url, $id]);
            $guncellenen++;
        } catch (Exception $e) { }

        $id++;
        if ($id > 500) break; // Sadece bizim yüklediğimiz 500 kitabın kapağını güncelle
    }
    fclose($handle);
    echo "<h3 style='color:green;'>Şov Başlasın! $guncellenen kitabın gerçek kapak resmi vitrine yerleştirildi. 🎨🚀</h3>";
}
?>