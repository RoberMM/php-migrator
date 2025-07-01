<?php
/**
 * src/View/partials/migration_details_view.php
 *
 * Corpo principal do formulário de configuração da migração.
 * Todos os dados são acessados via $viewData.
 */
?>
<div class="card card-dark mb-3">
    <div class="card-header">
        <h5>Detalhes da Migração - <?= htmlspecialchars($viewData['client']->nome) ?></h5>
    </div>

    <div class="card-body">
        <form id="formTemplate" method="POST">
            <div id="migrationStatus" class="mt-3"></div>
            <input type="hidden" id="cliente_id" name="cliente_id" value="<?= $viewData['client']->id ?>">
            <input type="hidden" id="user_id" name="user_id" value="<?= $viewData['userData']['id'] ?>">

            <?php 
                // Parte 1: Formulário de Conexões
                require __DIR__ . '/connections_form.php'; 
            ?>

            <hr>

            <div class="mt-4">
                <h5>Tabelas para Migração</h5>
                <?php 
                    // Componente completo da Tabela Ação
                    require __DIR__ . '/action_table_form.php'; 

                    // Componente da Tabela de Assunto
                    require __DIR__ . '/assunto_table_form.php'; 

                    // Componente da Tabela de Categoria
                    require __DIR__ . '/categoria_table_form.php';

                    // Componente da Tabela de Causa de Pedir
                    require __DIR__ . '/causa_pedir_table_form.php';

                    // Componente da Tabela de Fase
                    require __DIR__ . '/fase_table_form.php'; 

                    // Componente da Tabela de Tipo de Garantia
                    require __DIR__ . '/garantia_tipo_table_form.php';

                    // Componente da Tabela de Garantia
                    require __DIR__ . '/garantia_table_form.php';

                    // Componente da Tabela de Grupo de Trabalho
                    require __DIR__ . '/grupo_trabalho_table_form.php'; 

                    // Componente da Tabela de Juízo
                    require __DIR__ . '/juizo_table_form.php';

                    // Componente da Tabela de Localizador
                    require __DIR__ . '/localizador_table_form.php'; 

                    // Componente da Tabela de Matéria
                    require __DIR__ . '/materia_table_form.php';

                    // Componente da Tabela de Órgão Julgador
                    require __DIR__ . '/orgao_julgador_table_form.php'; 

                    // Componente da Tabela de Qualificação
                    require __DIR__ . '/qualificacao_table_form.php';

                    // Componente da Tabela de Resultado
                    require __DIR__ . '/resultado_table_form.php'; 

                    // Componente da Tabela de Rito
                    require __DIR__ . '/rito_table_form.php';

                    // Componente da Tabela de Situação
                    require __DIR__ . '/situacao_table_form.php'; 

                    // Componente da Tabela de Tema
                    require __DIR__ . '/tema_table_form.php'; 

                    // Componente completo da Tabela Pessoas
                    require __DIR__ . '/pessoa_table_form.php';

                    // Componente da Tabela de Unidade
                    require __DIR__ . '/unidade_table_form.php';

                ?>
                <hr>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="d-flex">
                    <button type="button" id="btnExportarJSON" class="btn btn-primary mr-2"
                            data-client-id="<?= $viewData['client']->id ?>">
                        <i class="fas fa-file-export"></i> Exportar JSON
                    </button>
                    <button type="button" id="btnImportarJSON" class="btn btn-secondary mr-2">
                        <i class="fas fa-upload"></i> Importar JSON
                    </button>
                    <input type="file" id="importarJsonFile" style="display:none" accept=".json">
                </div>
                <div id="saveStatus" class="text-muted fst-italic"></div>

                <div class="d-flex">
                    <button type="button" id="btnIniciarMigracao" class="btn btn-success mr-2" disabled>
                        Iniciar Migração
                    </button>
                    <button type="button" id="btnPausarMigracao" class="btn btn-warning mr-2" disabled>Pausar</button>
                    <button type="button" id="btnCancelarMigracao" class="btn btn-danger" disabled>Cancelar</button>
                </div>
            </div>

        </form>
    </div>
</div>