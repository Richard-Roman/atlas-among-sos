import serial
import wave
import numpy as np
import time
from datetime import datetime
from websocket import create_connection
from scipy.signal import butter, lfilter

# ⚙️ Configuración
puerto = 'COM7'           # Cambia esto según tu sistema operativo
baudrate = 921600
frecuencia_muestreo = 16000
duracion_segundos = 15
total_muestras = frecuencia_muestreo * duracion_segundos
bytes_esperados = total_muestras * 2  # 16-bit PCM (2 bytes por muestra)
ws_url = "ws://tu_server.com:puerto/ws/audio"

# 🎛️ Filtros y normalización
def filtro_pasabajas(data, cutoff=7000, fs=16000, orden=5):
    nyq = 0.5 * fs
    normal_cutoff = cutoff / nyq
    b, a = butter(orden, normal_cutoff, btype='low', analog=False)
    return lfilter(b, a, data)

def filtro_pasaaltas(data, cutoff=100, fs=16000, orden=3):
    nyq = 0.5 * fs
    normal_cutoff = cutoff / nyq
    b, a = butter(orden, normal_cutoff, btype='high', analog=False)
    return lfilter(b, a, data)

def normalizar_audio(audio):
    max_val = np.max(np.abs(audio))
    return audio if max_val == 0 else audio * (32767 / max_val)

# 🚀 INICIO SERIAL
ser = serial.Serial(puerto, baudrate, timeout=1)
print("🎙️ Esperando 'INICIO_DATOS'...")

while True:
    try:
        linea = ser.readline().decode('utf-8', errors='ignore').strip()
        if "INICIO_DATOS" in linea:
            print("Grabación iniciada")
            break
    except:
        pass

# ⏱ Recolectar audio
raw_bytes = bytearray()
tiempo_inicio = time.time()
while len(raw_bytes) < bytes_esperados and (time.time() - tiempo_inicio) < 20:
    raw_bytes += ser.read(bytes_esperados - len(raw_bytes))

# Verificar fin
try:
    fin = ser.readline().decode('utf-8', errors='ignore').strip()
except:
    fin = ""

if len(raw_bytes) < bytes_esperados:
    print("⚠️ No se recibieron suficientes datos. Grabación fallida.")
    exit()

print("Fin de grabación" if "FIN_DATOS" in fin else "⚠️ FIN_DATOS no detectado")

# 🎧 Convertir a PCM
muestras = []
for i in range(0, len(raw_bytes), 2):
    if i + 1 < len(raw_bytes):
        low = raw_bytes[i]
        high = raw_bytes[i + 1]
        valor = (high << 8) | low
        muestras.append(valor - 2048)

audio_np = np.array(muestras, dtype=np.int16)

# 🎛️ Filtros + Normalización
audio_filtrado = filtro_pasabajas(audio_np)
audio_filtrado = filtro_pasaaltas(audio_filtrado)
audio_filtrado = normalizar_audio(audio_filtrado)

# 💾 Guardar WAV
timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
nombre_archivo = f"audio_{timestamp}.wav"
with wave.open(nombre_archivo, 'w') as wav:
    wav.setnchannels(1)
    wav.setsampwidth(2)
    wav.setframerate(frecuencia_muestreo)
    wav.writeframes(audio_filtrado.astype(np.int16).tobytes())

print(f"Audio guardado como: {nombre_archivo}")

# 🌐 Enviar por WebSocket
try:
    print("📤 Enviando audio por WebSocket...")
    ws = create_connection(ws_url)
    with open(nombre_archivo, "rb") as f:
        ws.send_binary(f.read())
    print("Audio enviado correctamente")
    ws.close()
except Exception as e:
    print(f"Error al enviar por WebSocket: {e}")
