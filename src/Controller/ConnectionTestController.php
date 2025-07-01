<?php
// src/Controller/ConnectionTestController.php
namespace App\Controller;
use App\Factory\ExternalConnectionFactory;

class ConnectionTestController
{
    public function test(array $connectionData): bool
    {
        // Apenas tenta criar a conexão. Se não lançar exceção, deu certo.
        ExternalConnectionFactory::create(...array_values($connectionData));
        return true;
    }
}