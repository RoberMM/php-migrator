<?php
/**
 * src/View/partials/pessoa_table_form.php
 * Componente de formulário para a Tabela de Pessoas, organizado com um Accordion.
 */

$prefix = 'tabelaPessoa';
$isCollapsed = $viewData['config']->config['ui_state'][$prefix] ?? false;
?>
<div class="card card-dark mb-2 query-preview-component <?= $isCollapsed ? 'collapsed-card' : '' ?>" data-prefix="<?= $prefix ?>" data-card-id="<?= $prefix ?>">
    <div class="card-header">
        <h5 class="card-title mb-0">Tabela de Pessoas (cad_pessoa)</h5>
        <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas <?= $isCollapsed ? 'fa-plus' : 'fa-minus' ?>"></i></button></div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3"><div class="form-check form-switch pt-4"><input type="hidden" name="tabelaPessoa[migrar]" value="0"><input class="form-check-input" type="checkbox" name="tabelaPessoa[migrar]" value="1" <?= !empty($viewData['config']->config['tabelaPessoa']['migrar']) ? 'checked' : '' ?>><label class="form-check-label">Migrar esta tabela?</label></div></div>
            <div class="col-md-3"><div class="form-check form-switch pt-4"><input type="hidden" name="tabelaPessoa[limpar_dados]" value="0"><input class="form-check-input" type="checkbox" name="tabelaPessoa[limpar_dados]" value="1" <?= !empty($viewData['config']->config['tabelaPessoa']['limpar_dados']) ? 'checked' : '' ?>><label class="form-check-label">Limpar dados antes de migrar?</label></div></div>
        </div>

        <?php
            // Linha 2: Componente de Query/Preview
            $prefix = 'tabelaPessoa';
            $query  = $viewData['config']->config['tabelaPessoa']['query'] ?? '';
            include __DIR__ . '/_query_and_preview_panel.php';
        ?>

        <div class="row mt-3">
            <div class="col-md-3"><label>Leitura em Lotes de:</label><input type="number" class="form-control" name="tabelaPessoa[batch_size]" value="<?= htmlspecialchars($viewData['config']->config['tabelaPessoa']['batch_size'] ?? 5000) ?>"><small class="form-text text-muted">Registros lidos da origem por vez.</small></div>
            <div class="col-md-3"><label>Campo "De" (Chave Origem):</label><input type="text" class="form-control" name="tabelaPessoa[campo_de]" value="<?= htmlspecialchars($viewData['config']->config['tabelaPessoa']['campo_de'] ?? '') ?>"></div>
            <div class="col-md-3"><label>Tabela Origem (Log):</label><input type="text" class="form-control" name="tabelaPessoa[tabela_origem]" value="<?= htmlspecialchars($viewData['config']->config['tabelaPessoa']['tabela_origem'] ?? '') ?>"></div>
        </div>

        <hr>
        
        <h5 class="mt-4">Mapeamento de Colunas (Origem -> Destino)</h5>
        <div class="accordion" id="accordionPessoa">
            <?php
            $fieldsGroups = [
                'Dados Principais' => ['nome', 'categoria', 'fisica_juridica', 'sigla', 'classe', 'cpf_cnpj', 'rgie', 'data_nascimento', 'estado_civil', 'nacionalidade', 'pai_socio1', 'mae_socio2', 'teleitor', 'pis'],
                'Contato' => ['telefones', 'telefones_com', 'telefones_cob', 'fax', 'fax_com', 'fax_cob', 'celular', 'email', 'http', 'enviar_email', 'ntm_envio_email'],
                'Endereço Residencial' => ['endereco', 'endereco_numero', 'complemento', 'bairro', 'cep', 'cidade', 'estado', 'id_cidades_ibge'],
                'Endereço Comercial' => ['endereco_com', 'endereco_numero_com', 'complemento_com', 'bairro_com', 'cep_com', 'cidade_com', 'estado_com', 'id_cidades_ibge_com'],
                'Endereço Cobrança' => ['endereco_cob', 'endereco_numero_cob', 'complemento_cob', 'bairro_cob', 'cep_cob', 'cidade_cob', 'estado_cob', 'id_cidades_ibge_cob'],
                'Dados Profissionais' => ['ramo', 'ctrabalho', 'oab', 'empresa', 'vinculo', 'inscricao_municipal'],
                'Dados Bancários' => ['banco_nome', 'banco_codigo', 'banco_agencia', 'banco_agencia_dv', 'banco_conta', 'banco_conta_dv', 'banco_titular', 'banco_titular_cpf_cnpj', 'banco_observacao'],
                'Outros' => ['contato', 'contato_telefone', 'observacao', 'outras_informacoes', 'nivel', 'icontabil_cli', 'icontabil_for', 'id_tributo_perfil', 'fin_cta_adiantamento']
            ];
            
            $isFirst = true;
            
            foreach ($fieldsGroups as $groupTitle => $fields) {
                $collapseId = 'collapse' . preg_replace('/[^a-zA-Z]/', '', $groupTitle);
            ?>
                <div class="card card-outline card-primary">
                    <div class="card-header bg-secondary">
                        <h3 class="card-title w-100">
                            <a class="d-block w-100" data-bs-toggle="collapse" href="#<?= $collapseId ?>"><?= $groupTitle ?></a>
                        </h3>
                    </div>
                    <div id="<?= $collapseId ?>" class="collapse <?= $isFirst ? 'show' : '' ?>" data-bs-parent="#accordionPessoa">
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($fields as $field) { 
                                    // Pega a regra completa para este campo
                                    $rule = $viewData['config']->config['tabelaPessoa'][$field] ?? ['type'=>'source_column', 'value'=>''];
                                    if (is_string($rule)) { // Tratamento para dados antigos
                                        $rule = ['type'=>'source_column', 'value'=> $rule];
                                    }
                                ?>
                                    <div class="col-md-3 mb-3">
                                        <label><?= ucwords(str_replace('_', ' ', $field)) ?>:</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control mapping-preview" 
                                                value="<?= htmlspecialchars(formatRuleForDisplay($rule)) ?>" readonly
                                                placeholder="Clique na engrenagem para configurar">
                                            
                                            <input type="hidden" class="mapping-rule" 
                                                name="tabelaPessoa[<?= $field ?>]" 
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
                </div>
            <?php $isFirst = false; } ?>
        </div>
    </div>
</div>