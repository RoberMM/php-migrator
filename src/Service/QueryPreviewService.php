<?php
// src/Service/QueryPreviewService.php
namespace App\Service;

use App\Factory\ExternalConnectionFactory;
use PDO;

class QueryPreviewService
{
    // Não precisamos mais do repositório aqui!
    public function __construct() {}

    /**
     * Recebe os dados da conexão e a query diretamente.
     */
    public function getPreview(array $sourceConfig, string $sql): array
    {
        if (trim($sql) === '') {
            throw new \InvalidArgumentException('A query não pode estar vazia.');
        }
        if (empty($sourceConfig['tipo']) || empty($sourceConfig['ip']) || empty($sourceConfig['dbName'])) {
            throw new \InvalidArgumentException('Dados de conexão de origem incompletos.');
        }

        // Cria a conexão com os dados recebidos
        $pdo = ExternalConnectionFactory::create(
            $sourceConfig['tipo'],
            $sourceConfig['ip'],
            $sourceConfig['porta'] ?? '',
            $sourceConfig['dbName'],
            $sourceConfig['usuario'] ?? '',
            $sourceConfig['senha'] ?? ''
        );

        $limitedSql = $this->injectLimit($sql, $sourceConfig['tipo']);
        return $pdo->query($limitedSql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adiciona LIMIT 20 ou TOP 20 à query se não houver um limite definido.
     */
    private function injectLimit(string $sql, string $dbType): string
    {
        if ($dbType === 'sqlsrv') {
            if (!preg_match('/\b(top|offset)\b/i', $sql)) {
                return preg_replace('/^\s*(select\s+)(distinct\s+)?/i', '$1$2TOP (20) ', $sql, 1);
            }
        } else {
            if (!preg_match('/\blimit\b/i', $sql)) {
                return rtrim($sql, '; ') . ' LIMIT 20';
            }
        }
        return $sql;
    }
}