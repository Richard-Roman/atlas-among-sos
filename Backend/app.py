import mysql.connector

from fastapi import FastAPI, HTTPException, WebSocket
from pydantic import BaseModel
from fastapi.responses import JSONResponse
from datetime import date, time
import os, datetime
import bcrypt
from typing import Optional
import librosa
import numpy as np
import soundfile as sf
from starlette.websockets import WebSocketDisconnect


from tensorflow.keras.models import load_model

# Cargar el modelo al iniciar el servidor
modelo = load_model("detector_disparos.h5")

os.makedirs("audios_recibidos", exist_ok=True)
os.makedirs("audio_chunks", exist_ok=True)


app = FastAPI()

# 📦 Datos de conexión MySQL (ajusta según tu caso)
db_config = {
    "host": "127.0.0.1",
    "user": "amoung",
    "password": "XION7612",
    "database": "amoung_sos",
    "port": 3306
}


# 📥 Modelos Pydantic
class Camara(BaseModel):
    id_camara: int
    codigo: str
    ubicacion: str

class CamaraInput(BaseModel):
    codigo: str
    ubicacion: str

class DispositivoIoT(BaseModel):
    id_dispositivo: int
    nombre: str
    ubicacion: str

class DispositivoIoTInput(BaseModel):
    nombre: str
    ubicacion: str

class DispositivoCamara(BaseModel):
    id_dispositivo: int
    id_camara: int

class Alerta(BaseModel):
    id_alerta: int
    id_dispositivo: int
    fecha: date
    hora: time
    tipo_alerta: str

class AlertaInput(BaseModel):
    id_dispositivo: int
    fecha: date
    hora: time
    tipo_alerta: str

class UsuarioInput(BaseModel):
    username: str
    password: Optional[str] = None
    nombre: str
    estado: int



# 📥 Modelo para el login
class LoginInput(BaseModel):
    username: str
    password: str


@app.get("/usuarios")
def listar_usuarios():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT idUsuario, username, nombre, estado FROM usuarios")
        resultado = cursor.fetchall()
        cursor.close()
        conn.close()
        return resultado
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/usuarios/{id}")
def ver_usuario(id: int):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT idUsuario, username, nombre, estado FROM usuarios WHERE idUsuario = %s", (id,))
        resultado = cursor.fetchone()
        cursor.close()
        conn.close()
        if not resultado:
            raise HTTPException(status_code=404, detail="Usuario no encontrado")
        return resultado
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.put("/usuarios/{id}")
def editar_usuario(id: int, data: UsuarioInput):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()

        if data.password:
            # Si envía nueva contraseña → hashearla
            password_hash = bcrypt.hashpw(data.password.encode(), bcrypt.gensalt()).decode()
        else:
            # Recuperar la contraseña actual
            cursor.execute("SELECT password FROM usuarios WHERE idUsuario = %s", (id,))
            result = cursor.fetchone()
            if not result:
                raise HTTPException(status_code=404, detail="Usuario no encontrado")
            password_hash = result[0]

        actualizar = """
            UPDATE usuarios
            SET username = %s, password = %s, nombre = %s, estado = %s
            WHERE idUsuario = %s
        """
        cursor.execute(actualizar, (data.username, password_hash, data.nombre, data.estado, id))
        conn.commit()
        cursor.close()
        conn.close()

        return {"message": "✅ Usuario actualizado correctamente"}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))




@app.get("/ping")
def ping():
    return {"message": "✅ FastAPI está funcionando correctamente"}

@app.post("/login")
def login(data: LoginInput):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)

        query = "SELECT * FROM usuarios WHERE username = %s AND estado = 1"
        cursor.execute(query, (data.username,))
        user = cursor.fetchone()

        cursor.close()
        conn.close()

        if user and bcrypt.checkpw(data.password.encode(), user["password"].encode()):
            # 🔑 Login válido
            return {
                "id": user["idUsuario"],
                "username": user["username"],
                "nombre": user["nombre"],
                "token": "token_simulado"
            }
        else:
            raise HTTPException(status_code=401, detail="Credenciales inválidas")

    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error de conexión: {str(e)}")


class UsuarioInput(BaseModel):
    username: str
    password: str
    nombre: str


