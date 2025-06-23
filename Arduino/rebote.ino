// ESP32 + MAX4466 - Grabación activada por sonido fuerte (15 segundos)
const int micPin = 34;
const unsigned long duracionGrabacion = 15000; // milisegundos
const unsigned int frecuenciaMuestreo = 8000;
const unsigned int periodoMuestreo = 1000000UL / frecuenciaMuestreo;
const unsigned int totalMuestras = duracionGrabacion * frecuenciaMuestreo / 1000;

const int umbralRuido = 3250; // Ajusta este valor según el entorno real
const int tiempoEspera = 50;  // Milisegundos entre chequeos

void setup() {
  Serial.begin(921600); // Velocidad alta para transmisión binaria
  delay(2000);
  Serial.println("⏳ Esperando sonido fuerte...");
}

void loop() {
  int valor = analogRead(micPin);
  if (valor > umbralRuido) {
    Serial.println("INICIO_DATOS");
    unsigned long tiempoInicio = micros();

    for (unsigned int i = 0; i < totalMuestras; i++) {
      int muestra = analogRead(micPin);
      uint16_t val = (uint16_t)muestra;

      byte lowByte = val & 0xFF;
      byte highByte = (val >> 8) & 0xFF;

      Serial.write(lowByte);
      Serial.write(highByte);

      while (micros() - tiempoInicio < (unsigned long)(i + 1) * periodoMuestreo);
    }

    Serial.println("FIN_DATOS");
    delay(1000); // Pausa antes de volver a escuchar
    Serial.println("⏳ Esperando sonido fuerte...");
  }

  delay(tiempoEspera); // Controla la frecuencia de escaneo
}
