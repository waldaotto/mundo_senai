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

const int PINO_SERVO_DIR  = 18;
const int PINO_SERVO_ESQ  = 19;
const int PINO_IR         = 23;
const int PINO_INTERTRAVA = 21;   // saída para o outro ESP (mesmo lado da placa)

// Repouso (para trás): servos espelhados, então os ângulos são opostos
const int ANG_INICIAL_DIR = 180;  // servo direito (18) para trás
const int ANG_INICIAL_ESQ = 0;    // servo esquerdo (19) para trás

// Servo esquerdo (19)
const int ANG_DESVIO  = 180;   // desvio lateral

// Servo direito (18): montado espelhado, sentidos invertidos
const int ANG_DESVIO_DIR = 0;    // desvio lateral

// ----- Passagem do meio (ID 1 / Sul) -----
// Fase 1: os dois vão para o ângulo contrário e abrem a passagem
const int ANG_ABRE_DIR = 0;      // servo direito (18) abre em 0 (para frente)
const int ANG_ABRE_ESQ = 180;    // servo esquerdo (19) abre em 180
// Fase 2: depois do tempo de abertura, só o direito volta para trás e empurra a caixa
const int ANG_EMPURRA_DIR = 180; // servo direito volta para 180 empurrando a caixa
// (o esquerdo permanece em ANG_ABRE_ESQ durante o empurrão)

// Abertura da passagem do meio (Sul)
const int PASSO_ABERTURA_GRAUS = 8;   // 8 / 20 ms = 400°/s (antes: 4 = 200°/s)
const int ANG_LIBERA_19 = 30;         // o 19 só começa quando o 18 chegar neste ângulo (ou menos)

// Velocidade dos servos: graus por passo e intervalo entre passos.
// Velocidade = PASSO / INTERVALO (1 / 20 ms = 50°/s)
const int PASSO_SERVO_GRAUS  = 1;    // velocidade normal (desvios laterais e retorno)
const int PASSO_RAPIDO_GRAUS = 4;    // movimento rápido (4 / 20 ms = 200°/s)
const int PASSO_EMPURRA_GRAUS = 4;   // velocidade do empurrão do braço direito
const int INTERVALO_SERVO_MS = 20;

const int ATRASO_ACAO_MS  = 12000;    // atraso entre sensor e braço (não vale para a passagem do meio)
const int TEMPO_DESVIO_MS = 5000;    // tempo segurando a haste na posição final (desvios laterais)
const int TEMPO_FRENTE_MS = 19000;   // tempo com os braços abertos na passagem do meio
const int TEMPO_EMPURRA_MS = 3000;   // tempo segurando o braço direito após empurrar a caixa
const int DEBOUNCE_IR_MS  = 300;

// Intertravamento da esteira (pulso para o outro ESP)
const int  PULSO_INTERTRAVA_MS    = 200;
const bool INTERTRAVAR_NO_FRENTE  = false;  // false = passagem do meio NÃO envia pulso (sem intertravamento)
const bool INTERTRAVAR_NOS_DESVIOS = true;  // false = desvios laterais também não enviam pulso
// ----------------------------------

enum Acao { ACAO_FRENTE, ACAO_DIREITA, ACAO_ESQUERDA };
enum Estado { LIVRE, AGUARDANDO, INDO, SEGURANDO, RETORNANDO };

Servo servoDir, servoEsq;
Estado estadoAtual = LIVRE;
Acao acaoAtual = ACAO_FRENTE;

int angDir = ANG_INICIAL_DIR;    // posição atual
int angEsq = ANG_INICIAL_ESQ;
int alvoDir = ANG_INICIAL_DIR;   // posição desejada
int alvoEsq = ANG_INICIAL_ESQ;
int passoAtual = PASSO_SERVO_GRAUS;

// Momento a partir do qual cada servo está liberado para se mover
unsigned long liberaDirEm = 0;
unsigned long liberaEsqEm = 0;

