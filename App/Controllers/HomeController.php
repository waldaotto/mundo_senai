<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produto;

class HomeController extends Controller
{
    public function index(): void
    {
        $produtos   = Produto::todos();
        $destaques  = Produto::destaques();
        $categorias = Produto::categorias();

        $this->render('home', [
            'produtos'   => $produtos,
            'destaques'  => $destaques,
            'categorias' => $categorias,
        ]);
    }
}
