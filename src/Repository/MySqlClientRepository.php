<?php

// src/Repository/MySqlClientRepository.php
namespace App\Repository;

use App\Entity\Client;
use PDO;

class MySqlClientRepository implements ClientRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function findBySystemId(int $systemId): array
    {
        $sql = "SELECT * FROM clientes WHERE sistema_id = :systemId ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(":systemId", $systemId, PDO::PARAM_INT);
        $stmt->execute();

        $clients = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $clients[] = new Client(
                id: $row['id'],
                sistema_id: $row['sistema_id'],
                nome: $row['nome'],
                created_at: $row['created_at']
            );
        }
        return $clients;
    }
    
    // Método unificado para criar e atualizar
    public function save(Client $client): Client
    {
        if ($client->id === null) {
            // LÓGICA DE INSERT (já estava correta)
            $sql = "INSERT INTO clientes (sistema_id, nome) VALUES (:systemId, :name)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':systemId' => $client->sistema_id, ':name' => $client->nome]);
            $client->id = (int)$this->pdo->lastInsertId();
        } else {
            // LÓGICA DE UPDATE (a peça que faltava)
            $sql = "UPDATE clientes SET nome = :name, sistema_id = :systemId WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name' => $client->nome,
                ':systemId' => $client->sistema_id,
                ':id' => $client->id
            ]);
        }
        return $client;
    }

    public function find(int $id): ?Client
    {
        $sql = "SELECT * FROM clientes WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new Client(
            id: (int)$data['id'],
            sistema_id: (int)$data['sistema_id'],
            nome: $data['nome'],
            created_at: $data['created_at']
        );
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM clientes WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * ATUALIZADO: Busca todos os clientes pertencentes a um usuário específico.
     *
     * @param int $userId O ID do usuário logado.
     * @return array Uma lista de clientes.
     */
    public function findAll(int $userId): array
    {
        // Esta query agora junta a tabela de clientes com a de sistemas
        // para poder filtrar pelo user_id que está na tabela de sistemas.
        $sql = "
            SELECT c.id, c.nome 
            FROM clientes c
            INNER JOIN sistemas s ON c.sistema_id = s.id
            WHERE s.user_id = :user_id
            ORDER BY c.nome ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}