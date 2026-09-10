# Mundo SENAI — Vitrine + Estação de Controle Inteligente

Projeto de automação logística estilo Mercado Livre: uma vitrine e-commerce em
PHP (MVC), um painel de telemetria em tempo real, e uma linha física de
triagem controlada por **2 placas ESP32**.

## Estrutura do projeto

```
mundo_senai/
├── index.php                 # Front controller (ponto de entrada único)
├── .htaccess                 # Reescrita de URLs (Apache)
├── composer.json
├── App/
│   ├── Core/
│   │   └── Controller.php    # Controller base (render, json, etc.)
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   └── TelemetriaController.php
│   ├── Models/
│   │   └── Produto.php       # Lê data/produtos.json
│   ├── Routes/
│   │   ├── Router.php
│   │   ├── UrlHelper.php
│   │   └── web.php           # Definição das rotas
│   └── Views/
│       ├── header.php / footer.php   # Layout da vitrine
│       ├── home.php                  # Vitrine (estilo Mercado Livre)
│       └── leituras.php              # Painel de telemetria (standalone)
├── Public/Assets/
│   ├── css/ (base.css, home.css, painel.css)
│   └── js/leituras.js        # Fetch polling a cada 1000ms
├── api/
│   └── telemetria.php        # API REST (POST dos ESP32, GET do painel)
├── data/
│   ├── produtos.json         # Catálogo da vitrine
│   └── status_esteira.json   # Estado atual da esteira (gravação atômica)
└── firmware/
    ├── ESP01_recepcao_pesagem/ESP01_recepcao_pesagem.ino
    └── ESP02_esteira_triagem/ESP02_esteira_triagem.ino
```

## Arquitetura de hardware (2 ESP32)

- **ESP-01 — Recepção e Pesagem**: RFID RC522 + célula de carga HX711 + 2
  servos (braço robótico). Lê o cartão RFID da caixa, define o destino
  (**RJ** ou **SC**) pelo UID, posiciona a caixa na balança, pesa e envia
  `POST` pra API.
- **ESP-02 — Esteira e Triagem**: motor via relé + sensor IR + servo
  desviador. Faz polling `GET` esperando `status = decisao_destino`, liga a
  esteira, detecta a caixa no IR, desvia para RJ ou SC, e envia `POST` final
  incrementando os contadores.

Não há sensor de cor nem terceiro ESP — o projeto usa só os dois módulos
acima.

## Rodando localmente para testar

Com o PHP instalado (`php -v` pra conferir), dentro da pasta do projeto:

```bash
php -S localhost:8000
```

Abra `http://localhost:8000/` no navegador (vitrine) e
`http://localhost:8000/rastreamento` (painel de telemetria).

> **Atenção:** o servidor embutido do PHP (`php -S`) não lê o `.htaccess`.
> Para testar as rotas bonitas (`/rastreamento`) localmente sem Apache, use
> um router script de teste ou instale um Apache/XAMPP local. Em produção
> no seu server com Apache, o `.htaccess` já cuida disso.

## Publicando no seu servidor (Apache)

1. Copie a pasta inteira `mundo_senai/` para o diretório servido pelo Apache
   (ex: `/var/www/html/mundo_senai` ou a raiz do seu virtual host).
2. Garanta que o módulo `mod_rewrite` está ativo no Apache
   (`a2enmod rewrite` e reinicie o Apache).
3. Dê permissão de escrita para o Apache na pasta `data/` (é onde fica o
   `status_esteira.json`, atualizado a cada leitura dos ESP32):
   ```bash
   chmod 775 data/
   chown www-data:www-data data/  # ajuste o usuário conforme seu server
   ```
4. Acesse pelo navegador: `http://SEU_IP_OU_DOMINIO/` (ou
   `http://SEU_IP/mundo_senai/` se não estiver na raiz).
5. **Se o projeto não estiver na raiz do domínio**, ajuste o `RewriteBase`
   no `.htaccess` se necessário, e confira se os links gerados por
   `UrlHelper` estão batendo com a URL real (eles já se adaptam sozinhos
   com base em `SCRIPT_NAME`, mas vale conferir).

## Configurando os ESP32

Em cada arquivo `.ino` dentro de `firmware/`, ajuste:

```cpp
const char* WIFI_SSID     = "NOME_DA_SUA_REDE";
const char* WIFI_PASSWORD = "SENHA_DA_SUA_REDE";
const char* API_URL       = "http://IP_DO_SEU_SERVER/api/telemetria.php";
```

- `IP_DO_SEU_SERVER` deve ser o IP local do computador/servidor que está
  rodando o Apache (ex: `192.168.0.100`), acessível pela mesma rede WiFi
  dos ESP32.
- No `ESP01_recepcao_pesagem.ino`, ajuste a tabela `mapaRFID[]` com os UIDs
  reais dos seus cartões e o `HX711_CALIB` com um peso conhecido.
- No `ESP02_esteira_triagem.ino`, confira a lógica do relé (`HIGH`/`LOW`) e
  do sensor IR (`IR_DETECTA_EM_LOW`) conforme os módulos que você está
  usando — inverta se estiver ao contrário.

### Bibliotecas Arduino IDE necessárias

- `WiFi`, `HTTPClient` (nativas do ESP32)
- `ArduinoJson` (Benoit Blanchon)
- `MFRC522` (GithubCommunity) — só ESP-01
- `HX711` (bogde) — só ESP-01
- `ESP32Servo` (Kevin Harrington) — ambos

## Fluxo completo do sistema

1. Cliente acessa a vitrine (`/`), navega pelos projetos.
2. Clica em "Acompanhe sua encomenda" (ou em um produto) → vai para
   `/rastreamento`.
3. O painel (`leituras.php` + `leituras.js`) faz `fetch` em
   `api/telemetria.php` a cada 1000ms.
4. Fisicamente: ESP-01 lê o RFID da caixa → define RJ/SC → pesa → envia
   `POST` (`status: recepcao` → `identificacao_id` → `pesagem` →
   automaticamente vira `decisao_destino`).
5. ESP-02 detecta `decisao_destino` via polling `GET`, liga a esteira,
   aguarda o sensor IR, desvia a caixa e envia `POST` final
   (`status: desviada`), que incrementa os contadores (`total`, `rj`, `sc`)
   e libera a estação pra próxima caixa.

## Próximos passos sugeridos

- Adicionar imagens reais dos produtos em `Public/Assets/img/` (os
  `<img>` da vitrine hoje apontam pra arquivos que ainda não existem e
  ficam ocultos via `onerror`).
- Se quiser, depois adiciono login e gestão de tags RFID (que já vi que
  você tem planejado em `LoginController` e `TagsController`).
