<?php
/**
 * src/View/partials/footer_view.php
 *
 * Contém o HTML do rodapé, todos os modais da aplicação e a inclusão dos scripts.
 * As variáveis que ele utiliza ($systems, $allClients) são fornecidas pelo Controller.
 */
?>
    </div></section></div>
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Versão</b> 1.0
        </div>
        <strong>&copy; 2025 Migrador PHP.</strong> Todos os direitos reservados.
    </footer>

    <!-- ======================================================= -->
    <!-- MODAL DE CADASTRO DOS ITENS                             -->
    <!-- ======================================================= -->
    <div class="modal fade" id="modalAddItem" tabindex="-1" aria-labelledby="modalAddLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formAddItem" class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAddLabel">Adicionar Novo Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="createItemType" class="form-label">Tipo de Cadastro:</label>
                        <select class="form-control" id="createItemType" name="type" required>
                            <option value="system" selected>Sistema</option>
                            <option value="client">Cliente</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="createItemName" class="form-label">Nome do Novo Item:</label>
                        <input type="text" class="form-control" id="createItemName" name="name" placeholder="Digite o nome" required>
                    </div>

                    <div id="clientCreationOptions" class="d-none">
                        <div class="form-group mb-3">
                            <label class="form-label" for="createSystemId">Vincular ao Sistema:</label>
                            <select class="form-control" id="createSystemId" name="systemId">
                                <?php foreach(($viewData['systems'] ?? []) as $sys): ?>
                                    <option value="<?= $sys->id ?>"><?= htmlspecialchars($sys->nome) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label" for="createTemplateId">Template Padrão (Opcional):</label>
                            <select class="form-control" id="createTemplateId" name="template_id">
                                <option value="">-- Começar com configuração em branco --</option>
                                </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
    
    
    <!-- ======================================================= -->
    <!-- MODAL DE EDITAR OS ITENS                                -->
    <!-- ======================================================= -->
    <div class="modal fade" id="modalEditItem" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formEditItem" class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel">Editar Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editItemType">1. O que você quer editar?</label>
                        <select class="form-control" id="editItemType">
                            <option value="" selected>-- Selecione o tipo --</option>
                            <option value="system">Sistema</option>
                            <option value="client">Cliente</option>
                        </select>
                    </div>

                    <div id="editSystemForm" class="d-none">
                        <div class="form-group mt-3">
                            <label for="editSystemSelect">2. Selecione o Sistema</label>
                            <select class="form-control" id="editSystemSelect">
                                <option value="">-- Selecione --</option>
                                <?php foreach($viewData['systems'] as $sys): ?>
                                    <option value="<?= $sys->id ?>"><?= htmlspecialchars($sys->nome) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div id="editClientForm" class="d-none">
                        <div class="form-group mt-3">
                            <label for="editClientSelect">2. Selecione o Cliente</label>
                            <select class="form-control" id="editClientSelect">
                                <option value="">-- Carregando... --</option>
                            </select>
                        </div>
                        <div id="editClientSystemGroup" class="form-group mt-3 d-none">
                            <label for="editClientNewSystemSelect">3. Mover para o Sistema</label>
                            <select class="form-control" id="editClientNewSystemSelect" name="systemId">
                                <?php foreach($viewData['systems'] as $sys): ?>
                                    <option value="<?= $sys->id ?>"><?= htmlspecialchars($sys->nome) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div id="editNameGroup" class="form-group mt-3 d-none">
                        <label for="editNome">Nome:</label>
                        <input type="text" class="form-control" id="editNome" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" disabled>Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- MODAL DE DELETE OS ITENS                                -->
    <!-- ======================================================= -->
    <div class="modal fade" id="modalDeleteItem" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-danger">
                    <h5 class="modal-title" id="modalDeleteLabel"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Confirmar Exclusão</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="deleteItemType">1. O que você quer excluir?</label>
                        <select class="form-control" id="deleteItemType">
                            <option value="" selected>-- Selecione o tipo --</option>
                            <option value="system">Sistema</option>
                            <option value="client">Cliente</option>
                        </select>
                    </div>

                    <div id="deleteSystemForm" class="d-none">
                        <div class="form-group mt-3">
                            <label for="deleteSystemSelect">2. Selecione o Sistema</label>
                            <select class="form-control" id="deleteSystemSelect">
                                <option value="">-- Selecione --</option>
                                <?php foreach($viewData['systems'] as $sys): ?>
                                    <option value="<?= $sys->id ?>" data-name="<?= htmlspecialchars($sys->nome) ?>"><?= htmlspecialchars($sys->nome) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div id="deleteClientForm" class="d-none">
                        <div class="form-group mt-3">
                            <label for="deleteClientSelect">2. Selecione o Cliente</label>
                            <select class="form-control" id="deleteClientSelect">
                                <option value="">-- Selecione o tipo primeiro --</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <p id="deleteItemMessage" class="alert alert-secondary">Selecione um item para confirmar a exclusão.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnConfirmDeleteItem" class="btn btn-danger" disabled>Confirmar Exclusão</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ======================================================= -->
    <!-- MODAL TO DO (CADASTRO)                                  -->
    <!-- ======================================================= -->
    <div class="modal fade" id="todoModal" tabindex="-1" aria-labelledby="todoModalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <form id="todoForm" class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="todoModalTitle">Novo Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="todoId">

                    <div class="mb-3">
                        <label for="todoTitle" class="form-label">Descrição</label>
                        <input type="text" class="form-control" name="title" id="todoTitle" required>
                    </div>

                    <div class="mb-3">
                        <label for="todoCliente" class="form-label">Vincular ao Cliente (Opcional)</label>
                        <select class="form-select" name="cliente_id" id="todoCliente">
                            <option value="">— Nenhum —</option>
                            <?php 
                            // Acessa a lista de clientes preparada pelo Controller via $viewData
                            if (isset($viewData['allClients'])): 
                                foreach ($viewData['allClients'] as $id => $nome): 
                            ?>
                                    <option value="<?= $id ?>"><?= htmlspecialchars($nome) ?></option>
                            <?php 
                                endforeach; 
                            endif; 
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="todoTempo" class="form-label">Tempo previsto</label>
                        <input type="text" class="form-control" name="tempo_prev" id="todoTempo" placeholder="ex: 2h, 30min">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>


    <!-- ======================================================= -->
    <!-- MODAL SAIR                                              -->
    <!-- ======================================================= -->
    <div class="modal fade" id="modalLogout" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white">

                <div class="modal-header border-0">
                    <h5 class="modal-title" id="logoutModalLabel"><i class="fas fa-sign-out-alt me-2"></i>Confirmação</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    Tem certeza de que deseja encerrar a sessão?
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    
                    <a href="<?= htmlspecialchars($viewData['basePath']) ?>/logout" class="btn btn-danger">
                        Sair
                    </a>
                </div>

            </div>
        </div>
    </div>

    

    <!-- ======================================================= -->
    <!-- MODAL CADASTRAR USUARIO                                 -->
    <!-- ======================================================= -->
    <div class="modal fade" id="modalAddUser" tabindex="-1">
        <div class="modal-dialog"><form id="formAddUser" class="modal-content bg-dark text-white">
            <div class="modal-header"><h5 class="modal-title">Adicionar Novo Usuário</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nome Completo</label><input type="text" class="form-control" name="nome" required></div>
                <div class="mb-3"><label class="form-label">Login</label><input type="text" class="form-control" name="login" required></div>
                <div class="mb-3"><label class="form-label">Senha</label><input type="password" class="form-control" name="senha" required></div>
                <div class="mb-3"><label class="form-label">Nível</label><select class="form-select" name="nivel" required><option value="consultor">Consultor</option><option value="tecnico">Técnico</option><option value="admin">Admin</option></select></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Salvar</button></div>
        </form></div>
    </div>
    

    <!-- ======================================================= -->
    <!-- CONFIRMAÇÃO EXCLUSÃO TO-DO                              -->
    <!-- ======================================================= -->
    <div class="modal fade" id="modalDeleteTodo" tabindex="-1" aria-labelledby="modalDeleteTodoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white">

                <div class="modal-header border-danger">
                    <h5 class="modal-title" id="modalDeleteTodoLabel"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Confirmar Exclusão</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <p id="deleteTodoMessage"></p>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnConfirmDeleteTodo" class="btn btn-danger">Excluir Tarefa</button>
                </div>

            </div>
        </div>
    </div>


    <!-- ======================================================= -->
    <!-- SUB-CONSULTA AVANÇADA                                   -->
    <!-- ======================================================= -->
    <div class="modal fade" id="modalAdvancedMapping" tabindex="-1" aria-labelledby="modalAdvancedMappingLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAdvancedMappingLabel">Mapeamento Avançado de Campo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="currentMappingField">
                    
                    <div class="row">
                        <div class="col-md-6"> 
                            <div class="form-group">
                                <label for="mappingType">Tipo de Mapeamento</label>
                                <select class="form-control" id="mappingType">
                                    <option value="source_column">Coluna da Origem (Simples)</option>
                                    <option value="sub_query">Sub-Consulta (Busca em outra tabela)</option>
                                    <option value="de_para_lookup">Busca De-Para (Chave Estrangeira)</option>
                                    <option value="sub_query_de_para_lookup">Busca De-Para com Sub-Consulta</option>
                                    <option value="custom_de_para">De-Para Customizado</option>
                                    <option value="generate_acronym">Gerar Sigla</option>
                                    <option value="auto_increment">ID Automático</option>
                                    <option value="datetime">Data e Hora Atual</option>
                                    <option value="default">Valor Fixo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mapping-options" id="options_source_column">
                        <div class="form-group">
                            <label>Nome da Coluna na Query de Origem</label>
                            <input type="text" class="form-control" data-rule-key="value" placeholder="Ex: NOME_CLIENTE">
                        </div>
                    </div>

                    <div class="mapping-options d-none" id="options_sub_query">
                        <div class="form-group mb-3"><label>Sub-Query SQL (use '?' como placeholder)</label><textarea class="form-control" rows="3" data-rule-key="sql" placeholder="SELECT telefone FROM contatos WHERE id_pessoa = ?"></textarea></div>
                        <div class="form-group"><label>Coluna da Query Principal para usar na Sub-Query (Chave da Busca)</label><input type="text" class="form-control" data-rule-key="source_key" placeholder="Ex: ID_PESSOA_ORIGEM"></div>
                    </div>

                    <div class="mapping-options d-none" id="options_de_para_lookup">
                        <div class="form-group mb-3"><label>Coluna da Query Principal (que contém a chave de origem)</label><input type="text" class="form-control" data-rule-key="source_key" placeholder="Ex: ID_CONTATO_ORIGEM"></div>
                        <div class="form-group"><label>Nome da Tabela de Origem (como está no De-Para)</label><input type="text" class="form-control" data-rule-key="source_table" placeholder="Ex: cad_contatos_legado"></div>
                    </div>

                    <div class="mapping-options d-none" id="options_sub_query_de_para_lookup">
                        <div class="alert alert-info">
                            Use esta opção para chaves estrangeiras indiretas. Primeiro, uma sub-query na **origem** busca uma chave, e depois essa chave é usada para uma busca na tabela **de-para** do destino.
                        </div>
                        <div class="form-group mb-3">
                            <label>1. Sub-Query na Origem (use '?')</label>
                            <textarea class="form-control" rows="2" data-rule-key="sql" placeholder="Ex: SELECT id_legado FROM pessoas WHERE email = ?"></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label>2. Coluna da Query Principal para usar na Sub-Query</label>
                            <input type="text" class="form-control" data-rule-key="sub_query_key" placeholder="Ex: EMAIL_DO_CONTATO">
                        </div>
                        <div class="form-group">
                            <label>3. Nome da Tabela de Origem no De-Para</label>
                            <input type="text" class="form-control" data-rule-key="source_table" placeholder="Ex: cad_pessoas_legado">
                        </div>
                    </div>

                    <div class="mapping-options d-none" id="options_custom_de_para">
                        <div class="form-group mb-3">
                            <label>Coluna da Query Principal (Chave de Entrada)</label>
                            <input type="text" class="form-control" data-rule-key="source_key" placeholder="Ex: STATUS_ORIGEM">
                        </div>
                        <div class="form-group">
                            <label>Regras de Mapeamento (Entrada => Saída)</label>
                            <textarea class="form-control" rows="5" data-rule-key="rules" placeholder="Use o formato: 'Entrada1' => 'Saida1',&#10;'Entrada2' => 2,&#10;'Entrada3' => null"></textarea>
                            <small class="form-text text-muted">Use o formato <code>'valor_origem' => 'valor_destino',</code> cada regra em uma linha.</small>
                        </div>
                    </div>

                    <div class="mapping-options d-none" id="options_generate_acronym">
                        <div class="form-group">
                            <label>Nome da Coluna de Origem para usar como base do texto</label>
                            <input type="text" class="form-control" data-rule-key="value" placeholder="Ex: NOME_CLIENTE">
                        </div>
                    </div>
                    
                    <div class="mapping-options d-none" id="options_auto_increment">
                        <div class="form-group">
                            <label>Valor Inicial (se a tabela de destino estiver vazia)</label>
                            <input type="number" class="form-control" data-rule-key="start_value" value="5000">
                        </div>
                    </div>

                    <div class="mapping-options d-none" id="options_default">
                        <div class="form-group">
                            <label>Valor Fixo a ser Inserido</label>
                            <input type="text" class="form-control" data-rule-key="value" placeholder="Ex: Ativo">
                        </div>
                    </div>

                    <div class="mapping-options d-none" id="options_datetime">
                        <p class="text-muted">Nenhuma opção necessária. O sistema irá inserir a data e hora do servidor no momento da migração.</p>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnSaveMapping" class="btn btn-primary">Salvar Regra</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" rel="stylesheet">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- ======================================================= -->
    <!-- INJETANDO O CAMINHO BASE PARA O JAVASCRIPT              -->
    <!-- ======================================================= -->
    <script>
        // Define uma configuração global que nosso JS pode acessar
        const AppConfig = {
            basePath: '<?= htmlspecialchars($viewData['basePath']) ?>',
            apiUrl: '<?= htmlspecialchars($viewData['basePath']) ?>/api'
        };
    </script>

    <script src="<?= htmlspecialchars($viewData['basePath']) ?>/assets/js/migracao.js"></script>

</body>
</html>