unsigned long tempoSegurarAtual = TEMPO_DESVIO_MS;   // muda conforme a ação
unsigned long inicioDesvio = 0;
unsigned long inicioEspera = 0;
unsigned long ultimaDeteccao = 0;
int contadorPecas = 0;
int regiaoPendente = -1;

// Controle da fase de empurrão na passagem do meio
bool empurrouFrente = false;

// Passagem do meio: o 19 fica travado até o 18 passar de ANG_LIBERA_19
bool aguardandoLiberar19 = false;

// Etapa atual do retorno ao repouso
int etapaRetorno = 0;

bool pulsoAtivo = false;
unsigned long inicioPulso = 0;

// Inicia o pulso de intertravamento (desliga sozinho em atualizarPulso)
void enviarPulsoIntertravamento() {
  digitalWrite(PINO_INTERTRAVA, HIGH);
  pulsoAtivo = true;
  inicioPulso = millis();
  Serial.println("[INTERTRAVA] Pulso enviado");
}

void atualizarPulso() {
  if (pulsoAtivo && millis() - inicioPulso >= PULSO_INTERTRAVA_MS) {
    digitalWrite(PINO_INTERTRAVA, LOW);
    pulsoAtivo = false;
  }
}

// Define o destino; o movimento acontece aos poucos em atualizarServos().
// Os dois servos ficam liberados imediatamente (quem quiser atrasar um deles
// ajusta liberaDirEm / liberaEsqEm logo depois).
void moverServos(int aDir, int aEsq) {
  alvoDir = constrain(aDir, 0, 180);
  alvoEsq = constrain(aEsq, 0, 180);
  liberaDirEm = millis();
  liberaEsqEm = millis();
  Serial.printf("[SERVO] Alvo -> Dir(18):%d Esq(19):%d\n", alvoDir, alvoEsq);
}

bool servosNoAlvo() {
  return angDir == alvoDir && angEsq == alvoEsq;
}

// Move cada servo um pouco em direção ao alvo, sem travar o loop
void atualizarServos() {
  static unsigned long ultimoPasso = 0;
  unsigned long agora = millis();
  if (agora - ultimoPasso < INTERVALO_SERVO_MS) return;
  ultimoPasso = agora;

  if (angDir != alvoDir && (long)(agora - liberaDirEm) >= 0) {
    angDir += constrain(alvoDir - angDir, -passoAtual, passoAtual);
    servoDir.write(angDir);
  }
  if (angEsq != alvoEsq && (long)(agora - liberaEsqEm) >= 0) {
    angEsq += constrain(alvoEsq - angEsq, -passoAtual, passoAtual);
    servoEsq.write(angEsq);
  }
}

// Região Norte: o 19 vai se mover depois do atraso, então o 18 sai da frente
// logo que a caixa passa no sensor, rápido, para dar espaço.
void afastarBracoDireito() {
  passoAtual = PASSO_RAPIDO_GRAUS;
  moverServos(ANG_ABRE_DIR, ANG_INICIAL_ESQ);
  Serial.println("[AFASTA] Braco direito (18) indo para frente para dar espaco ao 19");
}

// Retorno em etapas, para o 19 nunca se mover com o 18 atrás dele:
//   etapa 1: 18 vai para frente (só se o 19 precisa voltar)
//   etapa 2: 19 volta ao repouso
//   etapa 3: 18 volta ao repouso
// Devolve true quando tudo já está em repouso.
bool avancarRetorno() {
  switch (etapaRetorno) {
    case 0:
      if (angEsq != ANG_INICIAL_ESQ && angDir != ANG_ABRE_DIR) {
        passoAtual = PASSO_RAPIDO_GRAUS;
        moverServos(ANG_ABRE_DIR, angEsq);
        etapaRetorno = 1;
        return false;
      }
      // sem necessidade da etapa 1: segue para a próxima
    case 1:
      if (angEsq != ANG_INICIAL_ESQ) {
        passoAtual = PASSO_SERVO_GRAUS;
        moverServos(angDir, ANG_INICIAL_ESQ);
        etapaRetorno = 2;
        return false;
      }
      // 19 já está em repouso: segue para a próxima
    case 2:
      if (angDir != ANG_INICIAL_DIR) {
        passoAtual = PASSO_SERVO_GRAUS;
        moverServos(ANG_INICIAL_DIR, angEsq);
        etapaRetorno = 3;
        return false;
      }
    default:
      return true;
  }
}

