<?php
/**
 * src/View/csv_importer_view.php
 * View para a nova funcionalidade de Importador de CSV.
 */
require_once __DIR__ . '/partials/header_view.php'; 
?>

<!-- Identificador para o nosso JS saber que está nesta página -->
<div id="csv-importer-page" style="display: none;"></div>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>Funcionalidades <small>Importador de CSV</small></h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Coluna da Esquerda: Formulário de Configuração -->
                <div class="col-md-8">
                    <form id="formCsvImport" enctype="multipart/form-data">
                        <!-- Card de Conexão com o Banco de Destino -->
                        <div class="card card-primary">
                            <div class="card-header"><h3 class="card-title">1. Conexão com o Banco de Dados de Destino</h3></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6"><div class="form-group"><label>Tipo do Banco</label><select class="form-control" name="conexao[tipo]"><option value="mysql" selected>MySQL</option><option value="sqlsrv">SQL Server</option></select></div></div>
                                    <div class="col-md-6"><div class="form-group"><label>Nome do Banco</label><input type="text" class="form-control" name="conexao[dbName]" required></div></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6"><div class="form-group"><label>IP / Host</label><input type="text" class="form-control" name="conexao[ip]" required></div></div>
                                    <div class="col-md-6"><div class="form-group"><label>Porta</label><input type="text" class="form-control" name="conexao[porta]"></div></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6"><div class="form-group"><label>Usuário</label><input type="text" class="form-control" name="conexao[usuario]" required></div></div>
                                    <div class="col-md-6"><div class="form-group"><label>Senha</label><input type="password" class="form-control" name="conexao[senha]"></div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Card de Upload e Opções -->
                        <div class="card card-primary mt-3">
                             <div class="card-header"><h3 class="card-title">2. Arquivos e Opções</h3></div>
                             <div class="card-body">
                                <div class="form-group">
                                    <label for="csvZipFile">Arquivo .zip com os CSVs</label>
                                    <input type="file" id="csvZipFile" name="csv_zip_file" class="form-control" accept=".zip" required>
                                    <small class="form-text text-muted">Comprima todos os seus arquivos .csv em um único arquivo .zip para o upload.</small>
                                </div>
                                <div class="form-group mt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="limparTabelas" name="limpar_tabelas" value="1" checked>
                                        <label class="form-check-label" for="limparTabelas">Limpar tabelas de destino antes de importar? (TRUNCATE)</label>
                                    </div>
                                </div>
                             </div>
                             <div class="card-footer">
                                <button type="submit" class="btn btn-success"><i class="fas fa-rocket me-2"></i> Iniciar Importação</button>
                             </div>
                        </form>
                    </div>
                </div>

                <!-- Coluna da Direita: Log de Progresso -->
                <div class="col-md-4">
                    <div class="card card-dark">
                        <div class="card-header"><h3 class="card-title">Progresso da Importação</h3></div>
                        <div id="importLogOutput" class="card-body" style="height: 450px; overflow-y: auto; background: #1e2124; font-family: 'Courier New', Courier, monospace; color: #28a745; font-size: 0.9em; white-space: pre-wrap;">
                            Aguardando início do processo...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/partials/footer_view.php'; ?>
