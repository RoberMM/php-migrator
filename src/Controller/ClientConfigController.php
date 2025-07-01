<?php
// src/Controller/ClientConfigController.php
namespace App\Controller;

use App\Repository\ClientConfigRepositoryInterface;
use App\Repository\ClientRepositoryInterface;
use App\Entity\ClientConfig;

class ClientConfigController
{
    /**
     * O construtor injeta os repositórios necessários.
     */
    public function __construct(
        private ClientConfigRepositoryInterface $configRepo,
        private ClientRepositoryInterface $clientRepo
    ) {}

    /**
     * Atualiza a configuração JSON de um cliente (usado pelo auto-save).
     *
     * @param int   $clientId O ID do cliente a ser atualizado.
     * @param array $formData Os dados completos do formulário.
     * @return ClientConfig A entidade de configuração salva.
     */
    public function update(int $clientId, array $formData): ClientConfig
    {
        $userId = $_SESSION['user_id'];

        // CORREÇÃO: Usando a propriedade correta '$this->configRepo'
        $configEntity = $this->configRepo->find($userId, $clientId);

        if (!$configEntity) { // Cria uma nova configuração se não existir
            $configEntity = new ClientConfig(userId: $userId, clientId: $clientId, config: []);
        }

        // Atualiza o array de configuração com os novos dados do formulário
        $configEntity->config = $formData;

        // CORREÇÃO: Usando a propriedade correta '$this->configRepo'
        return $this->configRepo->save($configEntity);
    }

    /**
     * Gera e força o download de um arquivo JSON com a configuração do cliente.
     */
    public function exportJson(int $clientId): void
    {
        $userId = $_SESSION['user_id'];
        $configEntity = $this->configRepo->find($userId, $clientId);

        if (!$configEntity) {
            http_response_code(404);
            die("Configuração não encontrada para este cliente.");
        }

        // Pega o nome do cliente para criar um nome de arquivo amigável
        $client = $this->clientRepo->find($clientId);
        // Remove caracteres inválidos para nome de arquivo
        $clientName = $client ? strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '', $client->nome)) : 'config';
        $fileName = "migrador_config_{$clientName}.json";
        
        // Pega o conteúdo do JSON, formatado para fácil leitura
        $jsonContent = json_encode($configEntity->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Define os cabeçalhos HTTP para forçar o navegador a baixar o arquivo
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($jsonContent));

        // Envia o conteúdo e encerra o script para não enviar mais nada
        echo $jsonContent;
        exit;
    }
}