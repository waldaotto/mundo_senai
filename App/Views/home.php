<?php
use App\Routes\UrlHelper;

$mapaSelo = [
    'Maior Destaque' => 'selo--destaque',
    'Mais Avaliado'  => 'selo--avaliado',
    'Frete grátis'   => 'selo--frete',
    'Novo'           => 'selo--novo',
];

function estrelas(float $nota): string
{
    $cheias = (int) round($nota);
    return str_repeat('★', $cheias) . str_repeat('☆', 5 - $cheias);
}
?>

<main class="conteudo">

  <section class="hero">
    <div class="hero__conteudo">
      <span class="hero__selo">✨ VITRINE DE PROJETOS</span>
      <h1 class="hero__titulo">Projetos dos alunos do <span class="destaque">SENAI</span></h1>
      <p class="hero__texto">
        Protótipos, automações e inovações desenvolvidos nas unidades de todo o Brasil.
        Conheça, inspire-se e acompanhe a próxima geração da indústria.
      </p>
      <div class="hero__acoes">
        <a href="#projetos-destaque" class="botao-primario">Ver projetos</a>
        <span class="hero__contador">🎓 +<?= count($produtos) * 20 ?> protótipos publicados</span>
      </div>
    </div>
    <div class="hero__icone">🎓</div>
  </section>

  <div class="secao-topo" id="projetos-destaque">
    <h2>Projetos em destaque</h2>
    <span class="contagem"><?= count($destaques) ?> protótipos</span>
  </div>

  <div class="grid-produtos">
    <?php foreach ($destaques as $produto): ?>
      <a class="card-produto" href="<?= UrlHelper::url('/rastreamento') ?>">
        <div class="card-produto__imagem">
          <?php if (!empty($produto['selo'])): ?>
            <span class="card-produto__selo <?= $mapaSelo[$produto['selo']] ?? 'selo--novo' ?>">
              <?= htmlspecialchars($produto['selo']) ?>
            </span>
          <?php endif; ?>
          <img src="<?= UrlHelper::url($produto['imagem']) ?>"
               alt="<?= htmlspecialchars($produto['nome']) ?>"
               onerror="this.style.display='none'">
        </div>

        <div class="card-produto__preco">
          R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
        </div>
        <div class="card-produto__parcela">
          <?= $produto['parcelas'] ?>x R$ <?= number_format($produto['valor_parcela'], 2, ',', '.') ?> sem juros
        </div>

        <div class="card-produto__nome"><?= htmlspecialchars($produto['nome']) ?></div>

        <div class="card-produto__avaliacao">
          <span class="estrelas"><?= estrelas($produto['avaliacao']) ?></span>
          <span><?= number_format($produto['avaliacao'], 1, ',', '.') ?></span>
          <span>(<?= $produto['qtde_avaliacoes'] ?> avaliações)</span>
        </div>

        <div class="card-produto__autor">
          por <?= htmlspecialchars($produto['autor']) ?> · <?= htmlspecialchars($produto['curso']) ?>
        </div>

        <?php if (!empty($produto['frete_gratis'])): ?>
          <div class="card-produto__frete">🚚 Frete grátis</div>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>

</main>
