#include <WiFi.h>
#include <HTTPClient.h>
#include <ESPmDNS.h>
#include <ESP32Servo.h>
#include <ArduinoJson.h>

// ---------- CONFIGURAÇÃO ----------
const char* WIFI_SSID = "MundoSENAI";
const char* WIFI_PASS = "senai@2025";

const char* HOST_MDNS    = "serversenai.local";
const char* CAMINHO_API  = "/mundo_senai/api/regiao";
const char* URL_REGIAO   = "http://serversenai.local/mundo_senai/api/regiao";
const int   TIMEOUT_HTTP_MS = 3000;

const int ID_REGIAO_SUL          = 1;
const int ID_REGIAO_NORTE        = 2;
const int ID_REGIAO_CENTRO_OESTE = 3;

const char* JSON_CHAVE_REGIAO = "regiao_id";

const int PINO_SERVO_DIR = 18;
const int PINO_SERVO_ESQ = 19;
const int PINO_IR        = 23;

const int ANG_INICIAL = 90;    // repouso / retorno

// Servo esquerdo (19)
const int ANG_DESVIO  = 180;   // desvio lateral
const int ANG_TRAS    = 0;     // haste para trás (frente livre)

// Servo direito (18): montado espelhado, sentidos invertidos
const int ANG_DESVIO_DIR = 0;    // desvio lateral
const int ANG_TRAS_DIR   = 180;  // haste para trás (frente livre)

// Velocidade dos servos: graus por passo e intervalo entre passos.
// Mais lento: diminua PASSO_SERVO_GRAUS ou aumente INTERVALO_SERVO_MS.
// Velocidade = PASSO_SERVO_GRAUS / INTERVALO_SERVO_MS (1 / 20 ms = 50°/s)
const int PASSO_SERVO_GRAUS  = 1;
const int INTERVALO_SERVO_MS = 20;

const int TEMPO_DESVIO_MS = 5000;   // tempo segurando a haste na posição final
const int DEBOUNCE_IR_MS  = 300;
// ----------------------------------

enum Acao { ACAO_FRENTE, ACAO_DIREITA, ACAO_ESQUERDA };
enum Estado { LIVRE, INDO, SEGURANDO, RETORNANDO };

Servo servoDir, servoEsq;
Estado estadoAtual = LIVRE;

int angDir = ANG_INICIAL;    // posição atual
int angEsq = ANG_INICIAL;
int alvoDir = ANG_INICIAL;   // posição desejada
int alvoEsq = ANG_INICIAL;

unsigned long inicioDesvio = 0;
unsigned long ultimaDeteccao = 0;
int contadorPecas = 0;

// Define o destino; o movimento acontece aos poucos em atualizarServos()
void moverServos(int aDir, int aEsq) {
  alvoDir = constrain(aDir, 0, 180);
  alvoEsq = constrain(aEsq, 0, 180);
  Serial.printf("[SERVO] Alvo -> Dir(18):%d Esq(19):%d\n", alvoDir, alvoEsq);
}

bool servosNoAlvo() {
  return angDir == alvoDir && angEsq == alvoEsq;
}

// Move cada servo um pouco em direção ao alvo, sem travar o loop
void atualizarServos() {
  static unsigned long ultimoPasso = 0;
  if (millis() - ultimoPasso < INTERVALO_SERVO_MS) return;
  ultimoPasso = millis();

  if (angDir != alvoDir) {
    angDir += constrain(alvoDir - angDir, -PASSO_SERVO_GRAUS, PASSO_SERVO_GRAUS);
    servoDir.write(angDir);
  }
  if (angEsq != alvoEsq) {
    angEsq += constrain(alvoEsq - angEsq, -PASSO_SERVO_GRAUS, PASSO_SERVO_GRAUS);
    servoEsq.write(angEsq);
  }
}

void irParaInicial() {
  moverServos(ANG_INICIAL, ANG_INICIAL);
}

void executarAcao(Acao acao) {
  switch (acao) {
    case ACAO_DIREITA:  moverServos(ANG_INICIAL,     ANG_DESVIO);  Serial.println("[ACAO] DIREITA");  break;
    case ACAO_ESQUERDA: moverServos(ANG_DESVIO_DIR,  ANG_INICIAL); Serial.println("[ACAO] ESQUERDA"); break;
    case ACAO_FRENTE:   moverServos(ANG_TRAS_DIR,    ANG_TRAS);    Serial.println("[ACAO] FRENTE");   break;
  }
  estadoAtual = INDO;
}

