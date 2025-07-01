<?php
// src/Controller/TemplateController.php
namespace App\Controller;

use App\Repository\MySqlTemplateRepository;
use App\Repository\SystemRepositoryInterface;
use App\Repository\ClientRepositoryInterface;
use App\Entity\Template;
use InvalidArgumentException;

class TemplateController
{
    public function __construct(
        private MySqlTemplateRepository $templateRepository,
        private SystemRepositoryInterface $systemRepository,
        private ClientRepositoryInterface $clientRepository,
        private string $basePath
    ) {}

    public function showPage(): void
    {
        // Controle de Acesso (continua igual)
        if (($_SESSION['user_data']['nivel'] ?? 'none') !== 'admin') {
            http_response_code(403);
            require __DIR__ . '/../View/errors/403.php';
            exit;
        }

        // Busca os dados para o menu lateral
        $userId = $_SESSION['user_id'];
        $systems = $this->systemRepository->findByUserId($userId);
        $clientsBySystem = [];
        foreach ($systems as $system) {
            $clientsBySystem[$system->id] = $this->clientRepository->findBySystemId($system->id);
        }

        // Prepara os dados para a view (versão simplificada)
        $viewData = [
            'pageTitle' => 'Gerenciamento de Templates',
            'userData' => $_SESSION['user_data'],
            'basePath' => $this->basePath,
            'systems' => $systems,
            'clientsBySystem' => $clientsBySystem,
            // 'allClients' => $this->clientRepository->findAll(), // <-- LINHA REMOVIDA
            'selectedClientId' => 0 
        ];

        require_once __DIR__ . '/../View/templates_view.php';
    }

    /**
     * API: Retorna a lista de todos os templates como um array.
     * A responsabilidade de formatar como JSON fica para o roteador.
     */
    public function list(): array
    {
        return $this->templateRepository->findAll();
    }

    /**
     * API: Cria um novo template e o retorna.
     */
    public function create(array $postData, array $fileData): bool
    {
        $nome = $postData['nome'] ?? '';
        $jsonFile = $fileData['config_file'] ?? null;

        if (empty($nome) || !$jsonFile || $jsonFile['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Nome e arquivo JSON são obrigatórios.');
        }
        
        $configJson = file_get_contents($jsonFile['tmp_name']);
        $configArray = json_decode($configJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Arquivo JSON inválido.');
        }
        
        $template = new Template(
            nome: $nome,
            config_padrao: $configArray
        );

        return $this->templateRepository->create($template);
    }

    /**
     * API: Exclui um template e retorna um booleano de sucesso.
     */
    public function delete(int $id): bool
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID do template inválido.');
        }
        return $this->templateRepository->delete($id);
    }
}