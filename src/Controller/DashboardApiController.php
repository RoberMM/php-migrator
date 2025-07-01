<?php
// src/Controller/DashboardApiController.php
namespace App\Controller;

use App\Dto\DashboardData;
use App\Repository\MigrationLogRepositoryInterface;

class DashboardApiController {
    public function __construct(private MigrationLogRepositoryInterface $logRepository) {}

    public function getData(array $filters, int $userId): DashboardData {
        // 1. O repositório já nos dá tudo que precisamos, incluindo o chartData
        $stats = $this->logRepository->getDashboardStats($filters, $userId);
        
        // 2. CORREÇÃO: Passamos o chartData para dentro do nosso objeto de resposta
        return new DashboardData(
            total_migracoes: $stats['total_migracoes'],
            registros_migrados: $stats['registros_migrados'],
            logs: $stats['logs'],
            chartData: $stats['chartData'] // <-- ADICIONE ESTA LINHA
        );
    }
}