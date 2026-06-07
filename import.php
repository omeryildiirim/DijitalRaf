<?php
// Veritabanı bağlantını dahil et
include 'api/connect.php'; // Bağlantı dosyan neredeyse yolunu ona göre ayarla

echo "<h2>Veri Enjeksiyonu Başlıyor...</h2>";

// CSV dosyasını aç
if (($handle = fopen("books.csv", "r")) !== FALSE) {
    // İlk satırı (Başlıkları) atla
    $header = fgetcsv($handle, 1000, ","); 
    
    $eklenen_kitap = 0;
    $rastgele_kategoriler = ['Roman', 'Bilim Kurgu', 'Tarih', 'Felsefe', 'Polisiye', 'Macera', 'Biyografi'];

    // Veritabanına ekleme sorgusunu hazırla (Güvenli PDO formatı)
    $stmt = $conn->prepare("INSERT INTO kitaplar (baslik, yazar, kategori, aciklama, kapak_resmi, stok) VALUES (?, ?, ?, ?, ?, ?)");

    // Satır satır oku
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        
        $baslik = $data[1] ?? 'Bilinmeyen Kitap';
        $yazar = $data[2] ?? 'Bilinmeyen Yazar';
        
        // Veritabanımız için sahte veriler üretiyoruz
        $kategori = $rastgele_kategoriler[array_rand($rastgele_kategoriler)];
        $aciklama = "Goodreads veri setinden yapay zeka testi için otomatik aktarıldı.";
        $kapak_resmi = "https://via.placeholder.com/150x220.png?text=Kapak+Yok"; // Hız için standart kapak
        $stok = rand(2, 15); // 2 ile 15 arası rastgele stok

        try {
            $stmt->execute([$baslik, $yazar, $kategori, $aciklama, $kapak_resmi, $stok]);
            $eklenen_kitap++;
        } catch (Exception $e) {
            // Hata olursa o satırı atla devam et
            continue;
        }

        // 500 kitaba ulaştığımızda duralım (PHP time-out yemesin)
        if ($eklenen_kitap >= 500) {
            break;
        }
    }
    fclose($handle);
    echo "<h3 style='color:green;'>Operasyon Başarılı! Toplam $eklenen_kitap kitap raflara dizildi. 🚀</h3>";
} else {
    echo "<h3 style='color:red;'>HATA: books.csv dosyası okunamadı. Klasörde olduğundan emin ol!</h3>";
}
?>