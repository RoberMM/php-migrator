<?php
// src/Repository/MySqlClientConfigRepository.php
namespace App\Repository;

use App\Entity\ClientConfig;
use PDO;

class MySqlClientConfigRepository implements ClientConfigRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function find(int $userId, int $clientId): ?ClientConfig
    {
        $sql = "SELECT * FROM clientes_config WHERE user_id = :user_id AND cliente_id = :cliente_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':cliente_id' => $clientId]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $configData = json_decode($data['json_config'], true);
        if (is_string($configData)) {
            $configData = json_decode($configData, true);
        }
        if (!is_array($configData)) {
            $configData = [];
        }

        return new ClientConfig(
            id: (int)$data['id'],
            userId: (int)$data['user_id'],
            clientId: (int)$data['cliente_id'],
            config: $configData,
            migrationStatus: $data['status_migracao']
        );
    }
    
    public function save(ClientConfig $config): ClientConfig
    {
        $configToSave = $config->config;
        $jsonStringToSave = is_array($configToSave) ? json_encode($configToSave, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $configToSave;
        
        $sql = "
            INSERT INTO clientes_config (id, user_id, cliente_id, json_config, status_migracao)
            VALUES (:id, :user_id, :cliente_id, :json_config, :status_migracao)
            ON DUPLICATE KEY UPDATE
                json_config = VALUES(json_config),
                status_migracao = VALUES(status_migracao)
        ";
        
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([
            ':id' => $config->id,
            ':user_id' => $config->userId,
            ':cliente_id' => $config->clientId,
            ':json_config' => $jsonStringToSave,
            ':status_migracao' => $config->migrationStatus
        ]);
        
        if ($config->id === null) {
            $config->id = (int)$this->pdo->lastInsertId();
        }

        return $config;
    }

    public function delete(int $userId, int $clientId): bool
    {
        $sql = "DELETE FROM clientes_config WHERE user_id = :user_id AND cliente_id = :cliente_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':user_id' => $userId, ':cliente_id' => $clientId]);
    }

    /**
     * NOVO: Implementação do método para deletar múltiplas configurações.
     */
    public function deleteByClientIds(array $clientIds): bool
    {
        // Se a lista de IDs estiver vazia, não há nada a fazer.
        if (empty($clientIds)) {
            return true;
        }

        // Para usar a cláusula "IN" de forma segura com prepared statements,
        // precisamos criar um placeholder (?) para cada ID no array.
        $placeholders = implode(',', array_fill(0, count($clientIds), '?'));

        $sql = "DELETE FROM clientes_config WHERE cliente_id IN ($placeholders)";
        
        $stmt = $this->pdo->prepare($sql);
        
        // O PDO é inteligente o suficiente para mapear cada item do array
        // para cada '?' na query.
        return $stmt->execute($clientIds);
    }
}