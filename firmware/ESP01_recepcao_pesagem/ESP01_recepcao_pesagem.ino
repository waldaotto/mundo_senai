/*
 * ESP-01 — Recepção, Identificação RFID e Pesagem
 * Projeto Mundo SENAI — Estação de Controle Inteligente
 *
 * Hardware:
 *   - ESP32 (DevKit)
 *   - Leitor RFID RC522 (SPI)
 *   - Célula de Carga + HX711
 *   - 2x Servomotor SG90 (braço robótico: base + garra)
 *
 * Fluxo:
 *   1. Aguarda leitura do RFID -> define destino (RJ ou SC) pelo UID do cartão.
 *   2. Move o braço robótico para colocar a caixa na balança.
 *   3. Lê o peso real via HX711.
 *   4. Envia HTTP POST (JSON) para a API PHP em api/telemetria.php.
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>
#include <HX711.h>
#include <ESP32Servo.h>

// =====================================================
//  CONFIGURAÇÕES DE REDE E API — AJUSTE AQUI
// =====================================================
const char* WIFI_SSID     = "NOME_DA_SUA_REDE";
const char* WIFI_PASSWORD = "SENHA_DA_SUA_REDE";
// Troque pelo IP/domínio do servidor onde está o projeto PHP
const char* API_URL       = "http://192.168.0.100/api/telemetria.php";

// =====================================================
//  PINOS — RFID RC522 (SPI VSPI do ESP32)
// =====================================================
#define PIN_RFID_SDA   5
#define PIN_RFID_RST   4
#define PIN_SPI_SCK    18
#define PIN_SPI_MISO   19
#define PIN_SPI_MOSI   23

// =====================================================
//  PINOS — HX711 (Célula de Carga)
// =====================================================
#define PIN_HX711_SCK   14
#define PIN_HX711_DOUT  27

// =====================================================
//  PINOS — Servomotores (Braço Robótico)
// =====================================================
#define PIN_SERVO_BASE   12
#define PIN_SERVO_GARRA  13

// =====================================================
//  PARÂMETROS DE CALIBRAÇÃO E LÓGICA
// =====================================================
// Fator de calibração do HX711 — ajuste com peso conhecido.
const float HX711_CALIB = -345.0;

const float PESO_MINIMO_G = 5.0;

const int SERVO_BASE_REPOUSO   = 0;
const int SERVO_BASE_BALANCA   = 90;
const int SERVO_GARRA_ABERTA   = 0;
const int SERVO_GARRA_FECHADA  = 70;

const unsigned long TEMPO_ESTABILIZACAO = 800;

// =====================================================
//  OBJETOS GLOBAIS
// =====================================================
MFRC522 rfid(PIN_RFID_SDA, PIN_RFID_RST);
HX711  balanca;
Servo  servoBase;
Servo  servoGarra;

enum EstadoEsp01 {
  ESP01_AGUARDANDO_RFID,
  ESP01_MOVENDO_PARA_BALANCA,
  ESP01_PESANDO,
  ESP01_MOVENDO_PARA_REPOUSO,
};

EstadoEsp01 estadoAtual = ESP01_AGUARDANDO_RFID;
String idCaixaAtual     = "";
String destinoAtual     = "AGUARDANDO";
float  pesoAtual        = 0.0;

// =====================================================
//  TABELA DE DESTINOS POR ID DE CARTÃO RFID (UID -> RJ ou SC)
//  Ajuste os UIDs conforme os cartões reais usados nas caixas.
// =====================================================
struct MapaRFID {
  String uid;
  String destino;
};

MapaRFID mapaRFID[] = {
  { "A1B2C3D4", "RJ" },
  { "E5F6A7B8", "SC" },
  { "11223344", "RJ" },
  { "99887766", "SC" },
};
const int QTDE_MAPA = sizeof(mapaRFID) / sizeof(mapaRFID[0]);

// =====================================================
//  PROTOTIPAÇÃO
// =====================================================
void conectarWiFi();
String lerUID_RFID();
String destinoPorUID(const String& uid);
void  moverBraco(int base, int garra, int tempoEspera = 400);
float lerPeso();
void  limparCaixa();
bool  enviarTelemetria(const String& idCaixa, float peso, const String& destino, const String& status);

// =====================================================
//  SETUP
// =====================================================
void setup() {
  Serial.begin(115200);
  delay(300);
  Serial.println("\n[ESP-01] Inicializando Estação de Recepção...");

  SPI.begin(PIN_SPI_SCK, PIN_SPI_MISO, PIN_SPI_MOSI, PIN_RFID_SDA);
  rfid.PCD_Init();
  delay(50);
  Serial.println("[ESP-01] RC522 inicializado.");

  balanca.begin(PIN_HX711_DOUT, PIN_HX711_SCK);
  if (!balanca.wait_ready_timeout(2000)) {
    Serial.println("[ESP-01] AVISO: HX711 não respondeu. Verifique a ligação.");
  }
  balanca.set_scale(HX711_CALIB);
  balanca.tare();
  Serial.println("[ESP-01] HX711 calibrado e tarado.");

  ESP32PWM::allocateTimer(0);
  servoBase.setPeriodHertz(50);
  servoGarra.setPeriodHertz(50);
  servoBase.attach(PIN_SERVO_BASE, 500, 2400);
  servoGarra.attach(PIN_SERVO_GARRA, 500, 2400);
  moverBraco(SERVO_BASE_REPOUSO, SERVO_GARRA_ABERTA, 600);
  Serial.println("[ESP-01] Braço em posição de repouso.");

  conectarWiFi();
  enviarTelemetria("", 0.0, "AGUARDANDO", "aguardando");
  Serial.println("[ESP-01] Pronto. Aguardando caixa no RFID...\n");
}

// =====================================================
//  LOOP — Máquina de estados
// =====================================================
void loop() {
  switch (estadoAtual) {

    case ESP01_AGUARDANDO_RFID: {
      String uid = lerUID_RFID();
      if (uid.length() > 0) {
        idCaixaAtual = "CX-" + uid.substring(0, 4);
        destinoAtual = destinoPorUID(uid);

        Serial.printf("[ESP-01] RFID lido: %s -> ID %s -> Destino %s\n",
                      uid.c_str(), idCaixaAtual.c_str(), destinoAtual.c_str());

        enviarTelemetria(idCaixaAtual, 0.0, destinoAtual, "recepcao");

        moverBraco(SERVO_BASE_REPOUSO, SERVO_GARRA_FECHADA, 500);
        estadoAtual = ESP01_MOVENDO_PARA_BALANCA;
      }
      break;
    }

    case ESP01_MOVENDO_PARA_BALANCA: {
      moverBraco(SERVO_BASE_BALANCA, SERVO_GARRA_FECHADA, 600);
      moverBraco(SERVO_BASE_BALANCA, SERVO_GARRA_ABERTA, 500);

      enviarTelemetria(idCaixaAtual, 0.0, destinoAtual, "identificacao_id");
      estadoAtual = ESP01_PESANDO;
      break;
    }

    case ESP01_PESANDO: {
      Serial.println("[ESP-01] Estabilizando leitura de peso...");
      delay(TEMPO_ESTABILIZACAO);

      pesoAtual = lerPeso();
      Serial.printf("[ESP-01] Peso lido: %.1f g\n", pesoAtual);

      if (pesoAtual < PESO_MINIMO_G) {
        Serial.println("[ESP-01] Peso abaixo do mínimo! Abortando caixa.");
        enviarTelemetria(idCaixaAtual, pesoAtual, "AGUARDANDO", "erro");
        moverBraco(SERVO_BASE_REPOUSO, SERVO_GARRA_ABERTA, 500);
        limparCaixa();
        estadoAtual = ESP01_AGUARDANDO_RFID;
        break;
      }

      enviarTelemetria(idCaixaAtual, pesoAtual, destinoAtual, "pesagem");

      moverBraco(SERVO_BASE_BALANCA, SERVO_GARRA_FECHADA, 500);
      estadoAtual = ESP01_MOVENDO_PARA_REPOUSO;
      break;
    }

    case ESP01_MOVENDO_PARA_REPOUSO: {
      moverBraco(SERVO_BASE_REPOUSO, SERVO_GARRA_FECHADA, 600);
      moverBraco(SERVO_BASE_REPOUSO, SERVO_GARRA_ABERTA, 500); // solta na esteira

      enviarTelemetria(idCaixaAtual, pesoAtual, destinoAtual, "decisao_destino");
      Serial.println("[ESP-01] Caixa entregue à esteira. Ciclo concluído.\n");

      limparCaixa();
      estadoAtual = ESP01_AGUARDANDO_RFID;
      break;
    }
  }

  if (WiFi.status() != WL_CONNECTED) {
    conectarWiFi();
  }

  delay(50);
}

// =====================================================
//  FUNÇÕES AUXILIARES
// =====================================================
void conectarWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;
  Serial.printf("[ESP-01] Conectando à rede %s", WIFI_SSID);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  int tentativas = 0;
  while (WiFi.status() != WL_CONNECTED && tentativas < 30) {
    delay(500);
    Serial.print(".");
    tentativas++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.printf("\n[ESP-01] WiFi OK! IP: %s\n", WiFi.localIP().toString().c_str());
  } else {
    Serial.println("\n[ESP-01] Falha ao conectar WiFi. Retentando no próximo ciclo.");
  }
}

String lerUID_RFID() {
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    return "";
  }
  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
  return uid;
}

String destinoPorUID(const String& uid) {
  for (int i = 0; i < QTDE_MAPA; i++) {
    if (mapaRFID[i].uid == uid) {
      return mapaRFID[i].destino;
    }
  }
  // UID não cadastrado: define RJ como padrão
  return "RJ";
}

void moverBraco(int base, int garra, int tempoEspera) {
  servoBase.write(base);
  servoGarra.write(garra);
  delay(tempoEspera);
}

float lerPeso() {
  if (!balanca.wait_ready_timeout(1500)) {
    Serial.println("[ESP-01] HX711 sem resposta na leitura.");
    return 0.0;
  }
  float soma = 0.0;
  int   validas = 0;
  for (int i = 0; i < 5; i++) {
    if (balanca.wait_ready_timeout(500)) {
      soma += balanca.get_units(1);
      validas++;
    }
    delay(20);
  }
  if (validas == 0) return 0.0;
  float peso = soma / validas;
  if (peso < 0) peso = 0.0;
  return round(peso * 10.0) / 10.0;
}

void limparCaixa() {
  idCaixaAtual = "";
  destinoAtual = "AGUARDANDO";
  pesoAtual    = 0.0;
}

bool enviarTelemetria(const String& idCaixa, float peso, const String& destino, const String& status) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[ESP-01] Sem WiFi. Dados não enviados.");
    return false;
  }

  WiFiClient client;
  HTTPClient http;
  if (!http.begin(client, API_URL)) {
    Serial.println("[ESP-01] Falha ao iniciar HTTP.");
    return false;
  }

  http.addHeader("Content-Type", "application/json");

  StaticJsonDocument<256> doc;
  doc["esp_origem"]    = "ESP-01";
  doc["id_caixa"]      = idCaixa;
  doc["peso_g"]        = round(peso * 10.0) / 10.0;
  doc["destino"]       = destino;
  doc["status"]        = status;
  doc["estacao_ativa"] = true;

  String payload;
  serializeJson(doc, payload);

  int codigo = http.POST(payload);
  String resposta = http.getString();
  http.end();

  if (codigo > 0 && codigo < 300) {
    Serial.printf("[ESP-01] POST OK (%d): %s\n", codigo, resposta.c_str());
    return true;
  }
  Serial.printf("[ESP-01] POST FALHOU (%d): %s\n", codigo, resposta.c_str());
  return false;
}
