# Mundo Senai 2026

[![PHP](https://img.shields.io/badge/Backend-PHP-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![HTML5](https://img.shields.io/badge/Frontend-HTML5-E34F26?logo=html5&logoColor=white)](https://developer.mozilla.org/pt-BR/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/Frontend-CSS3-1572B6?logo=css3&logoColor=white)](https://developer.mozilla.org/pt-BR/docs/Web/CSS)
[![JavaScript](https://img.shields.io/badge/Frontend-JavaScript-F7DF1E?logo=javascript&logoColor=black)](https://developer.mozilla.org/pt-BR/docs/Web/JavaScript)
[![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Composer](https://img.shields.io/badge/Dependencies-Composer-885630?logo=composer&logoColor=white)](https://getcomposer.org/)
[![Docker](https://img.shields.io/badge/Ambiente-Docker-2496ED?logo=docker&logoColor=white)](https://www.docker.com/)
[![Linux](https://img.shields.io/badge/Ambiente-Linux-FCC624?logo=linux&logoColor=black)](https://www.linux.org/)
[![Apache](https://img.shields.io/badge/Servidor-Apache-D22128?logo=apache&logoColor=white)](https://httpd.apache.org/)
[![Git](https://img.shields.io/badge/Version_Control-Git-F05032?logo=git&logoColor=white)](https://git-scm.com/)
[![GitHub](https://img.shields.io/badge/Hospedagem-GitHub-181717?logo=github&logoColor=white)](https://github.com/)
[![Status](https://img.shields.io/badge/Status-Em%20Desenvolvimento-yellow)]()

## 📦 Sobre o Projeto

Aplicação web desenvolvida para o **Mundo Senai 2026**, com a finalidade de monitorar uma esteira de produtos com **RFID**.

O projeto simula uma esteira de distribuição de produtos de uma empresa, destinando caixas para diversas regiões do Brasil.

A versão final inclui a parte física do projeto: esteira, sensores RFID e braços robóticos.

## ⚙️ Funcionalidades

- **Conexão com banco de dados:** integração da aplicação com o MySQL.
- **Autenticação de usuário:** controle de acesso ao sistema.
- **Exibição de TAGs:** visualização das TAGs RFID registradas.

## 🛠️ Tecnologias

| Categoria      | Tecnologia | Finalidade                                   |
| -------------- | ---------- | -------------------------------------------- |
| Backend        | PHP        | Linguagem principal da aplicação.            |
| Frontend       | HTML5      | Estruturação das páginas.                    |
| Frontend       | CSS3       | Estilização da interface.                    |
| Frontend       | JavaScript | Interatividade da interface.                 |
| Banco de dados | MySQL      | Armazenamento e gerenciamento dos dados.     |
| Dependências   | Composer   | Gerenciamento de dependências do PHP.        |
| Ambiente       | Docker     | Containerização do ambiente de execução.     |
| Ambiente       | Linux      | Sistema operacional do ambiente.             |
| Ambiente       | Apache     | Servidor web.                                |
| Versionamento  | Git        | Controle de versão do código-fonte.          |
| Versionamento  | GitHub     | Hospedagem do repositório.                   |

## 🏗️ Arquitetura

```
C:.
├───App/
│   ├───Controllers/
│   ├───Core/
│   ├───Models/
│   ├───Routes/
│   ├───Services/
│   └───Views/
├───Public/
│   └───Assets/
└───vendor/
    └───composer/
```

O projeto é uma adaptação da arquitetura MVC para **MVCS**, separando as responsabilidades entre **Models**, **Views**, **Controllers** e **Services**.

## 🚀 Instalação

1. **Clone o repositório:**

```shell
git clone https://github.com/waldaotto/mundo_senai.git
```

2. **Entre na pasta do projeto:**

```shell
cd mundo_senai
```

3. **Instale as dependências com o [Composer](https://getcomposer.org/):**

```shell
composer install
```

4. **Acesse o Core do projeto:**

```shell
cd App/Core
```

5. **Crie o arquivo `env.php` dentro de `App/Core`:**

```php
<?php

return [
    "host" => "seu-host",
    "port" => "sua-porta",
    "db" => "sua-db",
    "password" => "sua-senha",
    "user" => "seu-user"
];
```

## 🗺️ Roadmap

- [x] Conexão com banco de dados.
- [x] Autenticação de usuário.
- [x] Exibição de TAGs.
- [ ] CRUD para TAGs.
- [ ] Painel admin.
- [ ] Painel de dashboard.
- [ ] Outras funcionalidades.

## 📌 Status

**Em Desenvolvimento**

O projeto está em fase de desenvolvimento. As funcionalidades estão sendo implementadas progressivamente.

## 📄 Licença

Licença ainda não definida.
