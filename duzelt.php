<?php
include 'api/connect.php'; 

echo "<h2>Kapak ve İsim Karmaşası Çözülüyor...</h2>";

if (($handle = fopen("books.csv", "r")) !== FALSE) {
    // İlk satırı (başlıkları) oku
    $header = fgetcsv($handle, 10000, ","); 
    
    // Hangi verinin hangi sütunda olduğunu otomatik bul (Hata payını sıfırlar)
    $title_col = array_search('title', $header);
    $author_col = array_search('authors', $header);
    $image_col = array_search('image_url', $header);

    if ($title_col === false || $author_col === false || $image_col === false) {
        die("<h3 style='color:red;'>Hata: CSV dosyasında gerekli sütunlar bulunamadı!</h3>");
    }

    $stmt = $conn->prepare("UPDATE kitaplar SET baslik = ?, yazar = ?, kapak_resmi = ? WHERE id = ?");
    
    $id = 1;
    $duzeltilen = 0;
    
    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
        $baslik = $data[$title_col];
        $yazar = $data[$author_col];
        $kapak = $data[$image_col];

        try {
            // Kitabın ID'sine göre adını, yazarını ve kapağını TAMAMEN güncelle
            $stmt->execute([$baslik, $yazar, $kapak, $id]);
            $duzeltilen++;
        } catch (Exception $e) { }

        $id++;
        if ($id > 500) break; // Bizim sistemdeki 500 kitabı eşleştirip dur
    }
    fclose($handle);
    echo "<h3 style='color:green;'>Operasyon Tamam! $duzeltilen kitabın adı, yazarı ve kapağı birbiriyle kusursuz eşleştirildi. 🚀</h3>";
} else {
    echo "<h3 style='color:red;'>Hata: books.csv dosyası okunamadı.</h3>";
}
?>