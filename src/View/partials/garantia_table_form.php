<?php
/**
 * src/View/partials/garantia_table_form.php
 * Componente de formulário para a Tabela de Garantia.
 */
$prefix = 'tabelaGarantia';
$config = $viewData['config']->config[$prefix] ?? [];
$isCollapsed = $viewData['config']->config['ui_state'][$prefix] ?? false;
?>
<div class="card card-dark mb-3 query-preview-component <?= $isCollapsed ? 'collapsed-card' : '' ?>" data-prefix="<?= $prefix ?>" data-card-id="<?= $prefix ?>">
    <div class="card-header">
        <h5 class="card-title mb-0">Tabela de Garantia (tab_garantia)</h5>
        <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas <?= $isCollapsed ? 'fa-plus' : 'fa-minus' ?>"></i></button></div>
    </div>
    <div class="card-body">
        <div class="row mb-4 align-items-center">
            <div class="col-md-auto"><div class="form-check form-switch"><input type="hidden" name="<?= $prefix ?>[migrar]" value="0"><input class="form-check-input" type="checkbox" name="<?= $prefix ?>[migrar]" value="1" <?= !empty($config['migrar']) ? 'checked' : '' ?>><label class="form-check-label">Migrar esta tabela?</label></div></div>
            <div class="col-md-auto"><div class="form-check form-switch"><input type="hidden" name="<?= $prefix ?>[limpar_dados]" value="0"><input class="form-check-input" type="checkbox" name="<?= $prefix ?>[limpar_dados]" value="1" <?= !empty($config['limpar_dados']) ? 'checked' : '' ?>><label class="form-check-label">Limpar dados antes?</label></div></div>
        </div>

        <?php
            $query  = $viewData['config']->config[$prefix]['query'] ?? '';
            include __DIR__ . '/_query_and_preview_panel.php';
        ?>

        <div class="row mt-3">
            <div class="col-md-4"><label>Leitura em Lotes de:</label><input type="number" class="form-control" name="<?= $prefix ?>[batch_size]" value="<?= htmlspecialchars($config['batch_size'] ?? 5000) ?>"></div>
            <div class="col-md-4"><label>Tabela Origem (p/ Log):</label><input type="text" class="form-control" name="<?= $prefix ?>[tabela_origem]" value="<?= htmlspecialchars($config['tabela_origem'] ?? '') ?>"></div>
            <div class="col-md-4"><label>Campo "De" (Chave Origem):</label><input type="text" class="form-control" name="<?= $prefix ?>[campo_de]" value="<?= htmlspecialchars($config['campo_de'] ?? '') ?>"></div>
        </div>
        
        <hr>
        <h6 class="mt-4">Mapeamento de Colunas (Origem -> Destino)</h6>
        
        <div class="accordion" id="accordionGarantia">
            <?php
            $fieldsGroups = [
                'Dados da Garantia' => ['descricao', 'ativa_passiva', 'codigo_ativo_empresa', 'codigo_gra_tipo', 'caracteristicas', 'valor', 'data_valor', 'data_avaliacao', 'sigla_regra'],
                'Endereço do Imóvel' => ['imo_num_matricula', 'endereco', 'cidade', 'uf'],
                'Cédula de Fiança' => ['cf_pessoa_cedente', 'cf_data_vencimento', 'cf_num_carta', 'cf_data_emissao'],
                'Garantia de Produto' => ['prod_local_pessoa', 'prod_quantidade']
            ];
            
            $isFirst = true;
            foreach ($fieldsGroups as $groupTitle => $fields) {
                $collapseId = 'collapseGarantia' . preg_replace('/[^a-zA-Z]/', '', $groupTitle);
            ?>
                <div class="card card-outline card-primary">
                    <div class="card-header bg-secondary"><h3 class="card-title w-100"><a class="d-block w-100" data-bs-toggle="collapse" href="#<?= $collapseId ?>"><?= $groupTitle ?></a></h3></div>
                    <div id="<?= $collapseId ?>" class="collapse <?= $isFirst ? 'show' : '' ?>" data-bs-parent="#accordionGarantia">
                        <div class="card-body"><div class="row">
                            <?php foreach ($fields as $field) {
                                $rule = $config[$field] ?? ['type' => 'source_column', 'value' => ''];
                                if (is_string($rule)) { $rule = ['type' => 'source_column', 'value' => $rule]; }
                            ?>
                                <div class="col-md-4 mb-3">
                                    <label><?= ucwords(str_replace('_', ' ', $field)) ?>:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control mapping-preview" value="<?= htmlspecialchars(formatRuleForDisplay($rule)) ?>" readonly placeholder="Clique para configurar">
                                        <input type="hidden" class="mapping-rule" name="<?= $prefix ?>[<?= $field ?>]" value='<?= htmlspecialchars(json_encode($rule)) ?>'>
                                        <button type="button" class="btn btn-outline-info btn-advanced-mapping" data-bs-toggle="modal" data-bs-target="#modalAdvancedMapping" title="Mapeamento Avançado"><i class="fas fa-cogs"></i></button>
                                    </div>
                                </div>
                            <?php } ?>
                        </div></div>
                    </div>
                </div>
            <?php $isFirst = false; } ?>
        </div>
    </div>
</div>