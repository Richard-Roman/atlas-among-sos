import serial
import wave
import numpy as np
from datetime import datetime

# ⚙️ Configuración
puerto = 'COM7'           # Cambia esto según tu sistema operativo
baudrate = 921600
frecuencia_muestreo = 8000
duracion_segundos = 15
total_muestras = frecuencia_muestreo * duracion_segundos

# 🔌 Abrir puerto serial
ser = serial.Serial(puerto, baudrate, timeout=15)
print(f"🎙️ Escuchando en {puerto}...")

# Esperar señal de inicio
while True:
    linea = ser.readline().decode('utf-8', errors='ignore').strip()
    if "INICIO_DATOS" in linea:
        print("🔴 Grabación iniciada")
        break

# Leer datos binarios (2 bytes por muestra)
raw_bytes = ser.read(total_muestras * 2)
print("🟢 Datos binarios recibidos")

# Esperar señal de fin (opcional)
try:
    fin = ser.readline().decode('utf-8', errors='ignore').strip()
    if "FIN_DATOS" in fin:
        print("✅ Señal de fin recibida")
except:
    pass

# 🧮 Convertir bytes a enteros centrados
muestras = []
for i in range(0, len(raw_bytes), 2):
    if i+1 < len(raw_bytes):
        low = raw_bytes[i]
        high = raw_bytes[i+1]
        valor = (high << 8) | low
        centrado = valor - 2048
        muestras.append(centrado)

audio_np = np.array(muestras, dtype=np.int16)

# 💾 Guardar como archivo WAV
timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
nombre_archivo = f"audio_{timestamp}.wav"
with wave.open(nombre_archivo, 'w') as wav:
    wav.setnchannels(1)
    wav.setsampwidth(2)
    wav.setframerate(frecuencia_muestreo)
    wav.writeframes(audio_np.tobytes())

print(f"✅ Audio guardado como {nombre_archivo}")
