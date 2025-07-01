<?php

// src/Factory/DatabaseFactory.php
namespace App\Factory;

use PDO;
use PDOException;

class DatabaseFactory
{
    private array $config;
    private ?PDO $applicationConnection = null;

    public function __construct(string $configFile)
    {
        // Carrega o arquivo de configuração uma vez
        $this->config = require $configFile;
    }

    /**
     * Cria e retorna a conexão principal da aplicação (antigo DatabaseMigrador).
     * Garante que apenas uma instância seja criada durante a execução.
     */
    public function createApplicationConnection(): PDO
    {
        // Se a conexão já foi criada, apenas a retorna (benefício do Singleton, sem os malefícios)
        if ($this->applicationConnection !== null) {
            return $this->applicationConnection;
        }

        try {
            $dbConfig = $this->config['migrador'];
            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
            
            $this->applicationConnection = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
            $this->applicationConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $this->applicationConnection;
        } catch (PDOException $e) {
            // Lança a exceção em vez de morrer, para que a aplicação possa tratá-la
            throw new PDOException("Erro na conexão com o banco de dados da aplicação: " . $e->getMessage(), (int)$e->getCode());
        }
    }
}