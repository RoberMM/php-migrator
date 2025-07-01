<?php
// config/database.php

/**
 * Este arquivo lê as configurações do banco de dados a partir das
 * variáveis de ambiente (definidas no arquivo .env), provendo valores
 * padrão caso alguma variável não seja encontrada.
 * Isso mantém as credenciais seguras e fora do controle de versão (Git).
 */

return [
    // Conexão principal da aplicação
    'migrador' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'dbname' => $_ENV['DB_DATABASE'] ?? 'migrador_bd',
        'user' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8' // O charset geralmente é fixo e pode continuar aqui.
    ]
];