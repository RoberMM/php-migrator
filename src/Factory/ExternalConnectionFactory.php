<?php

// src/Factory/ExternalConnectionFactory.php
namespace App\Factory;

use PDO;
use PDOException;
use InvalidArgumentException;

class ExternalConnectionFactory
{
    /**
     * Cria uma nova conexão PDO com base nos parâmetros fornecidos.
     * @throws PDOException se a conexão falhar.
     * @throws InvalidArgumentException se o tipo de banco não for suportado.
     */
    public static function create(string $tipo, string $host, string $porta, string $dbName, string $usuario, string $senha): PDO
    {
        $dsn = match (strtolower($tipo)) {
            'mysql' => "mysql:host=$host;port=$porta;dbname=$dbName;charset=utf8",
            'sqlsrv' => "sqlsrv:Server=$host,$porta;Database=$dbName;Encrypt=false",
            'firebird' => "firebird:dbname=$host/$porta:$dbName",
            default => throw new InvalidArgumentException("Tipo de banco '$tipo' não suportado."),
        };
        
        // O try-catch aqui é opcional, pois o PDO já lança PDOException.
        // O importante é NÃO usar die(). Deixe a exceção ser lançada.
        $pdo = new PDO($dsn, $usuario, $senha);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}