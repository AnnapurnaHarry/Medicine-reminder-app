#define BLYNK_TEMPLATE_ID "TMPL3VnIkd3V5"
#define BLYNK_TEMPLATE_NAME "Temperature and Humidity Monitor Using DHT22"
#define BLYNK_AUTH_TOKEN "Oy5WOGEdQ5CokN2Z8-byFoNJwUxopLLg"

#define BLYNK_PRINT Serial

#include <WiFi.h>
#include <BlynkSimpleEsp32.h>
#include <DHT.h>

char auth[] = BLYNK_AUTH_TOKEN;

char ssid[] = "Galaxy F23 5G 3FF5";     // your wifi name
char pass[] = "edrp6836ehan";           // your wifi password

BlynkTimer timer;

#define DHTPIN 4          // DATA pin connected to GPIO4 of ESP32
#define DHTTYPE DHT22

DHT dht(DHTPIN, DHTTYPE);

void sendSensor()
{
  float h = dht.readHumidity();
  float t = dht.readTemperature();   // Celsius

  if (isnan(h) || isnan(t))
  {
    Serial.println("Failed to read from DHT sensor!");
    return;
  }

  Blynk.virtualWrite(V0, t);
  Blynk.virtualWrite(V1, h);

  Serial.print("Temperature : ");
  Serial.print(t);
  Serial.print("    Humidity : ");
  Serial.println(h);
}

void setup()
{
  Serial.begin(115200);

  Blynk.begin(auth, ssid, pass);

  dht.begin();

  timer.setInterval(100L, sendSensor);
}

void loop()
{
  Blynk.run();
  timer.run();
}