@app.post("/singup")
def crear_usuario(datos: UsuarioInput):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()

        # Verifica si el username ya existe
        cursor.execute("SELECT 1 FROM usuarios WHERE username = %s", (datos.username,))
        if cursor.fetchone():
            raise HTTPException(status_code=409, detail="❌ El usuario ya existe")

        # Hashea la contraseña
        password_hash = bcrypt.hashpw(datos.password.encode(), bcrypt.gensalt()).decode()

        # Inserta el nuevo usuario
        insertar = """
            INSERT INTO usuarios (username, password, nombre, estado)
            VALUES (%s, %s, %s, 1)
        """
        cursor.execute(insertar, (datos.username, password_hash, datos.nombre))
        conn.commit()

        cursor.close()
        conn.close()

        return {"message": "✅ Usuario creado correctamente"}

    except mysql.connector.Error as e:
        raise HTTPException(status_code=500, detail=f"Error MySQL: {str(e)}")

    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error inesperado: {str(e)}")



@app.post("/logout")
def logout():
    # Si usas JWT en el futuro, aquí puedes revocar el token
    return JSONResponse(
        content={"message": "👋 Sesión cerrada correctamente"},
        status_code=200
    )



# 🔁 Endpoints para CAMARAS
@app.get("/camaras")
def listar_camaras():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM camaras")
        resultado = cursor.fetchall()
        cursor.close()
        conn.close()
        return resultado
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/camaras/{id}")
def ver_camara(id: int):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM camaras WHERE id_camara = %s", (id,))
        resultado = cursor.fetchone()
        cursor.close()
        conn.close()
        if not resultado:
            raise HTTPException(status_code=404, detail="Camara no encontrada")
        return resultado
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))



@app.get("/camaras/dispositivo/{id_dispositivo}")
def camaras_por_dispositivo(id_dispositivo: int):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT c.* FROM camaras c JOIN dispositivosCamaras dc ON c.id_camara = dc.id_camara WHERE dc.id_dispositivo = %s", (id_dispositivo,))
        resultado = cursor.fetchall()
        cursor.close()
        conn.close()
        return resultado
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/camaras")
def registrar_camara(data: CamaraInput):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        cursor.execute("INSERT INTO camaras (codigo, ubicacion) VALUES (%s, %s)", (data.codigo, data.ubicacion))
        conn.commit()
        cursor.close()
        conn.close()
        return {"message": "✅ Cámara registrada"}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.put("/camaras/{id}")
def editar_camara(id: int, data: CamaraInput):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        cursor.execute("UPDATE camaras SET codigo=%s, ubicacion=%s WHERE id_camara=%s", (data.codigo, data.ubicacion, id))
        conn.commit()
        cursor.close()
        conn.close()
        return {"message": "✅ Cámara actualizada"}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.delete("/camaras/{id}")
def eliminar_camara(id: int):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        cursor.execute("DELETE FROM camaras WHERE id_camara=%s", (id,))
        conn.commit()
        cursor.close()
        conn.close()
        return {"message": "🗑️ Cámara eliminada"}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# 🔁 Endpoints para DISPOSITIVOS
@app.get("/dispositivos")
def listar_dispositivos():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM dispositivos_iot")
        resultado = cursor.fetchall()
        cursor.close()
        conn.close()
        return resultado
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
        
@app.get("/dispositivos/{id}")
def ver_dispositivo(id: int):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM dispositivos_iot WHERE id_dispositivo = %s", (id,))
        resultado = cursor.fetchone()
        cursor.close()
        conn.close()

        if not resultado:
            raise HTTPException(status_code=404, detail="Dispositivo no encontrado")
        return resultado

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


###########################3        

@app.post("/dispositivos")
def registrar_dispositivo(data: DispositivoIoTInput):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        cursor.execute("INSERT INTO dispositivos_iot (nombre, ubicacion) VALUES (%s, %s)", (data.nombre, data.ubicacion))
        conn.commit()
        cursor.close()
        conn.close()
        return {"message": "✅ Dispositivo registrado"}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
        

@app.put("/dispositivos/{id}")
def editar_dispositivo(id: int, data: DispositivoIoTInput):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        cursor.execute("UPDATE dispositivos_iot SET nombre=%s, ubicacion=%s WHERE id_dispositivo=%s", (data.nombre, data.ubicacion, id))
        conn.commit()
        cursor.close()
        conn.close()
        return {"message": "✅ Dispositivo actualizado"}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
        

@app.delete("/dispositivos/{id}")
def eliminar_dispositivo(id: int):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        cursor.execute("DELETE FROM dispositivos_iot WHERE id_dispositivo=%s", (id,))
        conn.commit()
        cursor.close()
        conn.close()
        return {"message": "🗑️ Dispositivo eliminado"}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


