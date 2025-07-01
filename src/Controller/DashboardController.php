<?php
// src/Controller/DashboardController.php
namespace App\Controller;

use App\Repository\SystemRepositoryInterface;
use App\Repository\ClientRepositoryInterface;

class DashboardController
{
    /**
     * O construtor agora também recebe o $basePath, que é injetado pelo roteador.
     * Usamos a promoção de propriedades do construtor para um código mais limpo.
     */
    public function __construct(
        private SystemRepositoryInterface $systemRepository,
        private ClientRepositoryInterface $clientRepository,
        private string $basePath
    ) {}

    /**
     * Prepara os dados e renderiza a view principal do Dashboard.
     */
    public function show(): void
    {
        $userId = $_SESSION['user_id'] ?? 0;
        $userData = $_SESSION['user_data'] ?? [];

        // --- 1. Prepara dados para o LAYOUT (Header e Sidebar) ---
        $systems = $this->systemRepository->findByUserId($userId);
        
        // A lógica para montar a lista de clientes para o menu e filtros continua a mesma
        $clientsBySystem = [];
        $allClients = [];
        foreach ($systems as $system) {
            $clients = $this->clientRepository->findBySystemId($system->id);
            $clientsBySystem[$system->id] = $clients;
            foreach ($clients as $client) {
                $allClients[$client->id] = $client->nome;
            }
        }

        // Detecta qual cliente está selecionado na URL para destacar no menu
        $selectedClientId = 0;
        if (preg_match('/^\/cliente\/(\d+)$/', $_SERVER['REQUEST_URI'] ?? '', $matches)) {
            $selectedClientId = (int)$matches[1];
        }
        
        // --- 2. Agrupa TUDO que a View precisa para a CARGA INICIAL ---
        $viewData = [
            'userData' => $userData,
            'systems' => $systems,
            'clientsBySystem' => $clientsBySystem,
            'allClients' => $allClients,
            'selectedClientId' => $selectedClientId,
            'pageTitle' => 'Dashboard',
            'basePath' => $this->basePath // <-- Passamos o basePath para a view!
        ];

        // --- 3. Renderiza a View principal ---
        // A view 'dashboard_view.php' agora tem acesso a todos esses dados.
        require_once __DIR__ . '/../View/dashboard_view.php';
    }
}