/**
 * Exibe uma notificação "Toast" no canto da tela usando o plugin nativo do AdminLTE 3.
 * VERSÃO FINAL COMPLETA.
 * @param {'success'|'danger'|'warning'|'info'} type - O tipo do alerta.
 * @param {string} title - O título do alerta.
 * @param {string} message - A mensagem principal.
 */
function showAlert(type, title, message) {
    const settings = {
        success: { class: 'bg-success', icon: 'fas fa-check' },
        danger:  { class: 'bg-danger',  icon: 'fas fa-ban' },
        warning: { class: 'bg-warning', icon: 'fas fa-exclamation-triangle' },
        info:    { class: 'bg-info',    icon: 'fas fa-info-circle' }
    };

    const config = settings[type] || settings.info;

    // Chama o plugin do AdminLTE passando um objeto de opções completo
    $(document).Toasts('create', {
        class: config.class,    // Define a cor de fundo
        title: title,           // O título em negrito
        body: message,          // A mensagem principal
        icon: config.icon + ' fa-lg', // O ícone
        autohide: true,         // Faz o toast desaparecer sozinho
        delay: 5000,            // Tempo em milissegundos
        position: 'topRight',   // Posição no canto superior direito
        fade: true,             // Usa animação de fade
        close: true             // <-- A LINHA MÁGICA: Adiciona o botão 'x' funcional!
    });
}

function formatRuleForDisplayJS(rule) {
    if (typeof rule !== 'object' || !rule.type) {
        return rule || ''; // Retorna a própria string se não for um objeto de regra
    }
    switch (rule.type) {
        case 'source_column': return rule.value || '';
        case 'sub_query': return '[Sub-Consulta]';
        case 'generate_acronym': return `[Gerar Sigla]`;
        case 'datetime': return '[Data/Hora Atual]';
        case 'default': return `[Valor Fixo: ${rule.value}]`;
        default: return '[Regra Customizada]';
    }
}


