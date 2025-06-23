# 🧪 Instalación y prueba

Pasos para ejecutar correctamente el proyecto:

## 1️⃣ Configurar la base de datos

1. Crea una base de datos MySQL (por ejemplo: `amoung_sos`).
2. Crea un usuario con permisos para esa base de datos (ejemplo: usuario `amoung`, contraseña `amoung123456789`).
3. Importa el archivo [`amoung_sos.sql`](./Base%20de%20datos/amoung_sos.sql) desde phpMyAdmin o desde la terminal.

---

## 2️⃣ Configurar el servidor Python (FastAPI)

1. Asegúrate de tener **Python 3.9+** instalado.
2. Crea y activa un entorno virtual:

```bash
python -m venv env
source env/bin/activate   # Linux/Mac
env\Scripts\activate.bat  # Windows
```

3. Instala las dependencias necesarias:

```bash
pip install -r requirements.txt
```
Si no tienes `requirements.txt`, instala estas librerías:
```bash
pip fastapi uvicorn mysql-connector-python pydantic bcrypt librosa numpy soundfile tensorflow starlette
```

4. Verifica y edita la configuración de conexión en `app.py` (línea `db_config`) con tus credenciales de base de datos:

```python
db_config = {
    "host": "127.0.0.1",
    "user": "TU_USUARIO",
    "password": "TU_CONTRASEÑA",
    "database": "NOMBRE_BD",
    "port": 3306
}
```

5. Coloca el archivo del modelo entrenado `detector_disparos.h5` en el mismo directorio de `app.py`.

6. Ejecuta el servidor con:

```bash
uvicorn app:app --reload
```
Esto iniciará el backend en `http://127.0.0.1:8000`.

Tambien puedes escoger un puerto con:

```bash
uvicorn app:app --reload --host 0.0.0.0 --port 8080
```
Esto iniciará el backend en `http://127.0.0.1:8080`.

---

### 3️⃣ Ejecutar el sistema web (frontend PHP)

1. Coloca los archivos PHP dentro de la carpeta [fontend](./frontend) en:
   - `htdocs/` si usas **XAMPP** (Windows).
   - `/var/www/html/` si usas **Apache** en Linux.
   - O súbelos a la carpeta `public_html` si usas un servidor web externo.

2. Accede a través de tu navegador:  
   `http://localhost/index.php`
---

### 4️⃣ Comunicación con el ESP32 (IoT)

1. Abre el archivo `rebote.ino` y súbelo al **ESP32** usando el **Arduino IDE**.
2. Este programa permite capturar audio a través de micrófono y enviarlo vía **serial USB**.
3. Configura el archivo  [atlas.py](./Arduino/atlas.py) para que apunte a tu backend.

```python
ws_url = "ws://tu_server.com:puerto/ws/audio"
```
4. El archivo `atlas.py` (en tu PC) debe ejecutarse para establecer comunicación serial y guardar el audio recibido desde el ESP32 como archivo `.wav`:

```bash
python atlas.py
```
---

### 🔁 Comunicación con el backend (prototipo)

Debido a las restricciones de tiempo en la competencia, se implementó una solución donde el audio se transmite por **puerto serial**, en lugar de enviarlo directamente desde el ESP32 vía WiFi al servidor.

**Mejora:** El ESP32 deberia usar su módulo WiFi para comunicarse directamente con el servidor mediante HTTP/WebSocket.

---

### ✅ Prueba del sistema

1. Ejecuta `atlas.py` para capturar audio desde el ESP32.
2. El archivo guardado se analiza con el backend Python (`app.py`), que fragmenta, predice y genera alertas si detecta sonidos críticos.
3. Revisa la consola del servidor y el sistema web para validar las alertas registradas.

---
