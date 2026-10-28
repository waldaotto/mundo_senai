/*
  ESP32 + RFID (MFRC522) + Balança (HX711) + LCD 16x2 I2C
  Envia JSON {"rfid":"XX XX XX XX","peso":300.25} ao servidor
  somente quando a tag foi lida E o peso estiver estável.

  Conexões:
    MFRC522: SS -> GPIO21, RST -> GPIO22, SCK -> 18, MISO -> 19, MOSI -> 23
    HX711:   DT -> GPIO26, SCK -> GPIO27
    Botão de tara: GPIO13 e GND (pull-up interno)
    LCD I2C: SDA -> GPIO33, SCL -> GPIO25
             (GPIO35 NÃO serve para SDA: é só entrada, sem saída/pull-up)

  Bibliotecas (Gerenciador de Bibliotecas):
    MFRC522, HX711 (Bogdan Necula), LiquidCrystal_I2C (Frank de Brabander)
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <Wire.h>
#include <MFRC522.h>
#include <HX711.h>
#include <LiquidCrystal_I2C.h>

// ======================= CONFIGURAÇÕES =======================

// RFID
#define SS_PIN  21
#define RST_PIN 22
MFRC522 rfrc522(SS_PIN, RST_PIN);

// Balança
const int LOADCELL_DOUT_PIN = 26;
const int LOADCELL_SCK_PIN  = 27;
const int BOTAO_TARA_PIN    = 13;
float fatorCalibracao = 107;   // ajuste conforme sua calibração
HX711 balanca;

// LCD I2C 16x2
const int LCD_SDA_PIN = 33;    // GPIO35 é input-only, não funciona como SDA
const int LCD_SCL_PIN = 25;
const uint8_t LCD_ENDERECO = 0x27;   // se não aparecer nada, tente 0x3F
LiquidCrystal_I2C lcd(LCD_ENDERECO, 16, 2);

// Wi-Fi e API
const char* SSID       = "MundoSENAI";
const char* SENHA_WIFI = "senai@2025";
const char* URL_API    = "http://serversenai.local/mundo_senai/api/rfid";

// Estabilidade do peso
const float TOLERANCIA_G  = 10.0;  // variação máxima na janela (g)
const int   N_AMOSTRAS    = 8;     // tamanho da janela
const float PESO_MINIMO_G = 5.0;   // abaixo disso considera balança vazia

// Outros tempos
const unsigned long DEBOUNCE_TARA         = 300;    // ms
const unsigned long TIMEOUT_UID           = 30000;  // descarta UID se nenhum item for pesado
const unsigned long INTERVALO_RETENTATIVA = 3000;   // ms após falha de envio
const unsigned long TIMEOUT_HX711         = 3000;   // avisa se ficar tanto tempo sem leitura
const unsigned long TIMEOUT_WIFI          = 15000;  // tempo máx. na conexão inicial
const unsigned long INTERVALO_RECONEXAO   = 5000;   // ms entre tentativas de reconexão
const unsigned long INTERVALO_LCD         = 250;    // ms entre atualizações do LCD

// ======================= ESTADO =======================

float amostras[N_AMOSTRAS];
int   qtdAmostras = 0;
int   idxAmostra  = 0;

String        uidAtual     = "";
unsigned long uidTimestamp = 0;
bool          itemPresente = false;
bool          jaEnviado    = false;   // true = envio concluído (ou rejeitado pelo servidor)

// Flags só para exibição / controle de erro
bool enviando   = false;
bool falhaEnvio = false;   // falha temporária (rede / servidor fora) -> vai tentar de novo
bool rejeitado  = false;   // servidor respondeu 4xx (ex.: tag não cadastrada) -> não repete
bool hxErro     = false;

float pesoAtual = 0;

unsigned long ultimaLeituraOk  = 0;
unsigned long ultimoAvisoHX    = 0;
unsigned long ultimoClique     = 0;
unsigned long proximaTentativa = 0;
unsigned long ultimaReconexao  = 0;
unsigned long ultimoLCD        = 0;

String ultL1 = "";
String ultL2 = "";

// ======================= LCD =======================

void lcdLinha(uint8_t linha, const String &texto)
{
    String t = texto;
    while (t.length() < 16) t += ' ';
    lcd.setCursor(0, linha);
    lcd.print(t.substring(0, 16));
}

// Texto curto (máx. 8 caracteres) com a situação atual.
// Sem acentos: o LCD 16x2 comum não exibe.
const char* calcularStatus()
{
    if (enviando)                              return "Enviando";
    if (rejeitado)                             return "Rejeit.";
    if (jaEnviado)                             return "Enviado";
    if (hxErro)                                return "Erro HX";
    if (WiFi.status() != WL_CONNECTED)         return "Sem WiFi";
    if (falhaEnvio)                            return "Falha";
    if (uidAtual == "")                        return itemPresente ? "Leia tag" : "Sem tag";
    if (!itemPresente)                         return "Coloque";
    return "Pesando";
}

void atualizarLCD(bool forcar = false)
{
    if (!forcar && millis() - ultimoLCD < INTERVALO_LCD) return;
    ultimoLCD = millis();

    String l1 = (uidAtual != "") ? ("Tag:" + uidAtual) : String("Aproxime a tag");

    char buf[24];
    snprintf(buf, sizeof(buf), "%6.1fg %-8s", pesoAtual, calcularStatus());
    String l2 = String(buf);

    // Só escreve se mudou (evita flicker)
    if (l1 != ultL1) { lcdLinha(0, l1); ultL1 = l1; }
    if (l2 != ultL2) { lcdLinha(1, l2); ultL2 = l2; }
}

// ======================= JANELA DE AMOSTRAS =======================

void limparAmostras()
{
    qtdAmostras = 0;
    idxAmostra  = 0;
}

void adicionarAmostra(float valor)
{
    amostras[idxAmostra] = valor;
    idxAmostra = (idxAmostra + 1) % N_AMOSTRAS;
    if (qtdAmostras < N_AMOSTRAS) qtdAmostras++;
}

// Retorna true se a janela está cheia e a variação é <= tolerância.
// Em "media" devolve a média da janela.
bool pesoEstavel(float &media)
{
    if (qtdAmostras < N_AMOSTRAS) return false;

    float minimo = amostras[0];
    float maximo = amostras[0];
    float soma   = 0;

    for (int i = 0; i < N_AMOSTRAS; i++)
    {
        if (amostras[i] < minimo) minimo = amostras[i];
        if (amostras[i] > maximo) maximo = amostras[i];
        soma += amostras[i];
    }

    media = soma / N_AMOSTRAS;
    return (maximo - minimo) <= TOLERANCIA_G;
}

// ======================= BALANÇA =======================

// tare() trava para sempre se o HX711 não estiver respondendo,
// então só tara se ele estiver pronto.
bool fazerTara()
{
    if (!balanca.wait_ready_timeout(3000))
    {
        Serial.println("HX711 nao respondeu: tara cancelada.");
        hxErro = true;
        return false;
    }
    balanca.tare(20);   // média de 20 leituras em vez de 10
    hxErro = false;
    return true;
}

// ======================= RFID =======================

String ler_rfid()
{
    if (!rfrc522.PICC_IsNewCardPresent()) return "";
    if (!rfrc522.PICC_ReadCardSerial())   return "";

    String uidLido = "";

    for (byte i = 0; i < rfrc522.uid.size; i++)
    {
        if (rfrc522.uid.uidByte[i] < 0x10) uidLido += "0";
        uidLido += String(rfrc522.uid.uidByte[i], HEX);
    }

    uidLido.toUpperCase();

    Serial.print("UID lido: ");
    Serial.println(uidLido);

    rfrc522.PICC_HaltA();
    rfrc522.PCD_StopCrypto1();

    return uidLido;
}

// ======================= WI-FI / ENVIO =======================

void conectarWiFi()
{
    WiFi.mode(WIFI_STA);
    WiFi.setAutoReconnect(true);
    WiFi.begin(SSID, SENHA_WIFI);

    lcdLinha(0, "Conectando WiFi");
    lcdLinha(1, "");

    unsigned long inicio = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - inicio < TIMEOUT_WIFI)
    {
        delay(500);
        Serial.println("Conectando ao Wi-Fi...");
    }

    if (WiFi.status() == WL_CONNECTED)
    {
        Serial.println("Wi-Fi conectado!");
        lcdLinha(0, "WiFi conectado");
        lcdLinha(1, WiFi.localIP().toString());
        delay(1200);
    }
    else
    {
        // Não trava o sistema: o loop() continua tentando reconectar
        Serial.println("Wi-Fi indisponivel, tentando em segundo plano...");
        lcdLinha(0, "Sem WiFi");
        lcdLinha(1, "Tentando...");
        delay(1200);
    }
}

// Retorna o código HTTP (2xx = sucesso), ou -1 se nem chegou a enviar
int enviarDados(const String &rfid, float peso)
{
    if (WiFi.status() != WL_CONNECTED)
    {
        Serial.println("Wi-Fi nao conectado.");
        return -1;
    }

    HTTPClient http;
    if (!http.begin(URL_API))
    {
        Serial.println("Falha ao iniciar HTTP.");
        return -1;
    }
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(5000);

    String json = "{\"rfid\":\"" + rfid + "\",\"peso\":" + String(peso, 2) + "}";

    Serial.print("Enviando: ");
    Serial.println(json);

    int codigo = http.POST(json);
    Serial.println("Resposta HTTP: " + String(codigo));

    if (codigo > 0)
    {
        Serial.println("Resposta da API:");
        Serial.println(http.getString());
    }

    http.end();

    return codigo;
}

// ======================= SETUP =======================

void setup()
{
    Serial.begin(115200);
    Serial.println("Iniciando...");

    pinMode(BOTAO_TARA_PIN, INPUT_PULLUP);
    pinMode(32,OUTPUT);

    // LCD: Wire.begin() com os pinos ANTES do lcd.init(), pois os pinos padrão
    // do I2C (21/22) estão ocupados pelo RFID.
    Wire.begin(LCD_SDA_PIN, LCD_SCL_PIN);
    lcd.init();
    lcd.backlight();
    lcdLinha(0, "Iniciando...");

    SPI.begin();
    rfrc522.PCD_Init();

    balanca.begin(LOADCELL_DOUT_PIN, LOADCELL_SCK_PIN);
    balanca.set_scale(fatorCalibracao);
    Serial.println("Ajustando o zero da balanca (sem peso)...");
    lcdLinha(0, "Zerando balanca");
    lcdLinha(1, "Sem peso!");
    delay(2000);              // deixa o HX711 e a fonte estabilizarem
    fazerTara();

    conectarWiFi();

    ultimaLeituraOk = millis();
    Serial.println("Sistema pronto! Aproxime o item da balanca/leitor.");
    atualizarLCD(true);
}

// ======================= LOOP =======================

void loop()
{
    // --- Botão de tara ---
    if (digitalRead(BOTAO_TARA_PIN) == LOW &&
        millis() - ultimoClique > DEBOUNCE_TARA)
    {
        Serial.println(">>> Botao pressionado: tara...");
        fazerTara();
        limparAmostras();
        pesoAtual    = 0;
        ultimoClique = millis();
    }

    // --- Mantém o Wi-Fi vivo (sem chamar reconnect() a cada volta do loop) ---
    if (WiFi.status() != WL_CONNECTED && millis() - ultimaReconexao > INTERVALO_RECONEXAO)
    {
        Serial.println("Wi-Fi caiu, reconectando...");
        WiFi.reconnect();
        ultimaReconexao = millis();
    }

    // --- Leitura do RFID (só procura tag nova se ainda não tem uma) ---
    if (uidAtual == "" && !jaEnviado)
    {
        String uid = ler_rfid();
        if (uid != "")
        {
            uidAtual     = uid;
            uidTimestamp = millis();
        }
    }

    // --- Descarta UID antigo se nenhum item foi colocado na balança ---
    if (uidAtual != "" && !itemPresente && !jaEnviado &&
        millis() - uidTimestamp > TIMEOUT_UID)
    {
        Serial.println("UID descartado (timeout sem item na balanca).");
        uidAtual = "";
    }

    // --- LCD (fica antes dos returns abaixo para atualizar sempre) ---
    atualizarLCD();

    // --- Leitura do peso: só quando o HX711 tem dado novo pronto ---
    if (!balanca.is_ready())
    {
        // Não é erro: o HX711 só gera ~10 leituras/s.
        // Só avisa se ficar muito tempo sem nenhuma leitura (problema real).
        if (millis() - ultimaLeituraOk > TIMEOUT_HX711 &&
            millis() - ultimoAvisoHX > TIMEOUT_HX711)
        {
            Serial.println("HX711 nao responde (verifique fios DT/SCK e alimentacao).");
            ultimoAvisoHX = millis();
            hxErro = true;
        }
        return;
    }

    ultimaLeituraOk = millis();
    hxErro = false;

    float peso = balanca.get_units(2);
    if (peso < 0) peso = 0;
    pesoAtual = peso;

    if (peso < PESO_MINIMO_G)
    {
        // Balança vazia: rearma o sistema para o próximo item
        if (itemPresente)
        {
            Serial.println("Item removido. Sistema rearmado.");
            itemPresente = false;
            falhaEnvio   = false;

            if (jaEnviado)
            {
                // Item já foi enviado (ou rejeitado) e retirado: libera tudo
                jaEnviado = false;
                rejeitado = false;
                uidAtual  = "";
            }
        }
        limparAmostras();
        return;
    }

    // Há peso na balança
    itemPresente = true;
    adicionarAmostra(peso);

    Serial.print("Peso: ");
    Serial.print(peso, 2);
    Serial.println(" g");

    float pesoFinal;
    if (uidAtual != "" && !jaEnviado &&
        pesoEstavel(pesoFinal) &&
        millis() >= proximaTentativa)
    {
        Serial.print("Peso estavel: ");
        Serial.print(pesoFinal, 2);
        Serial.println(" g");

        enviando = true;
        atualizarLCD(true);          // mostra "Enviando" antes da requisição bloqueante

        int codigo = enviarDados(uidAtual, pesoFinal);

        enviando = false;

        if (codigo >= 200 && codigo < 300)
        {
            jaEnviado  = true;
            falhaEnvio = false;
            digitalWrite(32,1);
            delay(100);
            digitalWrite(32,0);
            Serial.println("Enviado com sucesso. Retire o item para pesar outro.");
        }
        else if (codigo >= 400 && codigo < 500)
        {
            // Servidor recusou (ex.: tag não cadastrada): repetir não adianta
            jaEnviado = true;
            rejeitado = true;
            Serial.println("Servidor rejeitou o envio. Retire o item.");
        }
        else
        {
            falhaEnvio       = true;
            proximaTentativa = millis() + INTERVALO_RETENTATIVA;
            Serial.println("Falha no envio, tentando novamente em instantes...");
        }

        atualizarLCD(true);
    }
}
