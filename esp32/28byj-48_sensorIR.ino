// ---------- Pinos ----------
// MOTOR DE PASSO COM INTERTRAVAMENTO
const int IN1 = 19;
const int IN2 = 18;
const int IN3 = 5;
const int IN4 = 17;
const int PIN_SENSOR = 27;
const int PIN_START  = 14;   // botão para GND
const int PIN_LED    = 2;    // LED onboard

// ---------- Configuração ----------
const bool SENSOR_ATIVO_BAIXO = true;     // módulo LM393: LOW = obstáculo
const uint32_t INTERVALO_PASSO_US = 2100;  // 500 Hz
const uint32_t DEBOUNCE_SENSOR_MS = 30;
const int DIRECAO = 1;                    // 1 ou -1 (inverte o sentido)

// Sequência de meio-passo
const uint8_t SEQ[8][4] = {
  {1,0,0,0}, {1,1,0,0}, {0,1,0,0}, {0,1,1,0},
  {0,0,1,0}, {0,0,1,1}, {0,0,0,1}, {1,0,0,1}
};

enum Estado { PARADA, RODANDO, INTERTRAVADA };
Estado estado = PARADA;

int indicePasso = 0;
uint32_t ultimoPassoUs = 0;

// ---------- Motor ----------
void aplicarPasso(int i) {
  digitalWrite(IN1, SEQ[i][0]);
  digitalWrite(IN2, SEQ[i][1]);
  digitalWrite(IN3, SEQ[i][2]);
  digitalWrite(IN4, SEQ[i][3]);
}

void desligarBobinas() {
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, LOW);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, LOW);
}

void passoNaoBloqueante() {
  uint32_t agora = micros();
  if (agora - ultimoPassoUs >= INTERVALO_PASSO_US) {
    ultimoPassoUs = agora;
    indicePasso = (indicePasso + DIRECAO + 8) % 8;
    aplicarPasso(indicePasso);
  }
}

// ---------- Sensor com debounce ----------
bool caixaNoPontoX() {
  static bool estavel = false;
  static bool ultimaLeitura = false;
  static uint32_t tMudanca = 0;

  bool leitura = (digitalRead(PIN_SENSOR) == (SENSOR_ATIVO_BAIXO ? LOW : HIGH));
  if (leitura != ultimaLeitura) {
    ultimaLeitura = leitura;
    tMudanca = millis();
  }
  if (millis() - tMudanca >= DEBOUNCE_SENSOR_MS) {
    estavel = leitura;
  }
  return estavel;
}

// ---------- Botão ----------
bool botaoPressionado() {
  static bool anterior = false;
  static uint32_t t = 0;
  bool atual = (digitalRead(PIN_START) == LOW);
  bool pulso = false;
  if (atual && !anterior && millis() - t > 50) {
    pulso = true;
    t = millis();
  }
  anterior = atual;
  return pulso;
}

void setup() {
  Serial.begin(115200);
  delay(1500);
  Serial.println("Iniciando...");
  pinMode(IN1, OUTPUT);
  pinMode(IN2, OUTPUT);
  pinMode(IN3, OUTPUT);
  pinMode(IN4, OUTPUT);
  pinMode(PIN_SENSOR, INPUT);
  pinMode(PIN_LED, OUTPUT);
  desligarBobinas();
}

void loop() {
  bool caixa = caixaNoPontoX();
  bool start = botaoPressionado();

  switch (estado) {
    case PARADA:
      if (start && !caixa) {
        estado = RODANDO;
        Serial.println("RODANDO");
      }
      break;

    case RODANDO:
      if (caixa) {                 // intertravamento
        estado = INTERTRAVADA;
        desligarBobinas();
        Serial.println("INTERTRAVADA: caixa no ponto X");
      } else {
        passoNaoBloqueante();
      }
      break;

    case INTERTRAVADA:
      if (!caixa) {                // caixa retirada, libera novo ciclo
        estado = PARADA;
        Serial.println("Ponto X livre. Aguardando start.");
      }
      break;
  }

  digitalWrite(PIN_LED, estado == INTERTRAVADA);
}