def convertir_segundos_a_hora(segundos):
    horas = int(segundos // 3600)
    minutos = int((segundos % 3600) // 60)
    segundos_restantes = int(segundos % 60)
    return time(horas, minutos, segundos_restantes)

def normalizar_hora(hora_str):
    partes = hora_str.split(":")
    if len(partes) == 3:
        h, m, s = partes
        return f"{int(h):02}:{int(m):02}:{int(s):02}"
    return hora_str  # si ya está bien

@app.get("/alertas")
def listar_alertas():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM alertas")
        resultado = cursor.fetchall()
        for fila in resultado:
            hora_val = fila["hora"]
            if isinstance(hora_val, (float, int)):
                hora_obj = convertir_segundos_a_hora(hora_val)
            elif isinstance(hora_val, time):
                hora_obj = hora_val
            else:
                hora_obj = time.fromisoformat(normalizar_hora(str(hora_val)))  # 🛠 fix aquí
            fila["hora"] = hora_obj.isoformat()
        cursor.close()
        conn.close()
        return resultado
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/alertas/dispositivo/{id_dispositivo}")
def alertas_por_dispositivo(id_dispositivo: int):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM alertas WHERE id_dispositivo = %s", (id_dispositivo,))
        resultado = cursor.fetchall()
        cursor.close()
        conn.close()
        return resultado
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/alertas")
def registrar_alerta(data: AlertaInput):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        cursor.execute("INSERT INTO alertas (id_dispositivo, fecha, hora, tipo_alerta) VALUES (%s, %s, %s, %s)", 
                       (data.id_dispositivo, data.fecha, data.hora, data.tipo_alerta))
        conn.commit()
        cursor.close()
        conn.close()
        return {"message": "🚨 Alerta registrada"}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.delete("/alertas/{id}")
def eliminar_alerta(id: int):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        cursor.execute("DELETE FROM alertas WHERE id_alerta = %s", (id,))
        conn.commit()
        cursor.close()
        conn.close()
        return {"message": "🗑️ Alerta eliminada"}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

        
# 🔗 Modelo de relación
class DispositivoCamaraInput(BaseModel):
    id_dispositivo: int
    id_camara: int


# 📥 Relacionar cámara con dispositivo
@app.post("/dispositivo-camara")
def relacionar_dispositivo_camara(data: DispositivoCamaraInput):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        cursor.execute("INSERT INTO dispositivosCamaras (id_dispositivo, id_camara) VALUES (%s, %s)", 
                       (data.id_dispositivo, data.id_camara))
        conn.commit()
        cursor.close()
        conn.close()
        return {"message": "🔗 Relación creada correctamente"}
    except mysql.connector.IntegrityError as e:
        raise HTTPException(status_code=409, detail="❌ La relación ya existe o hay claves inválidas")
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# 🔍 Ver todas las relaciones
@app.get("/dispositivo-camara")
def obtener_relaciones():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT * FROM dispositivosCamaras")
        data = cursor.fetchall()
        cursor.close()
        conn.close()
        return data
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/dispositivo-camaras/{id_dispositivo}")
def obtener_camaras_por_dispositivo(id_dispositivo: int):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)

        query = """
            SELECT c.id_camara, c.codigo, c.ubicacion
            FROM dispositivosCamaras dc
            JOIN camaras c ON dc.id_camara = c.id_camara
            WHERE dc.id_dispositivo = %s
        """

        # 👇 Corrige el paso del parámetro: debe ser una tupla con coma
        cursor.execute(query, (id_dispositivo,))
        data = cursor.fetchall()

        cursor.close()
        conn.close()
        return data

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


# ❌ Eliminar una relación
@app.delete("/dispositivo-camara")
def eliminar_relacion(data: DispositivoCamaraInput):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        cursor.execute("DELETE FROM dispositivosCamaras WHERE id_dispositivo = %s AND id_camara = %s", 
                       (data.id_dispositivo, data.id_camara))
        conn.commit()
        cursor.close()
        conn.close()
        return {"message": "🗑️ Relación eliminada"}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# lectura de audio

def predict_chunk(path, n_mfcc=40):
    audio, sr = librosa.load(path, sr=16000)

    # MFCC
    mfcc = librosa.feature.mfcc(y=audio, sr=sr, n_mfcc=n_mfcc)
    mfcc_mean = np.mean(mfcc.T, axis=0)

    # Otros features
    zcr = librosa.feature.zero_crossing_rate(y=audio)[0].mean()
    rms = librosa.feature.rms(y=audio)[0].mean()
    spectral_centroid = librosa.feature.spectral_centroid(y=audio, sr=sr)[0].mean()

    # Vector final de entrada
    feature_vector = np.hstack([mfcc_mean, zcr, rms, spectral_centroid])  # → (43,)
    input_tensor = feature_vector.reshape(1, -1, 1)  # → (1, 43, 1)

    prediction = modelo.predict(input_tensor, verbose=0)
    return prediction[0][1]  # probabilidad clase "disparo"


def fragmentar_audio(audio_path, output_dir="audio_chunks", sr_target=16000, chunk_sec=2):
    y, sr = librosa.load(audio_path, sr=sr_target)
    os.makedirs(output_dir, exist_ok=True)
    chunk_duration = chunk_sec * sr  # muestras por fragmento
    chunk_paths = []
    
    for i in range(0, len(y), chunk_duration):
        chunk = y[i:i+chunk_duration]
        if len(chunk) < chunk_duration:
            break  # opcional: puedes guardar igual si deseas
        chunk_file = os.path.join(output_dir, f"chunk_{i//chunk_duration}.wav")
        sf.write(chunk_file, chunk, sr)
        chunk_paths.append(chunk_file)

    return chunk_paths

#@app.websocket("/ws/audio")
#async def stream_audio(websocket: WebSocket):
#    await websocket.accept()
#    filename = datetime.datetime.now().strftime("audio_%Y%m%d_%H%M%S.wav")
#    audio_path = f"audios_recibidos/{filename}"

#    try:
#        with open(audio_path, "wb") as f:
#            while True:
#                data = await websocket.receive_bytes()
#                f.write(data)

#    except WebSocketDisconnect:
#        print(f"🔁 Análisis iniciado para: {audio_path}")
#        try:
#            chunks = fragmentar_audio(audio_path)
#            for chunk in chunks:
#                prob = predict_chunk(chunk)
#                if prob > 0.75:
#                    print(f"🚨 DISPARO detectado en {chunk} con {prob*100:.1f}%")
#                else:
#                    print(f"✅ Fragmento limpio: {chunk} ({prob*100:.1f}%)")
#        except Exception as e:
#            print(f"❌ Error procesando fragmentos: {e}")

#    except Exception as e:
#        print(f"❌ Error durante el WebSocket: {e}")



@app.websocket("/ws/audio")
async def stream_audio(websocket: WebSocket):
    await websocket.accept()
    filename = datetime.datetime.now().strftime("audio_%Y%m%d_%H%M%S.wav")
    audio_path = f"audios_recibidos/{filename}"

    try:
        with open(audio_path, "wb") as f:
            while True:
                data = await websocket.receive_bytes()
                f.write(data)

    except WebSocketDisconnect:
        print(f"🔁 Análisis iniciado para: {audio_path}")
        try:
            cuenta_altas = 0 
            chunks = fragmentar_audio(audio_path)
            probabilidades = []

            for chunk in chunks:
                prob = predict_chunk(chunk)
                if prob > 0.65:
                    print(f"🚨 Disparo detectado en ({prob*100:.1f}%)")
                    cuenta_altas += 1
                else:
                    print(f"✔️ Limpio: ({prob*100:.1f}%)")
                    probabilidades.append(prob)

                # Calcular promedio de las probabilidades
            promedio = sum(probabilidades) / len(probabilidades) if probabilidades else 0
            print(f"📊 Promedio de predicción: {promedio*100:.2f}%")

            # Evaluar condiciones para registrar alerta
            if promedio > 0.65 or cuenta_altas >= 3:
                try:
                    conn = mysql.connector.connect(**db_config)
                    cursor = conn.cursor()
                    now = datetime.datetime.now()
                    fecha = now.date().isoformat()
                    hora = now.time().isoformat(timespec='seconds')
                    tipo_alerta = "disparo"
                    id_dispositivo = 1  # puedes reemplazarlo dinámicamente si es necesario

                    cursor.execute("""
                        INSERT INTO alertas (id_dispositivo, fecha, hora, tipo_alerta)
                        VALUES (%s, %s, %s, %s)
                        """, (id_dispositivo, fecha, hora, tipo_alerta))

                    conn.commit()
                    cursor.close()
                    conn.close()
                    print("🚨 Alerta registrada correctamente")
                except Exception as db_err:
                    print(f"❌ Error registrando alerta: {db_err}")
            else:
                print("✅ Condiciones no cumplidas, no se registra alerta.")

        except Exception as e:
            print(f"❌ Error procesando fragmentos: {e}")

    except Exception as e:
        print(f"❌ Error durante el WebSocket: {e}")
