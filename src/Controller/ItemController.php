<?php
// src/Controller/ItemController.php
namespace App\Controller;

use App\Repository\SystemRepositoryInterface;
use App\Repository\ClientRepositoryInterface; // <-- ADICIONE ESTA LINHA
use App\Repository\ClientConfigRepositoryInterface;
use App\Repository\MySqlTemplateRepository; 
use App\Service\SystemService;
use App\Entity\System;
use App\Entity\Client;
use App\Entity\ClientConfig; 
use InvalidArgumentException;
use RuntimeException;

class ItemController
{
    // O construtor agora está correto e encontrará a interface
    public function __construct(
        private SystemRepositoryInterface $systemRepository,
        private ClientRepositoryInterface $clientRepository,
        private SystemService $systemService,
        private ClientConfigRepositoryInterface $configRepository,
        private MySqlTemplateRepository $templateRepository
    ) {}

    /**
     * Cria um novo Sistema ou um novo Cliente.
     * Se um cliente for criado com um templateId, sua configuração inicial
     * será preenchida com base no template.
     */
    public function create(array $postData): System|Client
    {
        $type = $postData['type'] ?? '';
        $name = trim($postData['name'] ?? '');
        $systemId = (int)($postData['systemId'] ?? 0);
        $templateId = (int)($postData['template_id'] ?? 0);

        if (empty($type) || empty($name)) {
            throw new InvalidArgumentException('Tipo e nome são obrigatórios.');
        }

        if ($type === 'system') {
            $system = new System(nome: $name, userId: $_SESSION['user_id']);
            return $this->systemRepository->save($system);
        } 
        
        if ($type === 'client') {
            if (empty($systemId)) {
                throw new InvalidArgumentException('É necessário selecionar um sistema para o cliente.');
            }

            $client = new Client(sistema_id: $systemId, nome: $name);
            $savedClient = $this->clientRepository->save($client);

            $configJson = '{}'; // Configuração padrão: um JSON vazio
            if ($templateId > 0) {
                // Busca o objeto Template
                $template = $this->templateRepository->find($templateId);
                
                if ($template && !empty($template->config_padrao)) {
                    // O repositório já nos deu um array, então o codificamos de volta para JSON
                    $configJson = json_encode($template->config_padrao);
                }
            }

            // Cria a entidade de configuração e a salva
            $newConfig = new ClientConfig(
                userId: $_SESSION['user_id'],
                clientId: $savedClient->id,
                config: json_decode($configJson, true) ?? []
            );
            $this->configRepository->save($newConfig);
            
            return $savedClient;
        }

        throw new InvalidArgumentException('Tipo de item inválido.');
    }

    public function update(array $data): System|Client
    {
        $type = $data['type'] ?? '';
        $id = (int)($data['id'] ?? 0);
        $name = trim($data['name'] ?? ''); // CORREÇÃO: Lendo 'name'

        if (empty($type) || $id <= 0 || $name === '') {
            throw new InvalidArgumentException('Dados inválidos para atualização.');
        }

        if ($type === 'system') {
            $system = $this->systemRepository->find($id);
            if (!$system) throw new RuntimeException('Sistema não encontrado.');
            $system->nome = $name;
            return $this->systemRepository->save($system);
        }
        
        if ($type === 'client') {
            $client = $this->clientRepository->find($id);
            if (!$client) throw new RuntimeException('Cliente não encontrado.');
            
            $client->nome = $name;
            // CORREÇÃO: Adicionada a lógica para mover o cliente de sistema
            if (isset($data['systemId'])) {
                $client->sistema_id = (int)$data['systemId'];
            }
            return $this->clientRepository->save($client);
        }

        throw new InvalidArgumentException('Tipo de item inválido.');
    }

    public function delete(array $data): bool
    {
        $type = $data['type'] ?? '';
        $id = (int)($data['id'] ?? 0);

        if (empty($type) || $id <= 0) {
            throw new InvalidArgumentException('Dados inválidos para exclusão.');
        }

        if ($type === 'system') {
            // CORREÇÃO: Delega a operação complexa para o SystemService
            return $this->systemService->deleteSystemAndChildren($id);
        } 
        
        if ($type === 'client') {
            // (Você precisará implementar a exclusão da config do cliente também, se necessário)
            return $this->clientRepository->delete($id);
        }

        return false; // Retorno padrão
    }
    
}