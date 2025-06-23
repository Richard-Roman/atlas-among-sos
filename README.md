# Among SOS 🚨📡

**Among SOS** es una solución tecnológica desarrollada para mejorar la seguridad ciudadana en Tarapoto, Perú, mediante el uso de sensores acústicos IoT y un modelo de inteligencia artificial que detecta y clasifica sonidos críticos como disparos, gritos o choques, enviando alertas automáticas a una central de videovigilancia en tiempo real.

---

## 📌 Problemática

Tarapoto enfrenta frecuentes disturbios, como peleas, vandalismo y disparos, donde los reportes actuales se realizan únicamente por llamadas telefónicas, generando demoras debido a:
- Ubicaciones imprecisas.
- Miedo a represalias.
- Falta de reporte oportuno.

Esto afecta la eficacia de los agentes de seguridad y la percepción de seguridad en la ciudad.

---

## 🎯 Objetivo General

Reducir el tiempo de respuesta de los agentes de seguridad ciudadana mediante sensores IoT con detección acústica para optimizar el monitoreo en tiempo real.

---

## 🎯 Objetivos Específicos

1. Identificar zonas críticas con alta incidencia de disturbios.
2. Desplegar sensores acústicos IoT en dichas zonas.
3. Desarrollar un sistema web para monitoreo y categorización de alertas.
4. Entrenar un modelo de inteligencia artificial para clasificar sonidos y generar notificaciones automáticas.

---

## 🧠 Propuesta de Solución (Prototipo)

- Sensores acústicos IoT especializados capturan sonidos anómalos.
- El modelo de IA clasifica el tipo de sonido (disparo).
- Se envía una alerta automática a la central de videovigilancia con:
  - Tipo de evento.
  - Ubicación exacta.
  - Nivel de prioridad.
- El sistema es interoperable con las cámaras de seguridad existentes (Tarapoto: 132, Morales: 36, Banda de Shilcayo: 24).

---

## 🛠️ Tecnologías Utilizadas

- 🔊 **ESP32 + sensores de sonido**
- 🤖 **Inteligencia Artificial (modelo de audio clasificación)**
- 🌐 **Sistema Web con dashboard de alertas**
- ☁️ **Servidor con alojamiento en la nube**
- 🧩 **Interoperabilidad con cámaras de videovigilancia**

---

## 🔁 Flujo del Sistema

1. 🎙️ **Captura de audio** por sensores.
2. 🌐 **sonido enviado** al servidor.
3. 🧠 **Procesamiento IA** y clasificación del evento.
4. 🖥️ **Visualización** en dashboard en tiempo real.
5. 🚓 **Despacho de serenazgo o policía.**

---

## 💰 Recursos

| Recurso                        | Cantidad |
|-------------------------------|----------|
| ESP32                         | 2        | 
| Sensores de sonido            | 2        | 
| Servicio de alojamiento web  | 1        | 
| Desarrollo web + prototipado | 1        | 

---

## 💡 Impacto Esperado

- 🔻 Reducción del tiempo de respuesta ante emergencias.
- 📉 Disminución de actos delictivos.
- 😊 Mayor percepción de seguridad en la población.
- 🤝 Fortalecimiento de la confianza en el sistema de serenazgo.
- ⚙️ Uso eficiente de recursos humanos y tecnológicos.

---

## 👨‍💻 Equipo de Desarrollo - Estudiantes del 7° ciclo de Ingeniería de Sistemas e Informática.

- Jean Bruno Llanos Huamán
- [Miranda Shapiama Kent Axel](https://github.com/KentAxel)
- [Curay Acosta Jordy Jhorkaet]((https://github.com/Jhorka029))
- [Roman Tocto Richard Adán](https://github.com/Richard-Roman)

Asesor: Ing. Segundo Roger Ramírez Shupingahua 
---

## 📚 Referencias

- Radio Tropical (2023). *183 cámaras de seguridad en Tarapoto*. [Radio Tropical](https://radiotropical.pe/183-camaras-de-seguridad-vienen-siendo-instaladas-en-tarapoto-morales-y-la-banda-de-shilcayo)

---

## 📚 Instalación y prueba

Pasos para  ejecutar el proyecto:
Consulta el archivo de [INSTALACION](./INSTALL) para más detalles.

---

## 📌 Recomendaciones

- Realizar estudios continuos de zonas críticas.
- Mantener y actualizar sensores IoT.
- Integrar plataforma con bases de datos policiales.
- Entrenar la IA con lenguaje y acento local.

---

## 📎 Licencia

Este proyecto está licenciado bajo la Licencia MIT (MIT License).  
Puedes usarlo, modificarlo y distribuirlo libremente siempre que conserves el aviso de copyright original.

Consulta el archivo [LICENSE](./LICENSE) para más detalles.


