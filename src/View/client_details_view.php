<?php
// src/View/client_details_view.php

// Suas funções agora estarão disponíveis para todos os parciais incluídos abaixo.
require_once __DIR__ . '/partials/_helpers.php';

// Inclui o cabeçalho, que agora recebe os dados necessários do $viewData
require_once __DIR__ . '/partials/header_view.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Migração</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item active">Detalhes do Cliente</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <?php 
                // Inclui o formulário de detalhes da migração
                require __DIR__ . '/partials/migration_details_view.php';
            ?>
        </div>
    </section>
</div>

<script>
    // Passa o ID do cliente para o JavaScript, como antes
    var clienteId = <?= $client->id ?>;
</script>

<?php 
// Inclui o rodapé com os modais e scripts
require_once __DIR__ . '/partials/footer_view.php'; 
?>