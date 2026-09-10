/*
 * ESP-02 — Esteira e Triagem (Desviador)
 * Projeto Mundo SENAI — Estação de Controle Inteligente
 *
 * Hardware:
 *   - ESP32 (DevKit)
 *   - Motor da Esteira (via Relé)
 *   - Sensor Infravermelho de presença (zona de desvio)
 *   - Servomotor Desviador ("Tapinha")
 *
 * Fluxo:
 *   1. Consulta a API (GET) esperando uma caixa com status "decisao_destino".
 *   2. Liga a esteira.
 *   3. Aguarda o sensor IR detectar a caixa na zona de desvio.
 *   4. Aciona o servo desviador para RJ ou SC.
 *   5. Envia HTTP POST atualizando contadores e liberando a estação.
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <ESP32Servo.h>

// =====================================================
//  CONFIGURAÇÕES DE REDE E API — AJUSTE AQUI
// =====================================================
const char* WIFI_SSID     = "NOME_DA_SUA_REDE";
const char* WIFI_PASSWORD = "SENHA_DA_SUA_REDE";
const char* API_URL       = "http://192.168.0.100/api/telemetria.php";

// =====================================================
//  PINOS
// =====================================================
#define PIN_RELE_ESTEIRA    26
#define PIN_IR_SENSOR       25
#define PIN_SERVO_DESVIADOR 27

// Muitos módulos IR ficam LOW quando detectam obstáculo. Inverta se necessário.
const bool IR_DETECTA_EM_LOW = true;

// =====================================================
//  PARÂMETROS DO SERVO DESVIADOR
// =====================================================
const int SERVO_DESVIO_NEUTRO = 90;
const int SERVO_DESVIO_RJ     = 45;
const int SERVO_DESVIO_SC     = 135;
const int TEMPO_DESVIO_MS     = 900;

// =====================================================
//  PARÂMETROS DE LÓGICA
// =====================================================
const unsigned long TIMEOUT_ESTEIRA_MS      = 15000;
const unsigned long INTERVALO_CONSULTA_API  = 800;

// =====================================================
//  OBJETOS GLOBAIS
// =====================================================
Servo servoDesviador;

enum EstadoEsp02 {
  ESP02_AGUARDANDO_CAIXA,
  ESP02_ESTEIRA_RODANDO,
  ESP02_DESVIANDO,
  ESP02_FINALIZANDO,
};

EstadoEsp02 estadoAtual = ESP02_AGUARDANDO_CAIXA;
String idCaixaAtual = "";
String destinoAtual = "AGUARDANDO";
float  pesoAtual    = 0.0;
unsigned long inicioEsteira = 0;

// =====================================================
//  PROTOTIPAÇÃO
// =====================================================
void conectarWiFi();
void ligarEsteira(bool ligar);
bool caixaDetectadaIR();
bool consultarDestinoAPI();
bool enviarTelemetria(const String& status);
void moverDesviador(int pos, int tempo);
void limparCaixa();

// =====================================================
//  SETUP
// =====================================================
void setup() {
  Serial.begin(115200);
  delay(300);
  Serial.println("\n[ESP-02] Inicializando Estação de Esteira e Triagem...");

  pinMode(PIN_RELE_ESTEIRA, OUTPUT);
  pinMode(PIN_IR_SENSOR, INPUT_PULLUP);
  ligarEsteira(false);

  ESP32PWM::allocateTimer(1);
  servoDesviador.setPeriodHertz(50);
  servoDesviador.attach(PIN_SERVO_DESVIADOR, 500, 2400);
  moverDesviador(SERVO_DESVIO_NEUTRO, 300);

  conectarWiFi();
  enviarTelemetria("aguardando");
  Serial.println("[ESP-02] Pronto. Aguardando caixa na esteira...\n");
}

// =====================================================
//  LOOP — Máquina de estados
// =====================================================
void loop() {
  switch (estadoAtual) {

    case ESP02_AGUARDANDO_CAIXA: {
      if (consultarDestinoAPI()) {
        if (destinoAtual == "RJ" || destinoAtual == "SC") {
          Serial.printf("[ESP-02] Caixa detectada: %s -> Destino %s\n",
                        idCaixaAtual.c_str(), destinoAtual.c_str());
          enviarTelemetria("esteira");
          estadoAtual = ESP02_ESTEIRA_RODANDO;
        }
      }
      delay(INTERVALO_CONSULTA_API);
      break;
    }

    case ESP02_ESTEIRA_RODANDO: {
      ligarEsteira(true);
      inicioEsteira = millis();
      Serial.println("[ESP-02] Esteira ligada. Aguardando sensor IR...");

      bool detectada = false;
      while (millis() - inicioEsteira < TIMEOUT_ESTEIRA_MS) {
        if (caixaDetectadaIR()) {
          detectada = true;
          break;
        }
        delay(10);
      }

      ligarEsteira(false);

      if (!detectada) {
        Serial.println("[ESP-02] Timeout! Caixa não chegou ao sensor IR.");
        enviarTelemetria("erro");
        limparCaixa();
        estadoAtual = ESP02_AGUARDANDO_CAIXA;
        break;
      }

      Serial.println("[ESP-02] Caixa na zona de desvio!");
      estadoAtual = ESP02_DESVIANDO;
      break;
    }

    case ESP02_DESVIANDO: {
      int posDesvio = (destinoAtual == "RJ") ? SERVO_DESVIO_RJ : SERVO_DESVIO_SC;
      Serial.printf("[ESP-02] Acionando desviador -> %s (pos %d)\n",
                    destinoAtual.c_str(), posDesvio);

      ligarEsteira(true);
      moverDesviador(posDesvio, TEMPO_DESVIO_MS);
      ligarEsteira(false);

      moverDesviador(SERVO_DESVIO_NEUTRO, 400);
      estadoAtual = ESP02_FINALIZANDO;
      break;
    }

    case ESP02_FINALIZANDO: {
      Serial.printf("[ESP-02] Caixa %s despachada -> %s\n",
                    idCaixaAtual.c_str(), destinoAtual.c_str());
      enviarTelemetria("desviada");

      limparCaixa();
      estadoAtual = ESP02_AGUARDANDO_CAIXA;
      Serial.println("[ESP-02] Ciclo concluído. Aguardando próxima caixa...\n");
      break;
    }
  }

  if (WiFi.status() != WL_CONNECTED) {
    conectarWiFi();
  }
}

// =====================================================
//  FUNÇÕES AUXILIARES
// =====================================================
void conectarWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;
  Serial.printf("[ESP-02] Conectando à rede %s", WIFI_SSID);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  int tentativas = 0;
  while (WiFi.status() != WL_CONNECTED && tentativas < 30) {
    delay(500);
    Serial.print(".");
    tentativas++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.printf("\n[ESP-02] WiFi OK! IP: %s\n", WiFi.localIP().toString().c_str());
  } else {
    Serial.println("\n[ESP-02] Falha ao conectar WiFi. Retentando no próximo ciclo.");
  }
}

void ligarEsteira(bool ligar) {
  digitalWrite(PIN_RELE_ESTEIRA, ligar ? HIGH : LOW);
}

bool caixaDetectadaIR() {
  int leitura = digitalRead(PIN_IR_SENSOR);
  return IR_DETECTA_EM_LOW ? (leitura == LOW) : (leitura == HIGH);
}

void moverDesviador(int pos, int tempo) {
  servoDesviador.write(pos);
  delay(tempo);
}

void limparCaixa() {
  idCaixaAtual = "";
  destinoAtual = "AGUARDANDO";
  pesoAtual    = 0.0;
}

/**
 * Consulta a API para saber se há uma caixa pronta para despacho
 * (status = decisao_destino, destino RJ ou SC).
 */
