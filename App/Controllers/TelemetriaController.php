<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class TelemetriaController extends Controller
{
    public function index(): void
    {
        // O painel de telemetria tem visual próprio (tema "estação de controle"),
        // então não usa o header/footer da vitrine.
        $this->renderStandalone('leituras');
    }
}
