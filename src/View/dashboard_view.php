<?php
/**
 * src/View/dashboard_view.php
 *
 * View principal do Dashboard com o novo layout de duas colunas
 * e filtros de log aprimorados.
 */

// 1. Inclui o cabeçalho
require_once __DIR__ . '/partials/header_view.php';
?>

<div id="dashboard-page-identifier" style="display: none;"></div>

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <h1>Dashboard</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12 col-sm-6 col-md-3"><div class="info-box bg-info"><span class="info-box-icon"><i class="fas fa-exchange-alt"></i></span><div class="info-box-content"><span class="info-box-text">Total de Migrações</span><span id="totalMigracoes" class="info-box-number">0</span></div></div></div>
                <div class="col-12 col-sm-6 col-md-3"><div class="info-box bg-success"><span class="info-box-icon"><i class="fas fa-database"></i></span><div class="info-box-content"><span class="info-box-text">Registros Migrados</span><span id="registrosMigrados" class="info-box-number">0</span></div></div></div>
                <div class="col-12 col-sm-6 col-md-3"><div class="info-box bg-warning"><span class="info-box-icon"><i class="fas fa-check-circle"></i></span><div class="info-box-content"><span class="info-box-text">Taxa de Sucesso</span><span id="taxaSucesso" class="info-box-number">0%</span></div></div></div>
                <div class="col-12 col-sm-6 col-md-3"><div class="info-box bg-danger"><span class="info-box-icon"><i class="fas fa-clock"></i></span><div class="info-box-content"><span class="info-box-text">Tempo Médio</span><span id="tempoMedio" class="info-box-number">0&nbsp;seg</span></div></div></div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card card-dark">
                        <div class="card-header">
                            <h5 class="card-title">Filtros</h5>
                        </div>
                        <div class="card-body">
                            <form id="filterForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="filterCliente">Cliente:</label>
                                            <select id="filterCliente" name="cliente_id" class="form-control">
                                                <option value="" selected>Todos os Clientes</option>
                                                <?php foreach ($viewData['allClients'] as $id => $nome): ?>
                                                    <option value="<?= $id ?>"><?= htmlspecialchars($nome) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filterNivel">Nível Log:</label>
                                            <select id="filterNivel" name="nivel" class="form-control">
                                                <option value="" selected>Todos</option>
                                                <option value="INFO">Info</option>
                                                <option value="DEBUG">Debug</option>
                                                <option value="WARN">Warning</option>
                                                <option value="ERROR">Error</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="filterEvento">Evento Log:</label>
                                            <input type="text" id="filterEvento" name="evento" class="form-control" placeholder="Ex: INICIO_LOTE">
                                        </div>
                                    </div>

                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card card-dark">
                        <div class="card-header"><h5 class="card-title">Logs de Migração</h5></div>
                        <div class="card-body table-responsive p-0" style="height: 400px;">
                            <table class="table table-head-fixed text-nowrap">
                                <thead><tr><th>Nível</th><th>Evento</th><th>Mensagem</th></tr></thead>
                                <tbody id="logsTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                     <div class="card card-dark">
                        <div class="card-header"><h3 class="card-title">Resumo das Migrações</h3></div>
                        <div class="card-body">
                            <div class="chart" style="position: relative; height: 400px;">
                                <canvas id="migrationChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <div class="row">
                <div class="col-md-6">
                    <div class="card card-dark">
                        <div class="card-header"><h3 class="card-title"><i class="far fa-list-alt me-2"></i> Lista de afazeres </h3><div class="card-tools"><button id="addTodoBtn" class="btn btn-tool btn-sm" title="Adicionar"><i class="fas fa-plus"></i></button></div></div>
                        <div class="card-body p-0"><ul id="todoList" class="todo-list" style="height: 400px; overflow-y: auto;"></ul></div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
<?php
// 3. Inclui o rodapé
require_once __DIR__ . '/partials/footer_view.php';
?>