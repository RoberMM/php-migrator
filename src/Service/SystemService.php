<?php
// src/Service/SystemService.php
namespace App\Service;

use App\Repository\SystemRepositoryInterface;
use App\Repository\ClientRepositoryInterface;
use App\Repository\ClientConfigRepositoryInterface;

class SystemService
{
    public function __construct(
        private SystemRepositoryInterface $systemRepo,
        private ClientRepositoryInterface $clientRepo,
        private ClientConfigRepositoryInterface $configRepo
    ) {}

    /**
     * Orquestra a exclusão de um sistema e todos os seus dados filhos.
     */
    public function deleteSystemAndChildren(int $systemId): bool
    {
        // 1. Encontra todos os clientes do sistema a ser excluído.
        $clients = $this->clientRepo->findBySystemId($systemId);
        if (!empty($clients)) {
            $clientIds = array_map(fn($c) => $c->id, $clients);
            
            // 2. Deleta todas as configurações associadas a esses clientes.
            $this->configRepo->deleteByClientIds($clientIds);
        }

        // 3. Deleta o sistema. O repositório já tem a lógica para deletar os clientes junto.
        return $this->systemRepo->delete($systemId);
    }
}