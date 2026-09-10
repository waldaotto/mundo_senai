<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\TelemetriaController;

/** @var \App\Routes\Router $router */

$router->get('/', [HomeController::class, 'index']);
$router->get('/rastreamento', [TelemetriaController::class, 'index']);