bool consultarDestinoAPI() {
  if (WiFi.status() != WL_CONNECTED) return false;

  WiFiClient client;
  HTTPClient http;
  String url = String(API_URL) + "?t=" + String(millis());
  if (!http.begin(client, url)) return false;

  http.addHeader("Accept", "application/json");
  int codigo = http.GET();

  if (codigo <= 0) {
    Serial.printf("[ESP-02] GET falhou: %s\n", http.errorToString(codigo).c_str());
    http.end();
    return false;
  }

  String resposta = http.getString();
  http.end();

  StaticJsonDocument<1024> doc;
  DeserializationError err = deserializeJson(doc, resposta);
  if (err) {
    Serial.print("[ESP-02] Erro ao parsear JSON: ");
    Serial.println(err.c_str());
    return false;
  }

  bool sucesso = doc["sucesso"] | false;
  if (!sucesso) return false;

  JsonObject dados = doc["dados"].as<JsonObject>();
  String status  = dados["status"]  | "aguardando";
  String destino = dados["destino"] | "AGUARDANDO";
  String idCaixa = dados["id_caixa"] | "";

  if (status == "decisao_destino" && (destino == "RJ" || destino == "SC") && idCaixa.length() > 0) {
    idCaixaAtual = idCaixa;
    destinoAtual = destino;
    pesoAtual    = dados["peso_g"] | 0.0;
    return true;
  }

  return false;
}

bool enviarTelemetria(const String& status) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[ESP-02] Sem WiFi. Dados não enviados.");
    return false;
  }

  WiFiClient client;
  HTTPClient http;
  if (!http.begin(client, API_URL)) return false;

  http.addHeader("Content-Type", "application/json");

  StaticJsonDocument<256> doc;
  doc["esp_origem"]    = "ESP-02";
  doc["id_caixa"]      = idCaixaAtual;
  doc["peso_g"]        = round(pesoAtual * 10.0) / 10.0;
  doc["destino"]       = destinoAtual;
  doc["status"]        = status;
  doc["estacao_ativa"] = true;

  String payload;
  serializeJson(doc, payload);

  int codigo = http.POST(payload);
  String resposta = http.getString();
  http.end();

  if (codigo > 0 && codigo < 300) {
    Serial.printf("[ESP-02] POST OK (%d): %s\n", codigo, resposta.c_str());
    return true;
  }
  Serial.printf("[ESP-02] POST FALHOU (%d): %s\n", codigo, resposta.c_str());
  return false;
}