void iniciarRetorno() {
  etapaRetorno = 0;
  estadoAtual = RETORNANDO;   // avancarRetorno() é chamado no loop
  Serial.println("[RETORNO] Voltando ao repouso (para tras)");
}

void executarAcao(Acao acao) {
  acaoAtual = acao;
  empurrouFrente = false;
  aguardandoLiberar19 = false;

  switch (acao) {
    case ACAO_DIREITA:
      // Só o 19 trabalha; o 18 já foi para a frente (ANG_ABRE_DIR) e fica lá
      tempoSegurarAtual = TEMPO_DESVIO_MS;
      passoAtual = PASSO_SERVO_GRAUS;
      moverServos(ANG_ABRE_DIR, ANG_DESVIO);
      Serial.println("[ACAO] DIREITA");
      if (INTERTRAVAR_NOS_DESVIOS) enviarPulsoIntertravamento();
      break;
    case ACAO_ESQUERDA:
      tempoSegurarAtual = TEMPO_DESVIO_MS;
      passoAtual = PASSO_SERVO_GRAUS;
      moverServos(ANG_DESVIO_DIR, ANG_INICIAL_ESQ);   // só o direito se move
      Serial.println("[ACAO] ESQUERDA");
      if (INTERTRAVAR_NOS_DESVIOS) enviarPulsoIntertravamento();
      break;
    case ACAO_FRENTE:
      tempoSegurarAtual = TEMPO_FRENTE_MS;   // segura aberto pelo tempo de abertura
      passoAtual = PASSO_ABERTURA_GRAUS;     // abre mais rápido
      moverServos(ANG_ABRE_DIR, ANG_ABRE_ESQ);
      // O 18 abre agora; o 19 fica travado até o 18 passar de ANG_LIBERA_19
      liberaEsqEm = millis() + 600000UL;
      aguardandoLiberar19 = true;
      Serial.println("[ACAO] FRENTE (18 abre agora, 19 abre quando o 18 liberar)");
      if (INTERTRAVAR_NO_FRENTE) enviarPulsoIntertravamento();
      break;
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
    executarAcao(ACAO_FRENTE);       // Sul: 18 abre, depois o 19; depois o direito empurra a caixa
  } else if (id == ID_REGIAO_NORTE) {
    executarAcao(ACAO_DIREITA);      // Norte: empurra para a direita (19)
  } else if (id == ID_REGIAO_CENTRO_OESTE) {
    executarAcao(ACAO_ESQUERDA);     // Centro-Oeste: empurra para a esquerda (18)
  } else {
    Serial.printf("[ERRO] Id de regiao desconhecido: %d (servos nao movidos)\n", id);
  }
}

