<?php
/**
 * src/View/templates_view.php
 * View para a página de Gerenciamento de Templates Padrão.
 */
require_once __DIR__ . '/partials/header_view.php'; 
?>

<div id="templates-page-identifier" style="display: none;"></div>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid"><h1>Gerenciamento de Templates Padrão</h1></div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-dark">
                <div class="card-header"><h3 class="card-title">Adicionar Novo Template</h3></div>
                <div class="card-body">
                    <form id="formNovoTemplate" enctype="multipart/form-data">
                        <div class="row align-items-end">
                            <div class="col-md-5"><div class="form-group"><label>Nome do Template</label><input type="text" name="nome" class="form-control" required placeholder="Ex: Padrão Cliente Tipo A"></div></div>
                            <div class="col-md-5"><div class="form-group"><label>Arquivo de Configuração (.json)</label><input type="file" name="config_file" class="form-control" accept=".json" required></div></div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" id="btnSalvarTemplate" class="btn btn-primary w-100">
                                    <i class="fas fa-save"></i> Salvar Template
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card card-dark mt-4">
                <div class="card-header"><h3 class="card-title">Templates Salvos</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="listaTemplates">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php 
require_once __DIR__ . '/partials/footer_view.php'; 
?>