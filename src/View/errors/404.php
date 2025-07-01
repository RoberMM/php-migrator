<?php 
/**
 * src/View/errors/404.php
 * View para a página de erro 404 (Não Encontrado).
 */
require_once __DIR__ . '/../partials/header_view.php'; 
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="error-page" style="padding-top: 15vh;">
                <h2 class="headline text-warning"> 404</h2>

                <div class="error-content">
                    <h3><i class="fas fa-exclamation-triangle text-warning"></i> Ops! Página não encontrada.</h3>

                    <p>
                        Não foi possível encontrar a página que você está procurando. <br>
                        Isso pode ter acontecido porque a URL está incorreta ou porque o item que você estava vendo (como um cliente) foi excluído.
                    </p>

                    <a href="<?= htmlspecialchars($viewData['basePath']) ?>/dashboard" class="btn btn-primary mt-4">Voltar para o Dashboard</a>
                </div>
                </div>
            </div>
    </section>
</div>

<?php 
require_once __DIR__ . '/../partials/footer_view.php'; 
?>