void setup() {
  Serial.begin(115200);
  delay(300);

  pinMode(PINO_IR, INPUT_PULLUP);
  pinMode(PINO_INTERTRAVA, OUTPUT);
  digitalWrite(PINO_INTERTRAVA, LOW);

  servoDir.setPeriodHertz(50);
  servoEsq.setPeriodHertz(50);
  servoDir.attach(PINO_SERVO_DIR, 500, 2400);
  servoEsq.attach(PINO_SERVO_ESQ, 500, 2400);

  // Posição inicial direta (no boot a posição real dos servos é desconhecida)
  servoDir.write(ANG_INICIAL_DIR);
  servoEsq.write(ANG_INICIAL_ESQ);

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

  // Movimento gradual dos servos e fim do pulso de intertravamento
  atualizarServos();
  atualizarPulso();

  // Libera o 19 assim que o 18 já abriu o suficiente (sem esbarrar)
  if (aguardandoLiberar19 && angDir <= ANG_LIBERA_19) {
    liberaEsqEm = millis();
    aguardandoLiberar19 = false;
    Serial.println("[FRENTE] 18 livre, liberando o 19");
  }

  // Borda de descida do IR com portões livres
  if (ultimoIR == HIGH && ir == LOW && estadoAtual == LIVRE
      && (agora - ultimaDeteccao) > DEBOUNCE_IR_MS) {
    ultimaDeteccao = agora;
    contadorPecas++;
    Serial.printf("[IR] Caixa #%d detectada. Consultando regiao...\n", contadorPecas);

    int idRegiao;
    if (buscarRegiao(idRegiao)) {
      if (idRegiao == ID_REGIAO_SUL) {
        // Passagem do meio: sem atraso, abre na hora e rápido (18 primeiro, 19 depois)
        Serial.printf("[REGIAO] id = %d. Abrindo imediatamente.\n", idRegiao);
        tratarRegiao(idRegiao);
      } else {
        Serial.printf("[REGIAO] id = %d. Aguardando %d ms...\n", idRegiao, ATRASO_ACAO_MS);
        regiaoPendente = idRegiao;
        inicioEspera = millis();
        estadoAtual = AGUARDANDO;

        // Norte: o 19 vai se mover, então o 18 sai da frente já, sem esperar o atraso
        if (idRegiao == ID_REGIAO_NORTE) afastarBracoDireito();
      }
    } else {
      Serial.println("[ERRO] Nao foi possivel obter a regiao. Servos em repouso.");
    }
  }
  ultimoIR = ir;

  // 0) Passou o atraso depois do sensor: agora sim move o braço (garra)
  //    No Norte, só move o 19 depois que o 18 já chegou na frente (trava de segurança)
  if (estadoAtual == AGUARDANDO && millis() - inicioEspera >= ATRASO_ACAO_MS
      && (regiaoPendente != ID_REGIAO_NORTE || servosNoAlvo())) {
    tratarRegiao(regiaoPendente);
    // Se o id for desconhecido, tratarRegiao não muda o estado; volta a LIVRE
    if (estadoAtual == AGUARDANDO) estadoAtual = LIVRE;
  }

  // 1) Haste chegou na posição final: começa a contar o tempo de espera
  if (estadoAtual == INDO && servosNoAlvo()) {
    estadoAtual = SEGURANDO;
    inicioDesvio = millis();
    Serial.printf("[POSICAO] Haste na posicao final. Segurando por %lu ms.\n", tempoSegurarAtual);
  }

  // 2) Tempo de espera acabou
  if (estadoAtual == SEGURANDO && millis() - inicioDesvio >= tempoSegurarAtual) {

    if (acaoAtual == ACAO_FRENTE && !empurrouFrente) {
      // Passagem do meio: tempo de abertura acabou -> braço direito volta para trás
      // e empurra a caixa; o esquerdo continua aberto.
      empurrouFrente = true;
      passoAtual = PASSO_EMPURRA_GRAUS;
      tempoSegurarAtual = TEMPO_EMPURRA_MS;
      moverServos(ANG_EMPURRA_DIR, ANG_ABRE_ESQ);
      estadoAtual = INDO;   // espera chegar ao alvo e segura por TEMPO_EMPURRA_MS
      Serial.println("[EMPURRA] Braco direito voltando e empurrando a caixa");
    } else {
      // Fim normal: volta ao repouso em etapas
      iniciarRetorno();
    }
  }

  // 3) Retorno em etapas: a cada etapa concluída, avança para a próxima
  if (estadoAtual == RETORNANDO && servosNoAlvo()) {
    if (avancarRetorno()) {
      estadoAtual = LIVRE;
      empurrouFrente = false;
      Serial.println("[LIVRE] Portoes em repouso (para tras).");
    }
  }
}
