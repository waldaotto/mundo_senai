<?php

// Caminhos do Servidor (Filesystem)
define('APP_PATH', ROOT."/App/");
define('CONTROLLERS_PATH', APP_PATH."/Controllers/");
define('SERVICES_PATH', APP_PATH."/Services/");
define('VIEWS_PATH', APP_PATH."/Views/");
define('CORE_PATH', APP_PATH."/Core/");
define('MODELS_PATH', APP_PATH."/Models/");

// URLs e Caminhos Web Públicos
define('BASE_URL', $_SERVER['HTTP_HOST']);

// A URL web aponta diretamente para a pasta Assets dentro de /mundo_senai/
define('PUBLIC_PATH', "/mundo_senai/Assets/");

// Assets para as Views
define('CSS_PATH', PUBLIC_PATH . "css/");
define('JS_PATH',  PUBLIC_PATH . "js/");
define('IMG_PATH', PUBLIC_PATH . "img/");