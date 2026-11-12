// ---------- Pinos ----------
const int IN1 = 5;
const int IN2 = 17;
const int IN3 = 16;
const int IN4 = 4;

const int PINO_INTERTRAVA = 18;   // entrada vinda do outro ESP (mesmo lado da placa)

// ---------- Configuração ----------
const uint32_t INTERVALO_PASSO_US = 2100;  // intervalo entre passos (~476 Hz)
const int DIRECAO = 1;                     // 1 ou -1 (inverte o sentido)

// MODO_NIVEL = false: o outro ESP manda um pulso e a esteira para por TEMPO_PARADA_MS
// MODO_NIVEL = true : a esteira fica parada enquanto o pino estiver em HIGH
const bool     MODO_NIVEL        = false;
const uint32_t ATRASO_TRAVA_MS   = 2000;   // tempo que a esteira continua rodando depois do sinal
const uint32_t TEMPO_PARADA_MS   = 4000;   // só vale no modo pulso (conta a partir da parada real)
const uint32_t FOLGA_RETOMADA_MS = 300;    // pequena folga antes de religar no modo nível

// Sequência de meio-passo
const uint8_t SEQ[8][4] = {
  {1,0,0,0}, {1,1,0,0}, {0,1,0,0}, {0,1,1,0},
  {0,0,1,0}, {0,0,1,1}, {0,0,0,1}, {1,0,0,1}
};

int indicePasso = 0;
uint32_t ultimoPassoUs = 0;

bool esteiraParada = false;
bool travaPendente = false;        // sinal recebido, aguardando o atraso para parar
uint32_t inicioPendenteMs = 0;
uint32_t inicioParadaMs = 0;
uint32_t ultimoHighMs = 0;
int ultimoIntertrava = LOW;

// ---------- Motor ----------
void aplicarPasso(int i) {
  digitalWrite(IN1, SEQ[i][0]);
  digitalWrite(IN2, SEQ[i][1]);
  digitalWrite(IN3, SEQ[i][2]);
  digitalWrite(IN4, SEQ[i][3]);
}

void desenergizarBobinas() {
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

// ---------- Intertravamento ----------
void pararEsteira() {
  if (!esteiraParada) {
    esteiraParada = true;
    desenergizarBobinas();   // evita aquecer o motor parado
    Serial.println("[INTERTRAVA] Esteira PARADA");
  }
}

void religarEsteira() {
  if (esteiraParada) {
    esteiraParada = false;
    ultimoPassoUs = micros();
    aplicarPasso(indicePasso);
    Serial.println("[INTERTRAVA] Esteira RODANDO");
  }
}

void atualizarIntertravamento() {
  int leitura = digitalRead(PINO_INTERTRAVA);
  uint32_t agora = millis();

  if (MODO_NIVEL) {
    if (leitura == HIGH) {
      ultimoHighMs = agora;
      // Borda de subida: começa a contar o atraso (sem travar o loop)
      if (ultimoIntertrava == LOW && !esteiraParada) {
        travaPendente = true;
        inicioPendenteMs = agora;
        Serial.printf("[INTERTRAVA] Sinal recebido. Parando em %lu ms\n", (unsigned long)ATRASO_TRAVA_MS);
      }
      if (travaPendente && agora - inicioPendenteMs >= ATRASO_TRAVA_MS) {
        travaPendente = false;
        pararEsteira();
      }
    } else {
      travaPendente = false;   // sinal caiu antes do atraso: cancela
      if (esteiraParada && agora - ultimoHighMs >= FOLGA_RETOMADA_MS) {
        religarEsteira();
      }
    }
  } else {
    // Borda de subida: inicia a contagem do atraso
    if (ultimoIntertrava == LOW && leitura == HIGH) {
      travaPendente = true;
      inicioPendenteMs = agora;
      Serial.printf("[INTERTRAVA] Pulso recebido. Parando em %lu ms\n", (unsigned long)ATRASO_TRAVA_MS);
    }
    // Passou o atraso: para e começa a contar o tempo de parada
    if (travaPendente && agora - inicioPendenteMs >= ATRASO_TRAVA_MS) {
      travaPendente = false;
      inicioParadaMs = agora;
      pararEsteira();
    }
    if (esteiraParada && agora - inicioParadaMs >= TEMPO_PARADA_MS) {
      religarEsteira();
    }
  }
  ultimoIntertrava = leitura;
}

void setup() {
  Serial.begin(115200);
  delay(1500);
  Serial.println("Esteira rodando.");

  pinMode(IN1, OUTPUT);
  pinMode(IN2, OUTPUT);
  pinMode(IN3, OUTPUT);
  pinMode(IN4, OUTPUT);

  pinMode(PINO_INTERTRAVA, INPUT_PULLDOWN);   // fio solto = LOW, não trava a esteira
}

void loop() {
  atualizarIntertravamento();
  if (!esteiraParada) passoNaoBloqueante();
}