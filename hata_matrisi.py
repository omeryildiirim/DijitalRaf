import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.metrics import confusion_matrix, ConfusionMatrixDisplay

# Bakkal hesabıyla kendi verilerini buraya gireceksin
# 0: Sevilmedi (0-2 puan), 1: Sevildi (3-5 puan) gibi düşün
# 'y_true' gerçek veriler, 'y_pred' sistemin tahminleri
y_true = [1, 0, 1, 1, 0, 1, 0, 0, 1, 0] # Gerçek durumlar
y_pred = [1, 0, 1, 0, 0, 1, 1, 0, 1, 0] # Sistemin tahminleri

# Matrisi hesapla
cm = confusion_matrix(y_true, y_pred)

# Görselleştir (Jüri buna bayılacak!)
fig, ax = plt.subplots(figsize=(8, 6))
disp = ConfusionMatrixDisplay(confusion_matrix=cm, display_labels=["Sevilmedi", "Sevildi"])
disp.plot(cmap=plt.cm.Blues, ax=ax)

plt.title('DijitalRaf Performans Matrisi (Hata Matrisi)')
plt.show()