// Faz o GET e devolve o id da região. Retorna false se falhar.
bool requisitarRegiao(const String& url, int& idRegiao) {
  HTTPClient http;
  http.setTimeout(TIMEOUT_HTTP_MS);
  if (!http.begin(url)) return false;

  int code = http.GET();
  if (code != HTTP_CODE_OK) {
    Serial.printf("[HTTP] Falha (%d) em %s\n", code, url.c_str());
    http.end();
    return false;
  }

  String body = http.getString();
  http.end();
  body.trim();
  Serial.printf("[HTTP] Resposta: %s\n", body.c_str());

  StaticJsonDocument<512> doc;
  DeserializationError err = deserializeJson(doc, body);

  if (!err && doc.is<JsonObject>()) {
    if (doc[JSON_CHAVE_REGIAO].isNull()) {
      Serial.printf("[ERRO] Chave '%s' nao encontrada no JSON\n", JSON_CHAVE_REGIAO);
      return false;
    }
    idRegiao = doc[JSON_CHAVE_REGIAO].as<int>();
    return true;
  }
  if (!err && doc.is<int>()) {
    idRegiao = doc.as<int>();
    return true;
  }

  Serial.println("[ERRO] Resposta em formato nao reconhecido");
  return false;
}

bool buscarRegiao(int& idRegiao) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WIFI] Desconectado, tentando reconectar");
    WiFi.reconnect();
    return false;
  }

  if (requisitarRegiao(URL_REGIAO, idRegiao)) return true;

  // Plano B: resolve o .local por mDNS e tenta pelo IP
  IPAddress ip = MDNS.queryHost(HOST_MDNS, 2000);
  if (ip == IPAddress((uint32_t)0)) {
    Serial.println("[mDNS] Nao foi possivel resolver o servidor");
    return false;
  }
  String urlIp = "http://" + ip.toString() + CAMINHO_API;
  return requisitarRegiao(urlIp, idRegiao);
}

void tratarRegiao(int id) {
  if (id == ID_REGIAO_SUL) {
    executarAcao(ACAO_FRENTE);       // Sul: ambos liberam, caixa passa reto
  } else if (id == ID_REGIAO_NORTE) {
    executarAcao(ACAO_DIREITA);      // Norte: empurra para a direita
  } else if (id == ID_REGIAO_CENTRO_OESTE) {
    executarAcao(ACAO_ESQUERDA);     // Centro-Oeste: empurra para a esquerda
  } else {
    Serial.printf("[ERRO] Id de regiao desconhecido: %d (servos nao movidos)\n", id);
  }
}

void setup() {
  Serial.begin(115200);
  delay(300);

  pinMode(PINO_IR, INPUT_PULLUP);

  servoDir.setPeriodHertz(50);
  servoEsq.setPeriodHertz(50);
  servoDir.attach(PINO_SERVO_DIR, 500, 2400);
  servoEsq.attach(PINO_SERVO_ESQ, 500, 2400);

  // Posição inicial direta (no boot a posição real dos servos é desconhecida)
  servoDir.write(ANG_INICIAL);
  servoEsq.write(ANG_INICIAL);

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  Serial.print("Conectando ao WiFi");
  unsigned long t0 = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - t0 < 20000) {
    delay(500); Serial.print(".");
  }
  Serial.println();
  if (WiFi.status() == WL_CONNECTED) {
    Serial.print("IP: "); Serial.println(WiFi.localIP());
    MDNS.begin("esp32-portoes");
  } else {
    Serial.println("Falha no WiFi.");
  }
}

void loop() {
  static int ultimoIR = HIGH;
  int ir = digitalRead(PINO_IR);
  unsigned long agora = millis();

  // Movimento gradual dos servos
  atualizarServos();

  // Borda de descida do IR com portões livres
  if (ultimoIR == HIGH && ir == LOW && estadoAtual == LIVRE
      && (agora - ultimaDeteccao) > DEBOUNCE_IR_MS) {
    ultimaDeteccao = agora;
    contadorPecas++;
    Serial.printf("[IR] Caixa #%d detectada. Consultando regiao...\n", contadorPecas);

    int idRegiao;
    if (buscarRegiao(idRegiao)) {
      Serial.printf("[REGIAO] id = %d\n", idRegiao);
      tratarRegiao(idRegiao);
    } else {
      Serial.println("[ERRO] Nao foi possivel obter a regiao. Servos em repouso.");
    }
  }
  ultimoIR = ir;

  // 1) Haste chegou na posição final: começa a contar o tempo de espera
  if (estadoAtual == INDO && servosNoAlvo()) {
    estadoAtual = SEGURANDO;
    inicioDesvio = millis();
    Serial.println("[POSICAO] Haste na posicao final.");
  }

  // 2) Tempo de espera acabou: volta devagar ao repouso
  if (estadoAtual == SEGURANDO && millis() - inicioDesvio >= TEMPO_DESVIO_MS) {
    irParaInicial();
    estadoAtual = RETORNANDO;
    Serial.println("[RETORNO] Voltando ao inicial (90/90)");
  }

  // 3) Chegou ao repouso: libera para a próxima caixa
  if (estadoAtual == RETORNANDO && servosNoAlvo()) {
    estadoAtual = LIVRE;
    Serial.println("[LIVRE] Portoes em repouso (90/90).");
  }
}