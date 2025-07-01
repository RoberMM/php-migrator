<?php
/**
 * src/View/partials/connections_form.php
 * View parcial que renderiza os formulários de conexão de Origem e Destino.
 */
// Pega o estado salvo para este card, padrão é 'não recolhido' (false)
$isCollapsed = $viewData['config']->config['ui_state']['connections_form'] ?? false;
?>
<div class="card card-dark mb-3 <?= $isCollapsed ? 'collapsed-card' : '' ?>" data-card-id="connections_form">
    <div class="card-header">
        <h5 class="card-title">Dados de Origem e Destino</h5>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Recolher/Expandir">
                <i class="fas <?= $isCollapsed ? 'fa-plus' : 'fa-minus' ?>"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            
            <div class="col-md-6 border-end pe-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6>Origem</h6>
                    <button type="button" class="btn btn-secondary btn-sm btn-test-connection" data-type="origem">
                        <i class="fas fa-plug"></i> Testar Conexão
                    </button>
                </div>
                <div class="text-end mb-2" style="height: 20px;"><span id="status-origem"></span></div>

                <div class="form-group mb-3">
                    <label>Tipo do Banco</label>
                    <select class="form-select" name="conexao[origem][tipo]">
                        <option value="mysql"  <?= ($viewData['config']->config['conexao']['origem']['tipo'] ?? '') === 'mysql'    ? 'selected' : '' ?>>MySQL</option>
                        <option value="sqlsrv" <?= ($viewData['config']->config['conexao']['origem']['tipo'] ?? '') === 'sqlsrv'   ? 'selected' : '' ?>>SQL Server</option>
                        <option value="firebird" <?= ($viewData['config']->config['conexao']['origem']['tipo'] ?? '') === 'firebird' ? 'selected' : '' ?>>Firebird</option>
                    </select>
                </div>
                <div class="row mb-3">
                    <div class="col-md-5"><label>Banco</label><input type="text" class="form-control" name="conexao[origem][dbName]" placeholder="Nome do banco" value="<?= htmlspecialchars($viewData['config']->config['conexao']['origem']['dbName'] ?? '') ?>"></div>
                    <div class="col-md-4"><label>IP</label><input type="text" class="form-control" name="conexao[origem][ip]" placeholder="Endereço IP" value="<?= htmlspecialchars($viewData['config']->config['conexao']['origem']['ip'] ?? '') ?>"></div>
                    <div class="col-md-3"><label>Porta</label><input type="text" class="form-control" name="conexao[origem][porta]" placeholder="Porta" value="<?= htmlspecialchars($viewData['config']->config['conexao']['origem']['porta'] ?? '') ?>"></div>
                </div>
                <div class="row">
                    <div class="col-6"><label>Usuário</label><input type="text" class="form-control" name="conexao[origem][usuario]" placeholder="Usuário" value="<?= htmlspecialchars($viewData['config']->config['conexao']['origem']['usuario'] ?? '') ?>"></div>
                    <div class="col-6"><label>Senha</label><input type="text" class="form-control" name="conexao[origem][senha]" placeholder="Senha" value="<?= htmlspecialchars($viewData['config']->config['conexao']['origem']['senha'] ?? '') ?>"></div>
                </div>
            </div>
            
            <div class="col-md-6 ps-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6>Destino</h6>
                    <button type="button" class="btn btn-secondary btn-sm btn-test-connection" data-type="destino">
                        <i class="fas fa-plug"></i> Testar Conexão
                    </button>
                </div>
                <div class="text-end mb-2" style="height: 20px;"><span id="status-destino"></span></div>

                <div class="form-group mb-3">
                    <label>Tipo do Banco</label>
                    <select class="form-select" name="conexao[destino][tipo]">
                        <option value="mysql"    <?= ($viewData['config']->config['conexao']['destino']['tipo'] ?? '') === 'mysql'    ? 'selected' : '' ?>>MySQL</option>
                        <option value="sqlsrv"   <?= ($viewData['config']->config['conexao']['destino']['tipo'] ?? '') === 'sqlsrv'   ? 'selected' : '' ?>>SQL Server</option>
                        <option value="firebird" <?= ($viewData['config']->config['conexao']['destino']['tipo'] ?? '') === 'firebird' ? 'selected' : '' ?>>Firebird</option>
                    </select>
                </div>
                <div class="row mb-3">
                    <div class="col-md-5"><label>Banco</label><input type="text" class="form-control" name="conexao[destino][dbName]" placeholder="Nome do banco" value="<?= htmlspecialchars($viewData['config']->config['conexao']['destino']['dbName'] ?? '') ?>"></div>
                    <div class="col-md-4"><label>IP</label><input type="text" class="form-control" name="conexao[destino][ip]" placeholder="Endereço IP" value="<?= htmlspecialchars($viewData['config']->config['conexao']['destino']['ip'] ?? '') ?>"></div>
                    <div class="col-md-3"><label>Porta</label><input type="text" class="form-control" name="conexao[destino][porta]" placeholder="Porta" value="<?= htmlspecialchars($viewData['config']->config['conexao']['destino']['porta'] ?? '') ?>"></div>
                </div>
                <div class="row">
                    <div class="col-6"><label>Usuário</label><input type="text" class="form-control" name="conexao[destino][usuario]" placeholder="Usuário" value="<?= htmlspecialchars($viewData['config']->config['conexao']['destino']['usuario'] ?? '') ?>"></div>
                    <div class="col-6"><label>Senha</label><input type="text" class="form-control" name="conexao[destino][senha]" placeholder="Senha" value="<?= htmlspecialchars($viewData['config']->config['conexao']['destino']['senha'] ?? '') ?>"></div>
                </div>
            </div>
            
        </div>
    </div>
</div>