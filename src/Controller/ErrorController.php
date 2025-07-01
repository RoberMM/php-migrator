<?php
// src/Controller/ErrorController.php
namespace App\Controller;

use App\Repository\SystemRepositoryInterface;
use App\Repository\ClientRepositoryInterface;

class ErrorController
{
    public function __construct(
        private SystemRepositoryInterface $systemRepository,
        private ClientRepositoryInterface $clientRepository,
        private string $basePath
    ) {}

    /**
     * Prepara os dados do layout e exibe a página de erro 404.
     */
    public function showNotFoundPage(): void
    {
        http_response_code(404);

        // Busca os dados para o menu lateral, para que ele seja renderizado corretamente
        $userId = $_SESSION['user_id'] ?? 0;
        $systems = $this->systemRepository->findByUserId($userId);
        $clientsBySystem = [];
        foreach ($systems as $system) {
            $clientsBySystem[$system->id] = $this->clientRepository->findBySystemId($system->id);
        }

        // Prepara os dados para a view
        $viewData = [
            'pageTitle' => 'Página Não Encontrada',
            'userData' => $_SESSION['user_data'] ?? null,
            'basePath' => $this->basePath,
            'systems' => $systems,
            'clientsBySystem' => $clientsBySystem,
            'allClients' => [], // Não necessário aqui
            'selectedClientId' => 0
        ];

        require_once __DIR__ . '/../View/errors/404.php';
    }
}