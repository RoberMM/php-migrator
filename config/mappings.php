<?php
// config/mappings.php

/**
 * Mapa de configuração para todas as migrações.
 * A chave de cada entrada (ex: 'tabelaAcao') corresponde ao 'prefix'
 * que definimos no nosso formulário HTML.
 */
return [
    // =============================================
    // MAPA COMPLETO PARA A TABELA DE AÇÃO
    // =============================================
    'tabelaAcao' => [
        'target_table' => 'tab_acao',
        'destination_key_column' => 'sigla', 
        'columns' => [
                'update_data_hora' => ['type' => 'datetime', 'value' => 'NOW()'],
                'update_usuario' => ['type' => 'default', 'value' => -999],
                'sigla' => ['type' => 'generate_acronym', 'from_field' => 'sigla', 'max_length' => 8],
                'descricao' => ['type' => 'source_column', 'from_field' => 'descricao', 'max_length' => 80],
                'qualif_autor' => ['type' => 'default', 'value' => 'Autor'],
                'qualif_reu' => ['type' => 'default', 'value' => 'Réu'],
                'esconder_formato' => ['type' => 'default', 'value' => 0],
                'id_integracao_api' => ['type' => 'default', 'value' => null],
        ]
    ],
    
    // =============================================
    // MAPA COMPLETO PARA A TABELA DE PESSOAS
    // =============================================
    'tabelaPessoa' => [
        'target_table' => 'cad_pessoa',
        'destination_key_column' => 'codigo', 
        'columns' => [
            // --- Colunas com valores automáticos ou padrão ---
            'update_data_hora'  => ['type' => 'datetime'],
            'update_usuario'    => ['type' => 'default', 'value' => -999],
            'codigo' => [
                'type'        => 'auto_increment',
                'start_value' => 5000, // Começa em 5000 se a tabela estiver vazia
                'scope'       => 'table' // 'table' significa que ele vai olhar o MAX() da tabela para continuar
            ],
            'nome'                  => ['type' => 'source_column', 'from_field' => 'nome', 'max_length' => 60, 'default_if_null' => ''],
            'nomepesq'              => ['type' => 'source_column', 'from_field' => 'nomepesq', 'max_length' => 60, 'default_if_null' => ''],
            'categoria'             => ['type' => 'sub_query_de_para_lookup', 'from_field' => 'categoria', 'default_if_null' => null],
            'fisica_juridica'       => ['type' => 'source_column', 'from_field' => 'fisica_juridica', 'default_if_null' => null],
            'sigla'                 => ['type' => 'source_column', 'max_length' => 8, 'default_if_null' => ''],
            'classe'                => ['type' => 'source_column', 'from_field' => 'classe', 'max_length' => 1, 'default_if_null' => null],
            'contato'               => ['type' => 'source_column', 'from_field' => 'contato', 'max_length' => 40, 'default_if_null' => null],
            'contato_telefone'      => ['type' => 'source_column', 'from_field' => 'contato_telefone', 'max_length' => 30, 'default_if_null' => null],
            'telefones'             => ['type' => 'source_column', 'from_field' => 'telefones', 'max_length' => 30, 'default_if_null' => null],
            'fax'                   => ['type' => 'source_column', 'from_field' => 'fax', 'max_length' => 15, 'default_if_null' => null],
            'observacao'            => ['type' => 'source_column', 'from_field' => 'observacao', 'default_if_null' => null],
            'data_cadastro'         => ['type' => 'source_column', 'from_field' => 'data_cadastro', 'default_if_null' => null],
            'nivel'                 => ['type' => 'source_column', 'from_field' => 'nivel', 'default_if_null' => null],
            'cliente'               => ['type' => 'source_column', 'from_field' => 'cliente', 'default_if_null' => 0],
            'fornecedor'            => ['type' => 'source_column', 'from_field' => 'fornecedor', 'default_if_null' => 0],
            'endereco_comercial'    => ['type' => 'source_column', 'from_field' => 'endereco_comercial', 'default_if_null' => null],
            'cpf_cnpj'              => ['type' => 'source_column', 'from_field' => 'cpf_cnpj', 'max_length' => 20, 'default_if_null' => null],
            'rgie'                  => ['type' => 'source_column', 'from_field' => 'rgie', 'max_length' => 20, 'default_if_null' => null],
            'ramo'                  => ['type' => 'source_column', 'from_field' => 'ramo', 'max_length' => 50, 'default_if_null' => null],
            'estado_civil'          => ['type' => 'source_column', 'from_field' => 'estado_civil', 'max_length' => 15, 'default_if_null' => null],
            'nacionalidade'         => ['type' => 'source_column', 'from_field' => 'nacionalidade', 'max_length' => 15, 'default_if_null' => null],
            'endereco'              => ['type' => 'source_column', 'from_field' => 'endereco', 'max_length' => 60, 'default_if_null' => null],
            'complemento'           => ['type' => 'source_column', 'from_field' => 'complemento', 'max_length' => 40, 'default_if_null' => null],
            'cep'                   => ['type' => 'source_column', 'from_field' => 'cep', 'max_length' => 9, 'default_if_null' => null],
            'cidade'                => ['type' => 'source_column', 'from_field' => 'cidade', 'max_length' => 40, 'default_if_null' => null],
            'estado'                => ['type' => 'source_column', 'from_field' => 'estado', 'max_length' => 2, 'default_if_null' => null],
            'ctrabalho'             => ['type' => 'source_column', 'from_field' => 'ctrabalho', 'max_length' => 20, 'default_if_null' => null],
            'pai_socio1'            => ['type' => 'source_column', 'from_field' => 'pai_socio1', 'max_length' => 60, 'default_if_null' => null],
            'mae_socio2'            => ['type' => 'source_column', 'from_field' => 'mae_socio2', 'max_length' => 60, 'default_if_null' => null],
            'teleitor'              => ['type' => 'concatenated_source', 'from_field' => 'teleitor', 'max_length' => 20, 'default_if_null' => null],
            'data_nascimento'       => ['type' => 'source_column', 'from_field' => 'data_nascimento', 'default_if_null' => null],
            'email'                 => ['type' => 'source_column', 'from_field' => 'email', 'max_length' => 50, 'default_if_null' => null],
            'http'                  => ['type' => 'source_column', 'from_field' => 'http', 'max_length' => 50, 'default_if_null' => null],
            'enviar_email'          => ['type' => 'source_column', 'from_field' => 'enviar_email', 'default_if_null' => null],
            'oab'                   => ['type' => 'source_column', 'from_field' => 'oab', 'max_length' => 12, 'default_if_null' => null],
            'celular'               => ['type' => 'source_column', 'from_field' => 'celular', 'max_length' => 30, 'default_if_null' => null],
            'endereco_cob'          => ['type' => 'source_column', 'from_field' => 'endereco_cob', 'max_length' => 60, 'default_if_null' => null],
            'bairro_cob'            => ['type' => 'source_column', 'from_field' => 'bairro_cob', 'max_length' => 40, 'default_if_null' => null],
            'complemento_cob'       => ['type' => 'source_column', 'from_field' => 'complemento_cob', 'max_length' => 40, 'default_if_null' => null],
            'cep_cob'               => ['type' => 'source_column', 'from_field' => 'cep_cob', 'max_length' => 9, 'default_if_null' => null],
            'cidade_cob'            => ['type' => 'source_column', 'from_field' => 'cidade_cob', 'max_length' => 40, 'default_if_null' => null],
            'estado_cob'            => ['type' => 'source_column', 'from_field' => 'estado_cob', 'max_length' => 2, 'default_if_null' => null],
            'telefones_cob'         => ['type' => 'source_column', 'from_field' => 'telefones_cob', 'max_length' => 30, 'default_if_null' => null],
            'fax_cob'               => ['type' => 'source_column', 'from_field' => 'fax_cob', 'max_length' => 15, 'default_if_null' => null],
            'endereco_com'          => ['type' => 'source_column', 'from_field' => 'endereco_com', 'max_length' => 60, 'default_if_null' => null],
            'bairro_com'            => ['type' => 'source_column', 'from_field' => 'bairro_com', 'max_length' => 40, 'default_if_null' => null],
            'complemento_com'       => ['type' => 'source_column', 'from_field' => 'complemento_com', 'max_length' => 40, 'default_if_null' => null],
            'cep_com'               => ['type' => 'source_column', 'from_field' => 'cep_com', 'max_length' => 9, 'default_if_null' => null],
            'cidade_com'            => ['type' => 'source_column', 'from_field' => 'cidade_com', 'max_length' => 40, 'default_if_null' => null],
            'estado_com'            => ['type' => 'source_column', 'from_field' => 'estado_com', 'max_length' => 2, 'default_if_null' => null],
            'telefones_com'         => ['type' => 'source_column', 'from_field' => 'telefones_com', 'max_length' => 30, 'default_if_null' => null],
            'fax_com'               => ['type' => 'source_column', 'from_field' => 'fax_com', 'max_length' => 15, 'default_if_null' => null],
            'empresa'               => ['type' => 'source_column', 'from_field' => 'empresa', 'default_if_null' => null],
            'vinculo'               => ['type' => 'source_column', 'from_field' => 'vinculo', 'max_length' => 30, 'default_if_null' => null],
            'outras_informacoes'    => ['type' => 'source_column', 'from_field' => 'outras_informacoes', 'default_if_null' => null],
            'bairro'                => ['type' => 'source_column', 'from_field' => 'bairro', 'max_length' => 40, 'default_if_null' => null],
            'pis'                   => ['type' => 'source_column', 'from_field' => 'pis', 'max_length' => 14, 'default_if_null' => null],
            'cpf_cnpjpesq'          => ['type' => 'default', 'max_length' => 20, 'value' => ' '],
            'icontabil_cli'         => ['type' => 'source_column', 'from_field' => 'icontabil_cli', 'max_length' => 25, 'default_if_null' => ''],
            'icontabil_for'         => ['type' => 'source_column', 'from_field' => 'icontabil_for', 'max_length' => 25, 'default_if_null' => ''],
            'banco_nome'            => ['type' => 'source_column', 'from_field' => 'banco_nome', 'max_length' => 30, 'default_if_null' => ''],
            'banco_codigo'          => ['type' => 'source_column', 'from_field' => 'banco_codigo', 'default_if_null' => 0],
            'banco_agencia'         => ['type' => 'source_column', 'from_field' => 'banco_agencia', 'default_if_null' => 0],
            'banco_agencia_dv'      => ['type' => 'source_column', 'from_field' => 'banco_agencia_dv', 'default_if_null' => 0],
            'banco_conta'           => ['type' => 'source_column', 'from_field' => 'banco_conta', 'max_length' => 20, 'default_if_null' => ''],
            'banco_conta_dv'        => ['type' => 'source_column', 'from_field' => 'banco_conta_dv', 'default_if_null' => 0],
            'banco_titular'         => ['type' => 'source_column', 'from_field' => 'banco_titular', 'max_length' => 60, 'default_if_null' => ''],
            'banco_titular_cpf_cnpj' => ['type' => 'source_column', 'from_field' => 'banco_titular_cpf_cnpj', 'max_length' => 20, 'default_if_null' => ''],
            'banco_observacao'      => ['type' => 'source_column', 'from_field' => 'banco_observacao', 'default_if_null' => null],
            'id_tributo_perfil'     => ['type' => 'source_column', 'from_field' => 'id_tributo_perfil', 'default_if_null' => null],
            'id_cidades_ibge'       => ['type' => 'source_column', 'from_field' => 'id_cidades_ibge', 'default_if_null' => null],
            'endereco_numero'       => ['type' => 'source_column', 'from_field' => 'endereco_numero', 'max_length' => 10, 'default_if_null' => null],
            'id_cidades_ibge_com'   => ['type' => 'source_column', 'from_field' => 'id_cidades_ibge_com', 'default_if_null' => null],
            'endereco_numero_com'   => ['type' => 'source_column', 'from_field' => 'endereco_numero_com', 'max_length' => 10, 'default_if_null' => null],
            'id_cidades_ibge_cob'   => ['type' => 'source_column', 'from_field' => 'id_cidades_ibge_cob', 'default_if_null' => null],
            'endereco_numero_cob'   => ['type' => 'source_column', 'from_field' => 'endereco_numero_cob', 'max_length' => 10, 'default_if_null' => null],
            'fin_cta_adiantamento'  => ['type' => 'source_column', 'from_field' => 'fin_cta_adiantamento', 'default_if_null' => null],
            'inscricao_municipal'   => ['type' => 'source_column', 'from_field' => 'inscricao_municipal', 'max_length' => 100, 'default_if_null' => null],
            'integracao_situacao'   => ['type' => 'default', 'value' => 0],
            'integracao_data'       => ['type' => 'source_column', 'from_field' => 'integracao_data', 'default_if_null' => null],
            'ntm_envio_email'       => ['type' => 'source_column', 'from_field' => 'ntm_envio_email', 'default_if_null' => null],
        ]
    ],

    // =============================================
    // MAPA PARA A TABELA DE CATEGORIA
    // =============================================
    'tabelaCategoria' => [
        'target_table' => 'tab_categoria',
        'destination_key_column' => 'codigo', 
        'columns' => [
            // Colunas automáticas
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Coluna com auto-incremento customizado
            'codigo' => [
                'type'        => 'auto_increment',
                'start_value' => 1, // Começa em 1 se a tabela estiver vazia
                'scope'       => 'table' // Busca o MAX() da tabela para continuar
            ],

            // Colunas mapeadas pelo formulário
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => ''
            ],
            'abreviatura' => [
                'type' => 'source_column',
                'max_length' => 10,
                'default_if_null' => null
            ],
            'id_integracao_api' => [
                'type' => 'source_column',
                'max_length' => 36,
                'default_if_null' => ''
            ],
        ]
    ],

     // =============================================
    // MAPA PARA A TABELA DE ASSUNTO
    // =============================================
    'tabelaAssunto' => [
        'target_table' => 'tab_assunto',
        'destination_key_column' => 'sigla', // A sigla é a chave primária aqui
        'columns' => [
            // --- Colunas com valores automáticos ou padrão ---
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            'incide_inss'      => ['type' => 'default', 'value' => 0],
            'inss'             => ['type' => 'default', 'value' => 0.00],
            'id_integracao_api'=> ['type' => 'default', 'value' => ''],

            // --- Coluna com valor gerado ---
            'sigla' => [
                'type' => 'generate_acronym',
                // Dizemos ao sistema que o texto base para a sigla virá do campo 'descricao' do formulário
                'from_field' => 'descricao',
                'max_length' => 5,
                'default_if_null' => 'ERRO'
            ],
            'descricao' => [
                'type' => 'source_column',
                'from_field' => 'descricao',
                'max_length' => 40,
                'default_if_null' => ''
            ],
        ]
    ],

    // =============================================
    // MAPA PARA A TABELA DE CAUSA DE PEDIR
    // =============================================
    'tabelaCausaPedir' => [
        'target_table' => 'tab_causa_pedir',
        'destination_key_column' => 'id_causa_pedir', 
        'columns' => [
            // Colunas automáticas
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Coluna com auto-incremento customizado
            'id_causa_pedir' => [
                'type'        => 'auto_increment',
                'start_value' => 1, // Começa em 1 se a tabela estiver vazia
                'scope'       => 'table' // Busca o MAX() da tabela para continuar
            ],

            // Colunas mapeadas pelo formulário
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 250,
                'default_if_null' => ''
            ],
            'id_integracao_api' => [
                'type' => 'source_column',
                'max_length' => 36,
                'default_if_null' => ''
            ],
        ]
    ],

    // =============================================
    // MAPA PARA A TABELA DE FASE
    // =============================================
    'tabelaFase' => [
        'target_table' => 'tab_fase',
        'destination_key_column' => 'codigo', // A chave primária
        'columns' => [
            // Colunas automáticas
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Coluna com auto-incremento customizado
            'codigo' => [
                'type'        => 'auto_increment',
                'start_value' => 1, // Começa em 1 se a tabela estiver vazia
                'scope'       => 'table' // Busca o MAX() da tabela para continuar
            ],

            // Coluna mapeada pelo formulário
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => ''
            ],
        ]
    ],

    // =============================================
    // MAPA PARA A TABELA DE GARANTIA
    // =============================================
    'tabelaGarantia' => [
        'target_table' => 'tab_garantia',
        'destination_key_column' => 'codigo', 
        'columns' => [
            // --- Colunas com valores automáticos ou padrão ---
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // --- Coluna com auto-incremento customizado ---
            'codigo' => [
                'type'        => 'auto_increment',
                'start_value' => 1, // Começa em 1 se a tabela estiver vazia
                'scope'       => 'table' // Busca o MAX() da tabela para continuar
            ],

            // --- Colunas mapeadas pelo formulário ---
            'descricao'          => ['type' => 'source_column', 'max_length' => 40, 'default_if_null' => ''],
            'ativa_passiva'      => ['type' => 'source_column', 'default_if_null' => 0],
            'codigo_ativo_empresa' => ['type' => 'source_column', 'max_length' => 40, 'default_if_null' => null],
            'codigo_gra_tipo'    => ['type' => 'source_column', 'default_if_null' => 0],
            'caracteristicas'    => ['type' => 'source_column', 'default_if_null' => null],
            'endereco'           => ['type' => 'source_column', 'max_length' => 60, 'default_if_null' => null],
            'cidade'             => ['type' => 'source_column', 'max_length' => 40, 'default_if_null' => null],
            'uf'                 => ['type' => 'source_column', 'max_length' => 2, 'default_if_null' => null],
            'data_avaliacao'     => ['type' => 'source_column', 'default_if_null' => null],
            'valor'              => ['type' => 'source_column', 'default_if_null' => 0.00],
            'data_valor'         => ['type' => 'source_column', 'default_if_null' => null],
            'sigla_regra'        => ['type' => 'source_column', 'max_length' => 8, 'default_if_null' => ''],
            'imo_num_matricula'  => ['type' => 'source_column', 'max_length' => 40, 'default_if_null' => null],
            'cf_pessoa_cedente'  => ['type' => 'source_column', 'default_if_null' => null],
            'cf_data_vencimento' => ['type' => 'source_column', 'default_if_null' => null],
            'cf_num_carta'       => ['type' => 'source_column', 'max_length' => 40, 'default_if_null' => null],
            'cf_data_emissao'    => ['type' => 'source_column', 'default_if_null' => null],
            'prod_local_pessoa'  => ['type' => 'source_column', 'default_if_null' => null],
            'prod_quantidade'    => ['type' => 'source_column', 'default_if_null' => null],
        ]
    ],

    // =============================================
    // MAPA PARA A TABELA DE TIPO DE GARANTIA
    // =============================================
    'tabelaGarantiaTipo' => [
        'target_table' => 'tab_garantia_tipo',
        'destination_key_column' => 'codigo', 
        'columns' => [
            // Colunas automáticas
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Coluna com auto-incremento customizado
            'codigo' => [
                'type'        => 'auto_increment',
                'start_value' => 1,
                'scope'       => 'table'
            ],

            // Coluna mapeada pelo formulário
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => ''
            ],

            // Coluna mapeada pelo formulário
            'codigo_formato_gra' => [
                'type' => 'source_column',
                'max_length' => 6,
                'default_if_null' => 0
            ],
        ]
    ],

    // =============================================
    // MAPA PARA A TABELA DE GRUPO DE TRABALHO
    // =============================================
    'tabelaGrupoTrabalho' => [
        'target_table' => 'tab_grupo_trabalho',
        'destination_key_column' => 'codigo', 
        'columns' => [
            // Colunas automáticas/padrão
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Coluna com auto-incremento customizado
            'codigo' => [
                'type'        => 'auto_increment',
                'start_value' => 1,
                'scope'       => 'table'
            ],

            // Colunas mapeadas pelo formulário
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => ''
            ],
            'isProcessual' => [
                'type' => 'source_column',
                'default_if_null' => 0
            ],
            'isFinanceiro' => [
                'type' => 'source_column',
                'default_if_null' => 0
            ],
            'isCobranca' => [
                'type' => 'source_column',
                'default_if_null' => 0
            ],
        ]
    ],

    // =============================================
    // MAPA PARA A TABELA DE JUÍZO
    // =============================================
    'tabelaJuizo' => [
        'target_table' => 'tab_juizo',
        'destination_key_column' => 'sigla', // A sigla é a chave primária
        'columns' => [
            // Colunas automáticas/padrão
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Coluna com valor gerado
            'sigla' => [
                'type' => 'generate_acronym',
                'from_field' => 'descricao', // O texto base para a sigla virá do campo 'descricao' do formulário
                'max_length' => 8,
                'default_if_null' => 'ERRO'
            ],

            // Colunas mapeadas pelo formulário
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 60,
                'default_if_null' => ''
            ],
            'cidade' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => null
            ],
            'estado' => [
                'type' => 'source_column',
                'max_length' => 2,
                'default_if_null' => null
            ],
            'Info_complementares' => [
                'type' => 'source_column',
                'default_if_null' => null
            ],
            'id_municipio' => [
                'type' => 'source_column',
                'default_if_null' => null
            ],
            'id_integracao_api' => [
                'type' => 'source_column',
                'max_length' => 36,
                'default_if_null' => ''
            ],
        ]
    ],

    // =============================================
    // MAPA PARA A TABELA DE LOCALIZADOR
    // =============================================
    'tabelaLocalizador' => [
        'target_table' => 'tab_localizador',
        'destination_key_column' => 'sigla', // A sigla é a chave primária
        'columns' => [
            // Colunas automáticas/padrão
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Colunas mapeadas pelo formulário
            'sigla' => [
                'type' => 'source_column',
                'max_length' => 20,
                'default_if_null' => ''
            ],
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => ''
            ],
            'tipo' => [
                'type' => 'source_column',
                'default_if_null' => 0
            ],
        ]
    ],

    // =============================================
    // MAPA PARA A TABELA DE MATÉRIA
    // =============================================
    'tabelaMateria' => [
        'target_table' => 'tab_materia',
        'destination_key_column' => 'codigo', 
        'columns' => [
            // Colunas automáticas
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Coluna com auto-incremento customizado
            'codigo' => [
                'type'        => 'auto_increment',
                'start_value' => 1,
                'scope'       => 'table'
            ],

            // Colunas mapeadas pelo formulário
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => ''
            ],
            'tipo' => [
                'type' => 'source_column',
                'default_if_null' => null
            ],
            'id_integracao_api' => [
                'type' => 'source_column',
                'max_length' => 36,
                'default_if_null' => ''
            ],
        ]
    ],

    // =============================================
    // MAPA PARA A TABELA DE ÓRGÃO JULGADOR
    // =============================================
    'tabelaOrgaoJulgador' => [
        'target_table' => 'tab_orgao_julgador',
        'destination_key_column' => 'sigla', // A sigla é a chave primária
        'columns' => [
            // Colunas automáticas/padrão
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Coluna com valor gerado
            'sigla' => [
                'type' => 'generate_acronym',
                'from_field' => 'sigla', // O texto base para a sigla virá do campo 'sigla' do formulário
                'max_length' => 3,
                'default_if_null' => 'ER' // Um valor padrão de erro caso a descrição esteja vazia
            ],

            // Colunas mapeadas pelo formulário
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => ''
            ],
            'id_integracao_api' => [
                'type' => 'source_column',
                'max_length' => 36,
                'default_if_null' => ''
            ],
        ]
    ],

    // =============================================
    // MAPA PARA A TABELA DE QUALIFICAÇÃO
    // =============================================
    'tabelaQualificacao' => [
        'target_table' => 'tab_qualificacao',
        'destination_key_column' => 'codigo', // A chave primária
        'columns' => [
            // Colunas automáticas/padrão
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Coluna com auto-incremento customizado
            'codigo' => [
                'type'        => 'auto_increment',
                'start_value' => 1,
                'scope'       => 'table'
            ],

            // Colunas mapeadas pelo formulário
            'sigla' => [
                'type' => 'source_column',
                'max_length' => 8,
                'default_if_null' => ''
            ],
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => ''
            ],
            'id_integracao_api' => [
                'type' => 'source_column',
                'max_length' => 36,
                'default_if_null' => ''
            ],
        ]
    ],

    // =============================================
    // NOVO MAPA PARA A TABELA DE RESULTADO
    // =============================================
    'tabelaResultado' => [
        'target_table' => 'tab_resultado',
        'destination_key_column' => 'codigo', // A chave primária
        'columns' => [
            // Colunas automáticas
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            'codigo' => [
                'type'        => 'auto_increment',
                'start_value' => 1, // Começa em 1 se a tabela estiver vazia
                'scope'       => 'table' // Busca o MAX() da tabela para continuar
            ],
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => ''
            ],
            'aplicabilidade' => [
                'type' => 'source_column',
                'default_if_null' => 0
            ],
        ]
    ],

    // =============================================
    // NOVO MAPA PARA A TABELA DE RITO
    // =============================================
    'tabelaRito' => [
        'target_table' => 'tab_rito',
        'destination_key_column' => 'id_rito', // A chave primária
        'columns' => [
            // Colunas automáticas
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Coluna com auto-incremento customizado
            'id_rito' => [
                'type'        => 'auto_increment',
                'start_value' => 1,
                'scope'       => 'table'
            ],

            // Coluna mapeada pelo formulário
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => ''
            ],
        ]
    ],

    // =============================================
    // NOVO MAPA PARA A TABELA DE SITUAÇÃO
    // =============================================
    'tabelaSituacao' => [
        'target_table' => 'tab_situacao',
        'destination_key_column' => 'codigo', // A chave primária
        'columns' => [
            // Colunas automáticas
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Colunas mapeadas pelo formulário
            'codigo' => [
                'type'        => 'auto_increment',
                'start_value' => 1, // Começa em 1 se a tabela estiver vazia
                'scope'       => 'table' // Busca o MAX() da tabela para continuar
            ],
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => ''
            ],
        ]
    ],

    // =============================================
    // NOVO MAPA PARA A TABELA DE TEMA
    // =============================================
    'tabelaTema' => [
        'target_table' => 'tab_tema',
        'destination_key_column' => 'id_tema', // A chave primária
        'columns' => [
            // Colunas automáticas
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // Coluna com auto-incremento customizado
            'id_tema' => [
                'type'        => 'auto_increment',
                'start_value' => 1,
                'scope'       => 'table'
            ],

            // Colunas mapeadas pelo formulário
            'descricao' => [
                'type' => 'source_column',
                'max_length' => 40,
                'default_if_null' => null
            ],
            'id_integracao_api' => [
                'type' => 'source_column',
                'max_length' => 36,
                'default_if_null' => ''
            ],
        ]
    ],

    // =============================================
    // NOVO MAPA PARA A TABELA DE USUÁRIO
    // =============================================
    'tabelaUsuario' => [
        'target_table' => 'usuario',
        'destination_key_column' => 'id_usuario', 
        'columns' => [
            // --- Colunas com valores automáticos ou padrão ---
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            'unidade_acesso'   => ['type' => 'default', 'value' => 6],
            'tipo'             => ['type' => 'default', 'value' => 1],
            'administrador'    => ['type' => 'default', 'value' => 0],
            'acesso'           => ['type' => 'default', 'value' => ''],
            'ext_processos_pessoa_sigla' => ['type' => 'default', 'value' => ''],
            
            // --- Coluna com auto-incremento customizado ---
            'id_usuario' => [
                'type'        => 'auto_increment',
                'start_value' => 1,
                'scope'       => 'table'
            ],

            // --- Colunas mapeadas pelo formulário ---
            'senha'             => ['type' => 'source_column', 'max_length' => 32, 'default_if_null' => null],
            'nome'              => ['type' => 'source_column', 'max_length' => 40, 'default_if_null' => ''],
            'pessoa_agenda'     => ['type' => 'source_column', 'default_if_null' => null],
            'codigo_supervisor' => ['type' => 'source_column', 'default_if_null' => null],
            'usr_centro_custo'  => ['type' => 'source_column', 'default_if_null' => null],
            'id_perfil'         => ['type' => 'source_column', 'default_if_null' => 0],
            'data_desbloqueio'  => ['type' => 'source_column', 'default_if_null' => null],
            'bloquear_apos_dias' => ['type' => 'source_column', 'default_if_null' => null],
            'acesso_ultimo_valido' => ['type' => 'source_column', 'default_if_null' => null],
            'acesso_ultimo_invalido' => ['type' => 'source_column', 'default_if_null' => null],
            'ultima_troca_senha' => ['type' => 'source_column', 'default_if_null' => null],
            'tentativas_invalidas' => ['type' => 'source_column', 'default_if_null' => null],
            'acesso_hora_inicial' => ['type' => 'source_column', 'default_if_null' => null],
            'acesso_hora_final' => ['type' => 'source_column', 'default_if_null' => null],
            'acesso_final_semana' => ['type' => 'source_column', 'default_if_null' => null],
            'login'             => ['type' => 'source_column', 'max_length' => 40, 'default_if_null' => null],
            'grupo_usu'         => ['type' => 'source_column', 'default_if_null' => null],
            'acessogrupos'      => ['type' => 'source_column', 'max_length' => 120, 'default_if_null' => null],
            'agpessoasoutras'   => ['type' => 'source_column', 'max_length' => 120, 'default_if_null' => null],
            'id_unidade'        => ['type' => 'source_column', 'default_if_null' => 0],
            'parametros_ad'     => ['type' => 'source_column', 'max_length' => 100, 'default_if_null' => null],
            'atributo_nome_ad'  => ['type' => 'source_column', 'max_length' => 60, 'default_if_null' => null],
            'atributo_valor_ad' => ['type' => 'source_column', 'max_length' => 60, 'default_if_null' => null],
            'ext_sigla_integracao' => ['type' => 'source_column', 'max_length' => 10, 'default_if_null' => null],
            'ext_processos_pessoa' => ['type' => 'source_column', 'default_if_null' => null],
            'ext_custas'        => ['type' => 'source_column', 'default_if_null' => 0],
            'ext_honorarios'    => ['type' => 'source_column', 'default_if_null' => 0],
            'ext_ficha_completa'=> ['type' => 'source_column', 'default_if_null' => 0],
            'ext_andamentos_internos' => ['type' => 'source_column', 'default_if_null' => 0],
            'ext_acessomorto'   => ['type' => 'source_column', 'default_if_null' => 0],
            'ext_acessodocumentos' => ['type' => 'source_column', 'default_if_null' => 0],
            'ext_janelainicio'  => ['type' => 'source_column', 'default_if_null' => 0],
            'ext_acessoconsulta'=> ['type' => 'source_column', 'default_if_null' => 0],
            'ext_acessodashboard' => ['type' => 'source_column', 'default_if_null' => 0],
            'ext_acessoagenda'  => ['type' => 'source_column', 'default_if_null' => 0],
            'codigo_validacao'  => ['type' => 'source_column', 'max_length' => 40, 'default_if_null' => ''],
            'data_hora_validacao' => ['type' => 'source_column', 'default_if_null' => null],
            'timesheet_usr_supervisor' => ['type' => 'source_column', 'default_if_null' => null],
            'configuracoes'     => ['type' => 'source_column', 'default_if_null' => null],
            'op_2fa'            => ['type' => 'source_column', 'default_if_null' => 0],
            'inativo'          => ['type' => 'source_column', 'default_if_null' => 0],
        ]
    ],

    // =============================================
    // NOVO MAPA PARA A TABELA DE UNIDADE
    // =============================================
    'tabelaUnidade' => [
        'target_table' => 'unidade',
        'destination_key_column' => 'id_unidade', 
        'columns' => [
            // --- Colunas com valores automáticos ou padrão ---
            'update_data_hora' => ['type' => 'datetime'],
            'update_usuario'   => ['type' => 'default', 'value' => -999],
            
            // --- Coluna com auto-incremento customizado ---
            'id_unidade' => [
                'type'        => 'auto_increment',
                'start_value' => 1,
                'scope'       => 'table'
            ],

            // --- Colunas mapeadas pelo formulário ---
            'id_pessoa' => [
                'type' => 'source_column',
                'from_field' => 'id_pessoa',
                'default_if_null' => 0
            ],
            'possui_financeiro' => [
                'type' => 'source_column',
                'from_field' => 'possui_financeiro',
                'default_if_null' => 0
            ],
            'id_nfs_emitente' => [
                'type' => 'source_column',
                'from_field' => 'id_nfs_emitente',
                'default_if_null' => null
            ],
        ]
    ],
];