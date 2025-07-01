<?php
/**
 * src/View/partials/action_table_form.php
 *
 * Componente completo para o formulário da "Tabela Ação", usando a arquitetura final.
 */
$prefix = 'tabelaAcao';
$isCollapsed = $viewData['config']->config['ui_state'][$prefix] ?? false;
?>
<div class="card card-dark mb-3 query-preview-component <?= $isCollapsed ? 'collapsed-card' : '' ?>" data-prefix="<?= $prefix ?>" data-card-id="<?= $prefix ?>">
    <div class="card-header">
        <h5 class="card-title mb-0">Tabela Ação (tab_acao)</h5>
        <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas <?= $isCollapsed ? 'fa-plus' : 'fa-minus' ?>"></i></button></div>
    </div>
    <div class="card-body">
        <div class="row mb-4 align-items-center">
            <div class="col-md-auto"><div class="form-check form-switch"><input type="hidden" name="tabelaAcao[migrar]" value="0"><input class="form-check-input" type="checkbox" role="switch" name="tabelaAcao[migrar]" value="1" <?= !empty($viewData['config']->config['tabelaAcao']['migrar']) ? 'checked' : '' ?>><label class="form-check-label">Migrar esta tabela?</label></div></div>
            <div class="col-md-auto"><div class="form-check form-switch"><input type="hidden" name="tabelaAcao[limpar_dados]" value="0"><input class="form-check-input" type="checkbox" role="switch" name="tabelaAcao[limpar_dados]" value="1" <?= !empty($viewData['config']->config['tabelaAcao']['limpar_dados']) ? 'checked' : '' ?>><label class="form-check-label">Limpar dados antes?</label></div></div>
        </div>

        <?php
            $query  = $viewData['config']->config[$prefix]['query'] ?? '';
            include __DIR__ . '/_query_and_preview_panel.php';
        ?>

        <div class="row mt-3">
            <div class="col-md-4"><label>Leitura em Lotes de:</label><input type="number" class="form-control" name="tabelaAcao[batch_size]" value="<?= htmlspecialchars($viewData['config']->config['tabelaAcao']['batch_size'] ?? 5000) ?>"></div>
            <div class="col-md-4"><label>Tabela Origem (p/ Log):</label><input type="text" class="form-control" name="tabelaAcao[tabela_origem]" value="<?= htmlspecialchars($viewData['config']->config['tabelaAcao']['tabela_origem'] ?? '') ?>"></div>
            <div class="col-md-4"><label>Campo "De" (Chave Origem):</label><input type="text" class="form-control" name="tabelaAcao[campo_de]" value="<?= htmlspecialchars($viewData['config']->config['tabelaAcao']['campo_de'] ?? '') ?>"></div>
        </div>
        
        <hr>
        <h6 class="mt-4">Mapeamento de Colunas (Origem -> Destino)</h6>
        
        <div class="row mt-3">
            <?php
            // Lista dos campos de mapeamento para esta tabela
            $mappingFields = ['sigla', 'descricao']; // Adicione outros campos aqui se necessário
            
            foreach ($mappingFields as $field) {
                $rule = $viewData['config']->config['tabelaAcao'][$field] ?? ['type' => 'source_column', 'value' => ''];
                if (is_string($rule)) { $rule = ['type' => 'source_column', 'value' => $rule]; }
            ?>
                <div class="col-md-4 mb-3">
                    <label><?= ucwords(str_replace('_', ' ', $field)) ?>:</label>
                    <div class="input-group">
                        <input type="text" class="form-control mapping-preview" 
                               value="<?= htmlspecialchars(formatRuleForDisplay($rule)) ?>" readonly
                               placeholder="Clique na engrenagem para configurar">
                        
                        <input type="hidden" class="mapping-rule" 
                               name="tabelaAcao[<?= $field ?>]" 
                               value='<?= htmlspecialchars(json_encode($rule)) ?>'>
                        
                        <button type="button" class="btn btn-outline-info btn-advanced-mapping" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalAdvancedMapping"
                                title="Mapeamento Avançado">
                            <i class="fas fa-cogs"></i>
                        </button>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>