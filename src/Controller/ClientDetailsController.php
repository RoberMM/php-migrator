<?php
// src/Controller/ClientDetailsController.php
namespace App\Controller;

use App\Repository\ClientConfigRepositoryInterface;
use App\Repository\ClientRepositoryInterface;
use App\Repository\SystemRepositoryInterface; // <-- 1. Adicionamos a dependência do SystemRepository
use App\Entity\ClientConfig;

class ClientDetailsController
{
    /**
     * O construtor agora recebe todas as dependências necessárias, incluindo
     * o SystemRepository (para o menu) e o basePath (para os links).
     */
    public function __construct(
        private ClientConfigRepositoryInterface $clientConfigRepository,
        private ClientRepositoryInterface $clientRepository,
        private SystemRepositoryInterface $systemRepository, // <-- 2. Dependência injetada
        private string $basePath                        // <-- 2. Dependência injetada
    ) {}

    /**
     * Exibe a página de detalhes e configuração para um cliente específico.
     */
    public function show(int $clientId): void
    {
        $userId = $_SESSION['user_id'] ?? 0;
        $userData = $_SESSION['user_data'] ?? [];

        // Busca o cliente que é o foco da página
        $client = $this->clientRepository->find($clientId);
        if (!$client) {
            http_response_code(404);
            require __DIR__ . '/../View/errors/404.php';
            exit;
        }

        // Busca a configuração do cliente
        $config = $this->clientConfigRepository->find($userId, $clientId);

        // LÓGICA DE NEGÓCIO: Se não houver config, cria e salva uma padrão.
        if ($config === null) {
            // Definimos concretamente a configuração padrão
            $defaultConfigData = [
                'conexao' => [
                    'origem' => ['tipo' => 'mysql', 'dbName' => '', 'ip' => '', 'porta' => '', 'usuario' => '', 'senha' => ''],
                    'destino' => ['tipo' => 'mysql', 'dbName' => '', 'ip' => '', 'porta' => '', 'usuario' => '', 'senha' => '']
                ],
                'tabelaAcao' => [
                    'migrar' => false, 'campo_de' => '', 'query' => '', 'sigla' => '', 
                    'sigla_funcs' => [], 'descricao' => '', 'descricao_funcs' => []
                ],
                'status_migracao' => 'livre'
            ];
            $config = new ClientConfig(userId: $userId, clientId: $clientId, config: $defaultConfigData);
            $config = $this->clientConfigRepository->save($config);
        }

        // --- 3. ATUALIZAÇÃO: Prepara dados para o LAYOUT (Header e Sidebar) ---
        // Esta página também precisa dos dados do menu para renderizar o layout corretamente.
        $systems = $this->systemRepository->findByUserId($userId);
        $clientsBySystem = [];
        foreach ($systems as $system) {
            $clientsBySystem[$system->id] = $this->clientRepository->findBySystemId($system->id);
        }

        // --- 4. Prepara todos os dados para a View ---
        $viewData = [
            'pageTitle' => 'Detalhes de ' . htmlspecialchars($client->nome),
            'client' => $client,
            'config' => $config,
            'userData' => $userData,
            'systems' => $systems, // <-- Agora com dados reais
            'clientsBySystem' => $clientsBySystem, // <-- Agora com dados reais
            'selectedClientId' => $clientId,
            'basePath' => $this->basePath // <-- Agora passando o basePath
        ];
        
        // Renderiza a view principal da página
        require_once __DIR__ . '/../View/client_details_view.php';
    }
}