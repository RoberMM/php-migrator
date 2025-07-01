<?php
// src/Controller/MigrationApiController.php
namespace App\Controller;

use App\Service\MigrationService;
use InvalidArgumentException;

class MigrationApiController 
{
    public function __construct(private MigrationService $migrationService) {}

    /**
     * Inicia o processo de migração para uma tabela específica.
     */
    public function start(array $postData): bool 
    {
        $clientId = (int)($postData['cliente_id'] ?? 0);
        $tablesToMigrate = $postData['tables_to_migrate'] ?? [];
        $userId = $_SESSION['user_id'];

        if (empty($clientId) || empty($tablesToMigrate)) {
            throw new InvalidArgumentException('Cliente e lista de tabelas são obrigatórios.');
        }
        
        // Chama o orquestrador principal com a lista de tabelas
        $this->migrationService->runFullMigration($userId, $clientId, $tablesToMigrate);
        return true;
    }

    /**
     * Envia um sinal de cancelamento para a migração.
     */
    public function cancel(array $postData): bool 
    {
        $clientId = (int)($postData['cliente_id'] ?? 0);
        $userId = $_SESSION['user_id'];

        // Apenas atualiza o status para 'cancelado' através do serviço
        return $this->migrationService->updateStatus($userId, $clientId, 'cancelado');
    }

    /**
     * Retorna o status atual da migração.
     */
    public function getStatus(int $clientId): string
    {
        $userId = $_SESSION['user_id'];
        return $this->migrationService->getStatus($userId, $clientId);
    }
}