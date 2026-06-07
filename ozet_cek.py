import mysql.connector
import requests
import time

print("--- GOOGLE BOOKS API DEVREDE (HATA TESPİT MODU) ---")

try:
    db = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="dijitalraf"
    )
    cursor = db.cursor(dictionary=True)

    # Test için şimdilik sadece 10 kitap çekiyoruz
    sorgu = "SELECT id, baslik, yazar FROM kitaplar WHERE aciklama LIKE '%henüz sisteme işlenmemiştir%' LIMIT 10"
    cursor.execute(sorgu)
    kitaplar = cursor.fetchall()

    if len(kitaplar) == 0:
        print("Kral, güncellenecek kitap kalmadı!")
        exit()

    print(f"Toplam {len(kitaplar)} kitap için Google'a bağlanılıyor...\n")

    for kitap in kitaplar:
        kitap_id = kitap['id']
        orijinal_baslik = kitap['baslik']
        yazar = kitap['yazar']
        
        # Parantezleri temizle
        temiz_baslik = orijinal_baslik.split('(')[0].strip()
        
        print(f"Aranıyor: {temiz_baslik} ({yazar})...")
        
        url = "https://www.googleapis.com/books/v1/volumes"
        # 🛑 KRİTİK DOKUNUŞ: Sadece başlık değil, YAZAR adını da ekledik ki tam isabet olsun
        parametreler = {"q": f"intitle:{temiz_baslik} inauthor:{yazar}"}
        
        cevap = requests.get(url, params=parametreler).json()
        aciklama_bulundu = False

        # 🛑 EĞER GOOGLE BİZE KOTA HATASI (ERROR) VERİYORSA YAKALAYALIM:
        if 'error' in cevap:
            print(f"  🚨 GOOGLE BİZİ ENGELLEDİ: {cevap['error'].get('message', 'Bilinmeyen Hata')}")
            print("  Bot korumasına takıldık! İşlem durduruluyor...")
            break # Döngüyü tamamen kırıp çıkıyoruz
        
        if 'items' in cevap:
            for item in cevap['items'][:3]:
                if 'volumeInfo' in item and 'description' in item['volumeInfo']:
                    yeni_aciklama = item['volumeInfo']['description']
                    
                    guncelle_sorgu = "UPDATE kitaplar SET aciklama = %s WHERE id = %s"
                    cursor.execute(guncelle_sorgu, (yeni_aciklama, kitap_id))
                    db.commit()
                    
                    print("  [✓] Gerçek özet bulundu ve işlendi!")
                    aciklama_bulundu = True
                    break 
        
        if not aciklama_bulundu:
            print("  [X] Google bu kitap için özet vermedi.")
            
        # Engellenmemek için bekleme süresini 2 saniyeye çıkardık
        time.sleep(2)

    print("\nTest bitti!")

except Exception as e:
    print(f"Bir hata oluştu: {e}")