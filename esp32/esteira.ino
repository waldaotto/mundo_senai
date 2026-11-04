// ---------- Pinos ----------
const int IN1 = 5;
const int IN2 = 17;
const int IN3 =  16;
const int IN4 = 4;

// ---------- Configuração ----------
const uint32_t INTERVALO_PASSO_US = 2100;  // intervalo entre passos (~476 Hz)
const int DIRECAO = 1;                     // 1 ou -1 (inverte o sentido)

// Sequência de meio-passo
const uint8_t SEQ[8][4] = {
  {1,0,0,0}, {1,1,0,0}, {0,1,0,0}, {0,1,1,0},
  {0,0,1,0}, {0,0,1,1}, {0,0,0,1}, {1,0,0,1}
};

int indicePasso = 0;
uint32_t ultimoPassoUs = 0;

// ---------- Motor ----------
void aplicarPasso(int i) {
  digitalWrite(IN1, SEQ[i][0]);
  digitalWrite(IN2, SEQ[i][1]);
  digitalWrite(IN3, SEQ[i][2]);
  digitalWrite(IN4, SEQ[i][3]);
}

void passoNaoBloqueante() {
  uint32_t agora = micros();
  if (agora - ultimoPassoUs >= INTERVALO_PASSO_US) {
    ultimoPassoUs = agora;
    indicePasso = (indicePasso + DIRECAO + 8) % 8;
    aplicarPasso(indicePasso);
  }
}

void setup() {
  Serial.begin(115200);
  delay(1500);
  Serial.println("Esteira rodando.");

  pinMode(IN1, OUTPUT);
  pinMode(IN2, OUTPUT);
  pinMode(IN3, OUTPUT);
  pinMode(IN4, OUTPUT);
}

void loop() {
  passoNaoBloqueante();
}