$(function() {

    // --- LÓGICA DO MODAL DE ADICIONAR USUARIO ---
    $('#formAddUser').off('submit').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.post(AppConfig.apiUrl + '/users', $(this).serialize(), 'json')
        .done(res => {
            if(res.success) {
                // 1. Esconde o modal
                $('#modalAddUser').modal('hide');
                // 2. Mostra o alerta de sucesso
                showAlert('success', 'Sucesso!', res.message || 'Usuário criado com sucesso!');
                // 3. Recarrega a página após 1.5s para que o novo usuário apareça na lista
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', 'Erro', res.message);
            }
        })
        .fail(xhr => showAlert('danger','Erro', xhr.responseJSON?.message))
        .always(() => $btn.prop('disabled', false).html('Salvar'));
    });

    // --- LÓGICA DO BOTÃO IMPORTAR JSON (VERSÃO FINAL COM FUNÇÃO RECURSIVA) ---
    const $importBtn = $('#btnImportarJSON');
    const $fileInput = $('#importarJsonFile');
    const $mainForm = $('#formTemplate'); // Cache do formulário principal

    $importBtn.on('click', function() {
        $fileInput.click();
    });

    $fileInput.on('change', function(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const importedConfig = JSON.parse(e.target.result);
                
                if (!confirm('Isto irá preencher o formulário com os dados do arquivo. Deseja continuar?')) {
                    return;
                }

                // Função recursiva para preencher o formulário
                function populateForm(dataObject, parentKey = '') {
                    for (const key in dataObject) {
                        if (dataObject.hasOwnProperty(key)) {
                            const value = dataObject[key];
                            // Constrói o 'name' do campo (ex: conexao[origem][ip])
                            const name = parentKey ? `${parentKey}[${key}]` : key;

                            if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
                                // Se o valor é outro objeto, chama a função novamente para o próximo nível
                                populateForm(value, name);
                            } else {
                                // Se for um valor final, encontra o campo e o preenche
                                const $field = $mainForm.find(`[name="${name}"]`);
                                
                                if ($field.is(':checkbox')) {
                                    $field.prop('checked', !!value);
                                } else if ($field.hasClass('mapping-rule')) {
                                    const $preview = $field.siblings('.mapping-preview');
                                    const ruleJson = JSON.stringify(value);
                                    $field.val(ruleJson);
                                    $preview.val(formatRuleForDisplayJS(value));
                                } else {
                                    $field.val(value);
                                }
                            }
                        }
                    }
                }

                // Inicia o preenchimento a partir do objeto principal
                populateForm(importedConfig);
                
                showAlert('success', 'Sucesso!', 'Os campos foram preenchidos com os dados do arquivo JSON.');
                
                // Dispara o evento de 'change' para que o auto-save seja acionado
                $mainForm.trigger('change'); 

            } catch (error) {
                showAlert('danger', 'Erro de Arquivo', 'O arquivo selecionado não é um JSON válido.');
            }
        };

        reader.readAsText(file);
        $(this).val('');
    });

    // --- LÓGICA DO BOTÃO EXPORTAR JSON ---
    $('#btnExportarJSON').on('click', function() {
        const clientId = $(this).data('client-id');
        if (!clientId) {
            showAlert('danger', 'Erro', 'Não foi possível identificar o cliente.');
            return;
        }

        // Constrói a URL de download
        const downloadUrl = `${AppConfig.basePath}/export-config/${clientId}`;
        
        // Redireciona o navegador para a URL, o que iniciará o download
        window.location.href = downloadUrl;
    });

    // ==========================================================
    // LÓGICA DO MODAL DE ADICIONAR ITEM (VERSÃO COM TEMPLATES)
    // ==========================================================
    if ($('#modalAddItem').length) {
        
        const $modal = $('#modalAddItem');
        const $form = $modal.find('#formAddItem');
        const $itemTypeSelect = $modal.find('#createItemType');
        const $clientOptions = $modal.find('#clientCreationOptions'); // Div que agrupa as opções de cliente
        const $templateSelect = $modal.find('#createTemplateId');
        let templatesLoaded = false; // Flag para carregar os templates apenas uma vez

        // Reseta o formulário quando o modal for aberto
        $modal.on('show.bs.modal', function () {
            $form[0].reset();
            $itemTypeSelect.trigger('change');
        });

        // Mostra/esconde as opções de cliente com base no tipo
        $itemTypeSelect.on('change', function() {
            const isClient = $(this).val() === 'client';
            $clientOptions.toggleClass('d-none', !isClient);

            // Se for cliente e os templates ainda não foram carregados, busca via API
            if (isClient && !templatesLoaded) {
                $templateSelect.html('<option>Carregando templates...</option>');
                $.getJSON(AppConfig.apiUrl + '/templates')
                .done(response => {
                    if(response.success) {
                        $templateSelect.empty().append('<option value="">-- Começar com configuração em branco --</option>');
                        response.data.forEach(tpl => {
                            $templateSelect.append(`<option value="${tpl.id}">${tpl.nome}</option>`);
                        });
                        templatesLoaded = true;
                    }
                });
            }
        });

        // Lida com a submissão do formulário
        $form.off('submit').on('submit', function(e) {
            e.preventDefault();
            const $submitBtn = $(this).find('button[type="submit"]');
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');

            $.post(AppConfig.apiUrl + '/items', $(this).serialize(), 'json')
            .done(response => {
                if (response.success) {
                    $modal.modal('hide');
                    showAlert('success', 'Sucesso!', response.message);
                    setTimeout(() => location.reload(), 1500); 
                } else {
                    showAlert('danger', 'Erro', response.message);
                }
            })
            .fail(xhr => showAlert('danger','Erro', xhr.responseJSON?.message || 'Falha de comunicação.'))
            .always(() => $submitBtn.prop('disabled', false).html('Salvar'));
        });
    }

    // ==========================================================
    // LÓGICA DO MODAL DE EDITAR ITEM (VERSÃO FINAL)
    // ==========================================================
    if ($('#modalEditItem').length) {

        // Cache dos elementos do DOM para melhor performance
        const $modal = $('#modalEditItem');
        const $form = $('#formEditItem');
        const $itemType = $('#editItemType');
        const $systemForm = $('#editSystemForm');
        const $clientForm = $('#editClientForm');
        const $systemSelect = $('#editSystemSelect');
        const $clientSelect = $('#editClientSelect');
        const $nameGroup = $('#editNameGroup');
        const $nameInput = $('#editNome'); // Corrigido para corresponder à variável já em cache
        const $clientSystemGroup = $('#editClientSystemGroup');
        const $clientNewSystemSelect = $('#editClientNewSystemSelect');
        const $submitBtn = $form.find('button[type="submit"]');
        let editClientsLoaded = false; // Flag para carregar a lista de clientes apenas uma vez

        // Reseta tudo para o estado inicial quando o modal é aberto
        $modal.on('show.bs.modal', function() {
            $form[0].reset();
            $itemType.val('').trigger('change');
        });

        // Evento principal: quando o TIPO de item (Sistema/Cliente) é escolhido
        $itemType.on('change', function() {
            const type = $(this).val();
            $systemForm.add($clientForm).add($nameGroup).add($clientSystemGroup).addClass('d-none');
            $submitBtn.prop('disabled', true);

            if (type === 'system') {
                $systemForm.removeClass('d-none');
            } else if (type === 'client') {
                $clientForm.removeClass('d-none');
                // Busca a lista de todos os clientes via API, se ainda não foi carregada
                if (!editClientsLoaded) {
                    $clientSelect.html('<option>Carregando...</option>');
                    $.getJSON(AppConfig.apiUrl + '/clients').done(res => {
                        if (res.success) {
                            let opts = '<option value="">-- Selecione o Cliente --</option>';
                            res.data.forEach(c => opts += `<option value="${c.id}">${c.nome}</option>`);
                            $clientSelect.html(opts);
                            editClientsLoaded = true;
                        }
                    });
                }
            }
        });

        // Evento: quando um SISTEMA específico é selecionado para edição
        $systemSelect.on('change', function(){
            const name = $(this).find('option:selected').text();
            const hasValue = !!$(this).val();
            $nameGroup.toggleClass('d-none', !hasValue);
            $nameInput.val(name); // Usa a variável em cache
            $submitBtn.prop('disabled', !hasValue);
        });

        // Evento: quando um CLIENTE específico é selecionado para edição
        $clientSelect.on('change', function(){
            const clientId = $(this).val();
            $nameGroup.add($clientSystemGroup).addClass('d-none');
            $submitBtn.prop('disabled', true);
            if (!clientId) return;

            // Busca os detalhes do cliente para preencher os campos
            $.getJSON(`${AppConfig.apiUrl}/clients/${clientId}`).done(res => {
                if(res.success && res.data) {
                    $nameInput.val(res.data.nome);
                    $clientNewSystemSelect.val(res.data.sistema_id);
                    $nameGroup.removeClass('d-none');
                    $clientSystemGroup.removeClass('d-none');
                    $submitBtn.prop('disabled', false);
                }
            });
        });

        // Evento: submissão do formulário de EDIÇÃO
        $form.off('submit').on('submit', function(e) {
            e.preventDefault();
            const type = $itemType.val();
            let dataPayload;

            if (type === 'system') {
                dataPayload = { type: 'system', id: $systemSelect.val(), name: $nameInput.val() };
            } else if (type === 'client') {
                dataPayload = { type: 'client', id: $clientSelect.val(), name: $nameInput.val(), systemId: $clientNewSystemSelect.val() };
            } else { return; }

            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
            $.ajax({ url: AppConfig.apiUrl + '/items', method: 'PUT', data: dataPayload, dataType: 'json' })
                .done(res => {
                    if (res.success) {
                        showAlert('success', 'Sucesso!', 'Item atualizado com sucesso!');
                        $modal.modal('hide');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('danger', 'Erro', res.message);
                    }
                })
                .fail(xhr => {
                    const errorMsg = xhr.responseJSON?.message || 'Falha de comunicação com o servidor.';
                    showAlert('danger', 'Erro ao Editar', errorMsg);
                })
                .always(() => $submitBtn.prop('disabled', false).html('Salvar Alterações'));
        });
    }

    // ==========================================================
    // LÓGICA DO MODAL DE EXCLUIR ITEM
    // ==========================================================
    const $deleteModal = $('#modalDeleteItem');
    const $deleteItemType = $('#deleteItemType');
    const $deleteSystemForm = $('#deleteSystemForm');
    const $deleteClientForm = $('#deleteClientForm');
    const $deleteSystemSelect = $('#deleteSystemSelect');
    const $deleteClientSelect = $('#deleteClientSelect');
    const $deleteMessage = $('#deleteItemMessage');
    const $deleteSubmitBtn = $('#btnConfirmDeleteItem');

    // Reseta o modal de exclusão quando ele é aberto
    $deleteModal.on('show.bs.modal', function() {
        $deleteItemType.val('').trigger('change');
    });

    // Evento principal: quando o TIPO de item a ser excluído é escolhido
    $deleteItemType.on('change', function() {
        const type = $(this).val();
        $deleteSystemForm.add($deleteClientForm).addClass('d-none');
        $deleteSubmitBtn.prop('disabled', true);
        $deleteMessage.text('Selecione um item para excluir.').attr('class', 'alert alert-secondary');
        
        if (type === 'system') {
            $deleteSystemForm.removeClass('d-none').find('select').val('');
        } else if (type === 'client') {
            $deleteClientForm.removeClass('d-none').find('select').val('');
            // Reutiliza a lista de clientes que a aba de Edição já carregou
            if ($('#editClientSelect').data('loaded') === true) {
                $deleteClientSelect.html($('#editClientSelect').html());
            } else {
                // Se não foi carregada, busca agora (caso o usuário só abra a aba de excluir)
                $deleteClientSelect.html('<option>Carregando...</option>');
                $.getJSON(AppConfig.apiUrl + '/clients').done(res => {
                    if (res.success) {
                        let opts = '<option value="">-- Selecione o Cliente --</option>';
                        res.data.forEach(c => opts += `<option value="${c.id}" data-name="${c.nome}">${c.nome}</option>`);
                        $deleteClientSelect.html(opts);
                    }
                });
            }
        }
    });

    // Função para atualizar a mensagem de confirmação
    function updateDeleteMessage() {
        const type = $deleteItemType.val();
        let name = '';
        let hasValue = false;
        let msg = 'Selecione um item para excluir.';
        let alertClass = 'alert-secondary';

        if (type === 'system' && $deleteSystemSelect.val()) {
            name = $deleteSystemSelect.find('option:selected').data('name');
            msg = `Tem certeza que deseja excluir o sistema <strong>${name}</strong>? Todos os seus clientes e configurações também serão apagados!`;
            alertClass = 'alert-danger';
            hasValue = true;
        } else if (type === 'client' && $deleteClientSelect.val()) {
            name = $deleteClientSelect.find('option:selected').data('name');
            msg = `Tem certeza que deseja excluir o cliente <strong>${name}</strong>?`;
            alertClass = 'alert-warning';
            hasValue = true;
        }

        $deleteMessage.html(msg).attr('class', `alert ${alertClass}`);
        $deleteSubmitBtn.prop('disabled', !hasValue);
    }

    // Anexa o evento de atualização da mensagem aos dois seletores
    $deleteSystemSelect.on('change', updateDeleteMessage);
    $deleteClientSelect.on('change', updateDeleteMessage);

    // Evento final: clique no botão de confirmação para excluir
    $deleteSubmitBtn.off('click').on('click', function() {
        const type = $deleteItemType.val();
        const id = (type === 'system') ? $deleteSystemSelect.val() : $deleteClientSelect.val();
        
        if (!id) return;

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Excluindo...');

        $.ajax({ 
            url: AppConfig.apiUrl + '/items', 
            method: 'DELETE', 
            data: { type, id }, // Os dados são enviados aqui
            dataType: 'json' 
        })
        .done(function(res) {
            if(res.success) {
                showAlert('success', 'Sucesso!', res.message);
                
                // =============================================
                // LÓGICA DE REDIRECIONAMENTO INTELIGENTE CORRIGIDA
                // =============================================
                const currentClientIdOnPage = $('#cliente_id').val(); // Pega o ID da página atual

                // Usamos as variáveis 'id' e 'type' que já temos neste escopo
                if (currentClientIdOnPage && type === 'client' && id == currentClientIdOnPage) {
                    // Se deletamos o cliente da página em que estamos, vamos para o dashboard
                    showAlert('info', 'Redirecionando...', 'O cliente que você estava vendo foi excluído.');
                    setTimeout(() => {
                        window.location.href = AppConfig.basePath + '/dashboard';
                    }, 2000);
                } else {
                    // Caso contrário, apenas recarregamos para atualizar o menu lateral
                    setTimeout(() => location.reload(), 1500);
                }
            } else {
                showAlert('danger', 'Erro!', res.message);
            }
        })
        .fail(xhr => showAlert('danger','Erro ao Excluir', xhr.responseJSON?.message))
        .always(() => {
            $btn.prop('disabled', false).html('Confirmar Exclusão');
            $deleteModal.modal('hide');
        });
    });





    // ==========================================================
    // LÓGICA DO DASHBOARD (KPIs, Logs e Filtros) - VERSÃO FINAL
    // ==========================================================

    // Usamos um ID na página do dashboard para garantir que este código só rode lá.
    // Adicione <div id="dashboard-page-identifier" style="display: none;"></div>
    // ao seu arquivo dashboard_view.php se ainda não tiver.
    if ($('#dashboard-page-identifier').length) {

        // Declara a variável do gráfico fora da função para que ela persista entre as chamadas
        let migrationChart = null;

        /**
         * Função central que busca dados da API e atualiza TODOS os componentes do dashboard.
         * @param {object} filters - Os dados do formulário a serem enviados como parâmetros GET.
         */
        function fetchDashboardData(filters) {
            const $filterBtn = $('#filterForm button[type="submit"]');
            $filterBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.getJSON(AppConfig.apiUrl + '/dashboard-data', filters)
                .done(response => {
                    if (response.success) {
                        const data = response.data;
                        
                        // 1. Preenche os KPIs
                        $('#totalMigracoes').text(data.total_migracoes || 0);
                        $('#registrosMigrados').text(data.registros_migrados || 0);
                        $('#taxaSucesso').text((data.taxa_sucesso || 0) + '%');
                        $('#tempoMedio').text((data.tempo_medio || 0) + ' seg');

                        // 2. Preenche a tabela de logs
                        const $logsTableBody = $('#logsTableBody');
                        let logsHtml = '';
                        if (data.logs && data.logs.length) {
                            data.logs.forEach(log => {
                                const nivelBadges = { 'INFO': 'bg-primary', 'DEBUG': 'bg-secondary', 'WARN': 'bg-warning', 'ERROR': 'bg-danger' };
                                const badgeClass = nivelBadges[log.nivel.toUpperCase()] || 'bg-light';
                                logsHtml += `<tr><td><span class="badge ${badgeClass}">${log.nivel}</span></td><td>${log.evento}</td><td>${log.mensagem}</td></tr>`;
                            });
                        } else {
                            logsHtml = '<tr><td colspan="3" class="text-center text-muted py-4">Nenhum log encontrado.</td></tr>';
                        }
                        $logsTableBody.html(logsHtml);
                        
                        // 3. Renderiza ou atualiza o gráfico
                        const chartData = data.chartData;
                        const chartCanvas = document.getElementById('migrationChart');
                        if (chartCanvas) {
                            if (migrationChart) {
                                // Se o gráfico já existe, apenas atualiza os dados
                                migrationChart.data.labels = chartData.labels;
                                migrationChart.data.datasets[0].data = chartData.sourceData;
                                migrationChart.data.datasets[1].data = chartData.destData;
                                migrationChart.update();
                            } else {
                                // Se não existe, cria o gráfico pela primeira vez
                                const ctx = chartCanvas.getContext('2d');
                                migrationChart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: chartData.labels,
                                        datasets: [
                                            { label: 'Origem', backgroundColor: 'rgba(210, 214, 222, 0.8)', data: chartData.sourceData },
                                            { label: 'Destino', backgroundColor: 'rgba(0, 123, 255, 0.8)', data: chartData.destData }
                                        ]
                                    },
                                    options: {
                                        maintainAspectRatio: false,
                                        responsive: true,
                                        indexAxis: 'x', // Garante que as barras sejam verticais (padrão)
                                        elements: {
                                            bar: {
                                                barPercentage: 0.5, // Ajuste este valor (0 a 1)
                                                categoryPercentage: 0.8 // Opcional: Ajusta o espaço ocupado pelos grupos de barras na categoria
                                            }
                                        }
                                    }
                                });
                            }
                        }
                    } else {
                        showAlert('warning', 'Atenção', response.message);
                    }
                })
                .fail(() => showAlert('danger', 'Erro', 'Não foi possível carregar os dados do dashboard.'))
                .always(() => {
                    $filterBtn.prop('disabled', false).html('Filtrar');
                });
        }

        // --- Gatilhos de Eventos ---

        // 1. Carga inicial dos dados quando a página carrega
        fetchDashboardData({});

        // 2. Evento de submissão do formulário de filtro UNIFICADO
        $('#filterForm').on('submit', function (e) {
            e.preventDefault(); // Impede o recarregamento da página
            fetchDashboardData($(this).serialize()); // Busca TODOS os dados com os filtros do formulário
        });
        
        // Evento para o filtro de cliente específico do gráfico
        $('#chartClientFilter').on('change', function() {
            // Pega o valor do filtro principal e adiciona/sobrescreve o cliente_id
            const mainFilters = $('#filterForm').serialize();
            const clientFilter = $(this).serialize(); // ex: "cliente_id=5"
            fetchDashboardData(mainFilters + '&' + clientFilter);
        });

    } // Fim do if ($('#dashboard-page-identifier').length)



    // Verifica se o elemento da lista de To-Do existe na página atual
    // ==========================================================
    // LÓGICA DA TO-DO LIST (VERSÃO FINAL COMPLETA)
    // ==========================================================

    // Verifica se estamos na página do dashboard que contém a To-Do List
    if ($('#todoList').length) {

        const $todoList = $('#todoList');
        let todoListNeedsRefresh = false; // Flag para controlar a atualização da lista

        // ----------------------------------------------------------
        // 1. FUNÇÃO PRINCIPAL DE RENDERIZAÇÃO
        // ----------------------------------------------------------
        function renderTodos() {
            $.getJSON(AppConfig.apiUrl + '/todos')
                .done(response => {
                    $todoList.empty();

                    if (!response.success || !response.todos || !response.todos.length) {
                        $todoList.html('<li id="todoEmpty" class="text-center text-muted pt-4">Nenhum item</li>');
                        $('#todoFooter').hide();
                        return;
                    }

                    $('#todoFooter').show();
                    response.todos.forEach(todo => {
                        const doneClass = todo.completed ? 'todo-done' : '';
                        const checkedAttr = todo.completed ? 'checked' : '';
                        const badge = todo.tempoPrev ? `<small class="badge bg-info">${todo.tempoPrev}</small>` : '';
                        const listItem = `
                            <li data-id="${todo.id}" data-title="${todo.title}" data-tempo="${todo.tempoPrev ?? ''}" data-cliente="${todo.clienteId ?? ''}" class="${doneClass}">
                                <span class="todo-handle"><i class="fas fa-grip-vertical"></i></span>
                                <div class="icheck-primary d-inline-block me-2">
                                    <input type="checkbox" ${checkedAttr} class="todo-toggle" id="todoChk${todo.id}">
                                    <label for="todoChk${todo.id}"></label>
                                </div>
                                <span class="text">${todo.title}</span>
                                ${badge}
                                <div class="tools">
                                    <i class="fas fa-edit text-warning todo-edit" title="Editar"></i>
                                    <i class="fas fa-trash text-danger todo-del" title="Excluir"></i>
                                </div>
                            </li>`;
                        $todoList.append(listItem);
                    });
                })
                .fail(() => showAlert('danger', 'Erro de Rede', 'Não foi possível carregar a lista de tarefas.'));
        }

        // ----------------------------------------------------------
        // 2. MANIPULADORES DE EVENTOS (CLICKS, SUBMIT, ETC.)
        // ----------------------------------------------------------

        // --- Modal de Adicionar/Editar ---
        $('#addTodoBtn').on('click', () => {
            $('#todoForm')[0].reset();
            $('#todoId').val('');
            $('#todoModalTitle').text('Novo Item');
            $('#todoModal').modal('show');
        });

        $todoList.on('click', '.todo-edit', function() {
            const $li = $(this).closest('li');
            $('#todoId').val($li.data('id'));
            $('#todoTitle').val($li.data('title'));
            $('#todoTempo').val($li.data('tempo'));
            $('#todoCliente').val($li.data('cliente'));
            $('#todoModalTitle').text('Editar Item');
            $('#todoModal').modal('show');
        });

        $('#todoForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const id = form.find('#todoId').val();
            const method = id ? 'PUT' : 'POST';
            const url = id ? `${AppConfig.apiUrl}/todos/${id}` : AppConfig.apiUrl + '/todos';

            $.ajax({ url: url, method: method, data: form.serialize(), dataType: 'json' })
                .done(response => {
                    if (response.success) {
                        todoListNeedsRefresh = true; // Avisa que a lista precisa ser atualizada
                        $('#todoModal').modal('hide');
                        showAlert('success', 'Sucesso!', response.message || 'Tarefa salva.');
                    } else {
                        showAlert('danger', 'Erro', response.message);
                    }
                })
                .fail(xhr => showAlert('danger', 'Erro', xhr.responseJSON?.message || 'Falha ao salvar a tarefa.'));
        });

        // --- Modal de Excluir ---
        $todoList.off('click', '.todo-del').on('click', '.todo-del', function() {
            const $li = $(this).closest('li');
            $('#deleteTodoMessage').html(`Tem certeza que deseja excluir a tarefa: <strong>"${$li.data('title')}"</strong>?`);
            $('#modalDeleteTodo').data('todo-id', $li.data('id'));
            $('#modalDeleteTodo').modal('show');
        });

        $('#btnConfirmDeleteTodo').off('click').on('click', function() {
            const id = $('#modalDeleteTodo').data('todo-id');
            if (!id) return;
            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({ url: `${AppConfig.apiUrl}/todos/${id}`, method: 'DELETE', dataType: 'json' })
                .done(() => {
                    todoListNeedsRefresh = true;
                    $('#modalDeleteTodo').modal('hide');
                    showAlert('success', 'Sucesso!', 'A tarefa foi excluída.');
                })
                .fail(() => showAlert('danger', 'Erro', 'Falha ao excluir a tarefa.'))
                .always(() => $btn.prop('disabled', false).html('Excluir Tarefa'));
        });

        // --- Eventos da Lista (Toggle e Sort) ---
        $todoList.on('change', '.todo-toggle', function() {
            const id = $(this).closest('li').data('id');
            $.ajax({ url: AppConfig.apiUrl + '/todos/toggle', method: 'PUT', data: { id: id } }).done(() => renderTodos());
        });

        $todoList.sortable({
            handle: '.todo-handle',
            update: function() {
                const orderData = $todoList.find('li').map((i, el) => ({ id: $(el).data('id'), ordem: i })).get();
                $.ajax({ url: AppConfig.apiUrl + '/todos/order', method: 'PUT', contentType: 'application/json', data: JSON.stringify({ rows: orderData }) });
            }
        });

        // --- Eventos de Fechamento dos Modais ---
        // Atualiza a lista somente DEPOIS que o modal de add/edit terminar de fechar
        $('#todoModal').on('hidden.bs.modal', () => {
            if (todoListNeedsRefresh) {
                renderTodos();
                todoListNeedsRefresh = false;
            }
        });
        
        // Atualiza a lista somente DEPOIS que o modal de exclusão terminar de fechar
        $('#modalDeleteTodo').on('hidden.bs.modal', () => {
            if (todoListNeedsRefresh) {
                renderTodos();
                todoListNeedsRefresh = false;
            }
        });

        // ----------------------------------------------------------
        // 3. INICIALIZAÇÃO
        // ----------------------------------------------------------
        renderTodos();
    }


    // ==========================================================
    // LÓGICA BOTÃO CONSULTAR
    // ==========================================================
    // Verifica se estamos na página que contém esses componentes
    if ($('.query-preview-component').length) {

        /**
         * LÓGICA REUTILIZÁVEL PARA QUALQUER BOTÃO DE PREVIEW DE QUERY
         * Versão final que envia os dados de conexão da tela para a API.
         */
        $(document).on('click', '.btn-preview-query', function () {
            const $btn = $(this); // O botão específico que foi clicado

            // 1. Encontra os elementos RELATIVOS ao botão clicado
            const $component = $btn.closest('.query-preview-component');
            const $textarea = $component.find('.query-textarea');
            const $table = $component.find('.preview-table');
            
            const sql = $textarea.val().trim();
            if (!sql) {
                showAlert('warning', 'Atenção', 'Digite a query primeiro.');
                return;
            }
            
            // ==============================================================
            // MUDANÇA PRINCIPAL AQUI: Montamos o objeto com os dados da tela
            // ==============================================================
            const $form = $btn.closest('form'); // Encontra o formulário pai
            const sourceConfig = {
                tipo:    $form.find('[name="conexao[origem][tipo]"]').val(),
                ip:      $form.find('[name="conexao[origem][ip]"]').val(),
                porta:   $form.find('[name="conexao[origem][porta]"]').val(),
                dbName:  $form.find('[name="conexao[origem][dbName]"]').val(),
                usuario: $form.find('[name="conexao[origem][usuario]"]').val(),
                senha:   $form.find('[name="conexao[origem][senha]"]').val()
            };
            
            // Animação de "carregando"
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            // Função interna para limpar a tabela de preview específica
            const mostraPlaceholder = (msg) => {
                $table.find('thead').html('<tr><th class="text-center">Pré-visualização</th></tr>');
                $table.find('tbody').html(`<tr class="placeholder"><td>${msg}</td></tr>`);
            };

            // Chamada AJAX com o novo payload de dados
            $.post(AppConfig.apiUrl + '/query-preview', { sql, sourceConfig })
                .done(res => {
                    if (!res.success) {
                        mostraPlaceholder(res.message);
                        return;
                    }
                    const rows = res.rows || [];
                    if (!rows.length) {
                        mostraPlaceholder('A consulta não retornou nenhum resultado.');
                        return;
                    }
                    
                    // O resto da lógica para montar a tabela continua igual
                    const cols = Object.keys(rows[0]);
                    $table.find('thead').html('<tr>' + cols.map(c => `<th>${c}</th>`).join('') + '</tr>');
                    
                    const $body = $table.find('tbody').empty();
                    rows.forEach(r => {
                        let cells = '';
                        cols.forEach(c => {
                            let cellData = r[c] === null ? '' : String(r[c]);
                            if (cellData.length > 100) cellData = cellData.substring(0, 100) + '...';
                            cells += `<td>${cellData}</td>`;
                        });
                        $body.append(`<tr>${cells}</tr>`);
                    });
                })
                .fail(xhr => {
                    const errorMsg = xhr.responseJSON?.message || `Falha na requisição (${xhr.status}).`;
                    mostraPlaceholder(errorMsg);
                })
                .always(() => {
                    $btn.prop('disabled', false).html('<i class="fas fa-search"></i> Consultar');
                });
        });

        // Estado inicial da tabela quando a página carrega
        $('.preview-table').each(function() {
            const $table = $(this);
            $table.find('thead').html('<tr><th class="text-center">Pré-visualização</th></tr>');
            $table.find('tbody').html(`<tr class="placeholder"><td>Clique em “Consultar” para visualizar.</td></tr>`);
        });
    }



    // ==========================================================
    // LÓGICA DA PÁGINA DE DETALHES DO CLIENTE (VERSÃO FINAL)
    // ==========================================================
    if ($('#formTemplate').length) {

        // --- CACHE DE ELEMENTOS JQUERY ---
        const $form = $('#formTemplate');
        const $status = $('#saveStatus');
        const clientId = $('#cliente_id').val();
        const $btnIniciar = $('#btnIniciarMigracao');
        const $btnPausar = $('#btnPausarMigracao');
        const $btnCancelar = $('#btnCancelarMigracao');
        const $migrationStatus = $('#migrationStatus');
        let saveTimeout;
        let statusInterval;
        let processedTablesAlert = [];

        // --- LÓGICA DO AUTO-SAVE ---
        function debounce(func, delay) {
            return function(...args) {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

        function buildNestedObject(formData) {
            let configObject = {};
            formData.forEach(item => {
                const keys = item.name.match(/[^[\]']+/g) || [];
                if (!keys.length) return;
                let current = configObject;
                for (let i = 0; i < keys.length - 1; i++) {
                    current = current[keys[i]] = current[keys[i]] || {};
                }
                let finalValue = item.value;
                if ($form.find(`input[name="${item.name}"]`).hasClass('mapping-rule')) {
                    try { finalValue = JSON.parse(item.value); } catch(e) {}
                }
                current[keys[keys.length - 1]] = finalValue;
            });
            return configObject;
        }

        function autoSaveTemplate() {
            $status.html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
            
            const configObject = buildNestedObject($form.serializeArray());

            // ADICIONADO: Lógica para salvar o estado dos cards (aberto/fechado)
            configObject.ui_state = {}; 
            $('.card[data-card-id]').each(function() {
                const cardId = $(this).data('card-id');
                const isCollapsed = $(this).hasClass('collapsed-card');
                configObject.ui_state[cardId] = isCollapsed;
            });
            
            $.ajax({
                url: `${AppConfig.apiUrl}/client-configs/${clientId}`,
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(configObject),
                dataType: 'json'
            })
            .done(response => {
                if (response.success) {
                    $status.html('<i class="fas fa-check-circle text-success"></i> Salvo');
                } else {
                    $status.html(`<i class="fas fa-times-circle text-danger"></i> ${response.message || 'Erro ao salvar'}`);
                }
            })
            .fail(xhr => $status.html(`<i class="fas fa-times-circle text-danger"></i> ${xhr.responseJSON?.message || 'Erro de comunicação'}`));
        }

        const debouncedSave = debounce(autoSaveTemplate, 1500);
        $form.on('input change', 'input, select, textarea', debouncedSave);
        $(document).on('expanded.lte.cardwidget collapsed.lte.cardwidget', '.card[data-card-id]', debouncedSave);


        // --- LÓGICA DE CONTROLE DA MIGRAÇÃO ---
        // Habilita o botão ao carregar a página
        $btnIniciar.prop('disabled', false);

        // INICIAR MIGRAÇÃO
        $btnIniciar.off('click').on('click', function() {
            if (!confirm('Iniciar a migração para as tabelas selecionadas?')) return;

            const clientId = $('#cliente_id').val();
            const tablesToMigrate = [];
            $('.query-preview-component').each(function() {
                if ($(this).find('input[name*="[migrar]"]').is(':checked')) {
                    tablesToMigrate.push($(this).data('prefix'));
                }
            });

            if (tablesToMigrate.length === 0) {
                return showAlert('warning', 'Nenhuma Tabela', 'Nenhuma tabela foi selecionada.');
            }

            // Prepara a UI para o estado de "rodando"
            $btnIniciar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Disparando...');
            $btnCancelar.prop('disabled', false);
            
            // Dispara UMA ÚNICA requisição para o back-end com a lista de tabelas
            $.post(AppConfig.apiUrl + '/migrations/start', { 
                cliente_id: clientId, 
                tables_to_migrate: tablesToMigrate
            });
            
            // Inicia o monitoramento IMEDIATAMENTE após disparar a requisição.
            // Não esperamos mais pela resposta do .done() ou .fail() aqui.
            setTimeout(() => {
                showAlert('info', 'Processo Iniciado', 'A migração foi iniciada no servidor. Acompanhe o status.');
                checkStatus(clientId, true); 
            }, 500); // Meio segundo de delay para o backend criar o primeiro registro de 'run'
        });

        // CANCELAR MIGRAÇÃO
        $btnCancelar.off('click').on('click', function() {
            if (!confirm('Tem certeza que deseja solicitar o cancelamento da migração?')) return;
            const clientId = $('#cliente_id').val();
            $btnCancelar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cancelando...');
            $.post(AppConfig.apiUrl + '/migrations/cancel', { cliente_id: clientId })
              .done(res => { if(res.success) showAlert('warning', 'Cancelamento Solicitado', res.message); })
              .always(() => $btnCancelar.prop('disabled', false).html('Cancelar'));
        });

        /**
         * Função para verificar o status periodicamente (POLLING) - VERSÃO FINAL
         */
        function checkStatus(clientId, isFirstRun = false) {
            if (statusInterval) clearInterval(statusInterval);
            if (isFirstRun) $migrationStatus.html('<div class="alert alert-info">Status: <strong>Verificando...</strong></div>');

            statusInterval = setInterval(() => {
                $.getJSON(`${AppConfig.apiUrl}/migrations/status/${clientId}`)
                    .done(response => {
                        if (response.success) {
                            const status = response.status;
                            const isRunning = status.includes('rodando') || status.includes('iniciando') || status.includes('limpando');
                            
                            let alertClass = isRunning ? 'alert-info' : (status === 'concluida' ? 'alert-success' : 'alert-danger');
                            $migrationStatus.html(`<div class="alert ${alertClass}">Status: <strong>${status}</strong></div>`);
                            
                            if (!isRunning && status !== 'nenhuma execução') {
                                clearInterval(statusInterval);
                                restoreButtons();
                                
                                if (status === 'concluida') {
                                    showAlert('success', 'Finalizado!', 'A migração foi concluída com sucesso.');
                                } else {
                                    showAlert('danger', 'Atenção', `O processo terminou com o status: ${status}.`);
                                }
                            }
                        }
                    })
            }, 2500); 
        }

        function restoreButtons() {
            $btnIniciar.prop('disabled', false).html('Iniciar Migração');
            $btnCancelar.prop('disabled', true);
        }


        //PARTE DE TESTE DE CONEXÃO
        const connectionStatus = {
            origem: false,
            destino: false
        };

        $('.btn-test-connection').on('click', function() {
            const type = $(this).data('type'); // 'origem' ou 'destino'
            const $statusEl = $(`#status-${type}`);
            const $form = $('#formTemplate');

            const connectionData = {
                tipo:    $form.find(`[name="conexao[${type}][tipo]"]`).val(),
                ip:      $form.find(`[name="conexao[${type}][ip]"]`).val(),
                porta:   $form.find(`[name="conexao[${type}][porta]"]`).val(),
                dbName:  $form.find(`[name="conexao[${type}][dbName]"]`).val(),
                usuario: $form.find(`[name="conexao[${type}][usuario]"]`).val(),
                senha:   $form.find(`[name="conexao[${type}][senha]"]`).val()
            };

            $statusEl.html('<i class="fas fa-spinner fa-spin"></i> Testando...');

            $.post(AppConfig.apiUrl + '/connections/test', { connectionData })
                .done(response => {
                    if (response.success) {
                        $statusEl.html('<span class="text-success"><i class="fas fa-check-circle"></i> Conectado</span>');
                        connectionStatus[type] = true;
                    }
                    checkAllConnections();
                })
                .fail(xhr => {
                    $statusEl.html(`<span class="text-danger"><i class="fas fa-times-circle"></i> Falhou</span>`);
                    showAlert('danger', 'Falha na Conexão', xhr.responseJSON?.message || 'Erro desconhecido.');
                    connectionStatus[type] = false;
                    checkAllConnections();
                });
        });

        function checkAllConnections() {
            // Só habilita o botão de Iniciar Migração se AMBAS as conexões foram testadas com sucesso
            if (connectionStatus.origem && connectionStatus.destino) {
                $('#btnIniciarMigracao').prop('disabled', false);
                showAlert('success', 'Pronto!', 'As duas conexões foram validadas. Você já pode iniciar a migração.');
            } else {
                $('#btnIniciarMigracao').prop('disabled', true);
            }
        }

        // Qualquer alteração nos campos de conexão reseta o status de validação
        $('#formTemplate').on('input', '[name^="conexao["]', function() {
            connectionStatus.origem = false;
            connectionStatus.destino = false;
            $('#status-origem, #status-destino').empty();
            checkAllConnections();
        });

        // --- LÓGICA DO MODAL DE MAPEAMENTO AVANÇADO ---
        const $advModal = $('#modalAdvancedMapping');
        let currentMappingInput = null;

        // Abre o modal e preenche com a regra atual do campo
        $(document).on('click', '.btn-advanced-mapping', function() {
            const $inputGroup = $(this).closest('.input-group');
            currentMappingInput = $inputGroup.find('.mapping-rule');
            const currentRule = JSON.parse(currentMappingInput.val());

            // Preenche o modal com os dados atuais
            $advModal.find('#mappingType').val(currentRule.type).trigger('change');
            // Preenche os campos de cada tipo de regra
            $advModal.find('.mapping-options [data-rule-key]').each(function() {
                const key = $(this).data('rule-key');
                $(this).val(currentRule[key] || '');
            });
        });

        // Mostra/esconde os campos de acordo com o tipo de mapeamento selecionado
        $advModal.find('#mappingType').on('change', function() {
            $advModal.find('.mapping-options').addClass('d-none');
            $('#options_' + $(this).val()).removeClass('d-none');
        });

        // Salva a regra de volta no input escondido do formulário principal
        $advModal.find('#btnSaveMapping').on('click', function() {
            if (!currentMappingInput) return;
            
            const newRule = { type: $advModal.find('#mappingType').val() };
            // Pega os valores dos campos visíveis
            $('#options_' + newRule.type).find('[data-rule-key]').each(function() {
                newRule[$(this).data('rule-key')] = $(this).val();
            });

            // Salva a regra como um texto JSON no input escondido
            currentMappingInput.val(JSON.stringify(newRule));
            
            // Atualiza o preview visual no input de texto visível
            const $previewInput = currentMappingInput.siblings('.mapping-preview');
            $previewInput.val(formatRuleForDisplayJS(newRule)); // Lógica de display simplificada
            
            $advModal.modal('hide');
            
            // Dispara o evento de 'change' para o auto-save funcionar!
            currentMappingInput.trigger('change');
        });

    } // Fim do if ($('#formTemplate').length)


    // ==========================================================
    // LÓGICA DA PÁGINA DE GERENCIAMENTO DE TEMPLATES (VERSÃO FINAL)
    // ==========================================================

    // Usamos um ID na página para garantir que este código só rode lá
    if ($('#templates-page-identifier').length) {

        const $form = $('#formNovoTemplate');
        const $tbody = $('#listaTemplates');

        // Função para carregar e renderizar a lista de templates existentes
        function carregarTemplates() {
            $tbody.html('<tr><td colspan="3" class="text-center">Carregando...</td></tr>');
            
            $.getJSON(AppConfig.apiUrl + '/templates')
                .done(function(response) {
                    $tbody.empty();
                    if(response.success && response.data.length > 0) {
                        response.data.forEach(tpl => {
                            // O HTML da linha agora inclui a coluna de ações com o botão de excluir
                            const row = `
                                <tr>
                                    <td>${tpl.id}</td>
                                    <td>${tpl.nome}</td>
                                    <td>
                                        <button class="btn btn-xs btn-danger delete-template" data-id="${tpl.id}" data-name="${tpl.nome}" title="Excluir Template">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>`;
                            $tbody.append(row);
                        });
                    } else {
                        $tbody.html('<tr><td colspan="3" class="text-center">Nenhum template encontrado.</td></tr>');
                    }
                })
                .fail(() => showAlert('danger', 'Erro', 'Não foi possível carregar os templates.'));
        }

        // Evento de CLIQUE no botão 'Salvar Template' para CRIAR um novo template
        $('#btnSalvarTemplate').on('click', function(e) {
            e.preventDefault();
            const formData = new FormData($form[0]);
            const $submitBtn = $(this);

            if ($form[0].checkValidity() === false) {
                $form[0].reportValidity();
                return;
            }

            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: AppConfig.apiUrl + '/templates',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            })
            .done(function(res) {
                if(res.success) {
                    showAlert('success', 'Sucesso!', res.message);
                    carregarTemplates();
                    $form[0].reset();
                } else {
                    showAlert('danger', 'Erro!', res.message);
                }
            })
            .fail(xhr => showAlert('danger', 'Erro!', xhr.responseJSON?.message || 'Falha ao criar template.'))
            .always(() => $submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Salvar Template'));
        });

        // Evento para EXCLUIR um template
        $tbody.on('click', '.delete-template', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            if (confirm(`Tem certeza que deseja excluir o template "${name}"?`)) {
                $.ajax({
                    url: `${AppConfig.apiUrl}/templates/${id}`, // Rota de exclusão com o ID
                    type: 'DELETE',
                    dataType: 'json'
                })
                .done(function(res) {
                    if(res.success) {
                        showAlert('success', 'Sucesso!', res.message);
                        carregarTemplates(); // Atualiza a lista após a exclusão
                    } else {
                        showAlert('danger', 'Erro!', res.message);
                    }
                })
                .fail(xhr => showAlert('danger', 'Erro!', xhr.responseJSON?.message || 'Falha ao excluir template.'));
            }
        });

        // Carga inicial dos templates ao entrar na página
        carregarTemplates();
    }

    // ==========================================================
    // LÓGICA DO IMPORTADOR DE CSV (VERSÃO COM REAL-TIME SSE)
    // ==========================================================
    if ($('#csv-importer-page').length) {
        const $form = $('#formCsvImport');
        const $logOutput = $('#importLogOutput');
        const $submitBtn = $form.find('button[type="submit"]');

        $form.on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Iniciando...');
            $logOutput.html('Enviando arquivo e configurações para o servidor...\n');

            // ETAPA 1: Iniciar a importação e obter o ID
            $.ajax({
                // A URL deve corresponder exatamente à rota definida no seu index.php
                url: AppConfig.apiUrl + '/api/csv/initiate', // <-- CORREÇÃO APLICADA AQUI
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            })
            .done(function(res) {
                if(res.success && res.importId) {
                    $logOutput.append('✔️ Próxima etapa: Conectando para receber logs em tempo real...\n');
                    // ETAPA 2: Conectar ao stream de logs
                    startLogStream(res.importId);
                } else {
                    handleFailure(res.message || 'Falha ao iniciar o processo de importação.');
                }
            })
            .fail(function(xhr) {
                // Tenta obter uma mensagem de erro mais clara do JSON retornado pelo servidor
                const error = xhr.responseJSON?.message || xhr.responseText || 'Falha crítica na comunicação com o servidor.';
                handleFailure(error);
            });
        });

        function startLogStream(importId) {
            const eventSource = new EventSource(AppConfig.apiUrl + '/api/csv/stream-log?id=' + importId);

            // Listener para mensagens de log padrão
            eventSource.onmessage = function(event) {
                // O dado vem como uma string JSON, então fazemos o parse
                const logLine = JSON.parse(event.data);
                $logOutput.append(logLine + '\n');
                // Auto-scroll para o final
                $logOutput.scrollTop($logOutput[0].scrollHeight);
            };

            // Listener para o evento de fechamento que definimos no PHP
            eventSource.addEventListener('close', function(event) {
                showAlert('success', 'Sucesso!', 'Importação concluída com sucesso!');
                $submitBtn.prop('disabled', false).html('<i class="fas fa-rocket me-2"></i> Iniciar Importação');
                eventSource.close(); // Fecha a conexão
            });

            // Listener para erros na conexão SSE
            eventSource.onerror = function(err) {
                $logOutput.append('❌ Erro na conexão de log. O processo pode ter sido interrompido ou finalizado.\n');
                showAlert('danger', 'Erro de Conexão!', 'A conexão com o servidor foi perdida.');
                $submitBtn.prop('disabled', false).html('<i class="fas fa-rocket me-2"></i> Iniciar Importação');
                eventSource.close();
            };
        }
        
        function handleFailure(message) {
            showAlert('danger', 'Erro!', message);
            $logOutput.append(`\nERRO CRÍTICO: ${message}`);
            $submitBtn.prop('disabled', false).html('<i class="fas fa-rocket me-2"></i> Iniciar Importação');
        }
    }



}); // Fim do $(function() { ... })