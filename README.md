Mundo Senai 2026

***Este projeto tem como finalidade o Mundo Senai de 2026. Consiste em uma aplicação web para monitoramento de uma esteira de produtos com RFID.***

---

## Sobre o Projeto

O projeto simula uma esteira de distribução de produtos de uma empresa, destinando caixas para diversas regiões do Brasil.

A versão final inclue a parte fiisica do projeto: esteira, sensores RFID e braços roboticos.

## Funcionalidades

* [X] Conexão com banco de dados;
* [X] Autenticação de usuario;
* [X] Exibição de TAGs;
* [ ] CRUD para TAGs;
* [ ] Painel admin;
* [ ] Painel de dashboard;
* [ ] Outras funcionalidades...

## Tecnologias

### Backend

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)

### Frontend

![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

### Banco de dados

![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

### Dependências

![Composer](https://img.shields.io/badge/Composer-885630?style=for-the-badge&logo=composer&logoColor=white)

### Ambiente

![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Linux](https://img.shields.io/badge/Linux-FCC624?style=for-the-badge&logo=linux&logoColor=black)
![Apache](https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=apache&logoColor=white)

### Versionamento

![Git](https://img.shields.io/badge/Git-F05032?style=for-the-badge&logo=git&logoColor=white)
![GitHub](https://img.shields.io/badge/GitHub-181717?style=for-the-badge&logo=github&logoColor=white)

## Arquitetura

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

O projeto é uma adaptação de arquitetura MVC para MVCS, separando reponsabilidades entre Models, Views, Cotrollers e Services.


## Instalação

1. **Clone o Repositório:**

```Shell
git clone https://github.com/waldaotto/mundo_senai.git
```

2. **Entre na pasta do projeto:**

```Shell
cd mundo_senai
```

3. **Instale o [Composer](https://getcomposer.org/):**

```Shell
composer install
```

4. **Acesse o Core do projeto:**

```Shell
cd App/Core
```

5. **Crie dentro de App/Core o arquivo *env.php:***

```PHP
<?php

return [
    "host"=>"seu-host",
    "port"=>"sua-porta",
    "db"=>"sua-db",
    "password"=>"sua-senha",
    "user"=>"seu-user"
];
```
