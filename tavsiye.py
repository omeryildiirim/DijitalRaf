import sys
import json
import pandas as pd
import mysql.connector
from sklearn.neighbors import NearestNeighbors
import warnings
warnings.filterwarnings('ignore')

try:
    # Web sitesinden (PHP'den) gelen kitap ID'sini alıyoruz
    hedef_kitap_id = int(sys.argv[1]) if len(sys.argv) > 1 else 2

    # Veritabanına Bağlan
    db = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="dijitalraf"
    )

    # Verileri Çek
    df_puanlar = pd.read_sql("SELECT uye_id, kitap_id, puan FROM degerlendirmeler", con=db)
    df_kitaplar = pd.read_sql("SELECT id, baslik, kapak_resmi FROM kitaplar", con=db)

    # 🛑 KRİTİK DÜZELTME: pivot_table ve aggfunc='mean' ile çift puanları ortalıyoruz
    matris = df_puanlar.pivot_table(index='kitap_id', columns='uye_id', values='puan', aggfunc='mean').fillna(0)
    
    # K-NN Modelini Eğit
    model_knn = NearestNeighbors(metric='cosine', algorithm='brute', n_neighbors=6)
    model_knn.fit(matris)

    oneriler = []
    
    # Eğer kitap matriste varsa komşularını bul
    if hedef_kitap_id in matris.index:
        mesafeler, indeksler = model_knn.kneighbors(matris.loc[hedef_kitap_id].values.reshape(1, -1), n_neighbors=6)
        
        for i in range(1, len(mesafeler.flatten())):
            komsu_id = int(matris.index[indeksler.flatten()[i]])
            komsu_satir = df_kitaplar[df_kitaplar['id'] == komsu_id]
            
            # Veritabanında eşleşen kitap varsa listeye ekle
            if not komsu_satir.empty:
                komsu_bilgi = komsu_satir.iloc[0]
                benzerlik = round((1 - mesafeler.flatten()[i]) * 100, 1)
                
                # Bulunan sonuçları web için paketle
                oneriler.append({
                    "id": komsu_id,
                    "baslik": komsu_bilgi['baslik'],
                    "kapak_resmi": komsu_bilgi['kapak_resmi'],
                    "benzerlik": benzerlik
                })

    # PHP'nin okuyabilmesi için SADECE JSON formatında yazdır 
    print(json.dumps(oneriler))

except Exception as e:
    # Hata olursa web sitesi çökmesin diye hatayı JSON olarak yolla
    print(json.dumps({"hata": str(e)}))