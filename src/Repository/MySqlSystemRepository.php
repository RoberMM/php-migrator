<?php

// src/Repository/MySqlSystemRepository.php
namespace App\Repository;

use App\Entity\System;
use PDO;

class MySqlSystemRepository implements SystemRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function findByUserId(int $userId): array
    {
        $sql = "SELECT * FROM sistemas WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        $systems = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $systems[] = new System(
                id: $row['id'],
                nome: $row['nome'],
                userId: $row['user_id']
            );
        }
        return $systems;
    }

    public function find(int $id): ?System
    {
        $sql = "SELECT * FROM sistemas WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new System(
            id: (int)$data['id'],
            nome: $data['nome'],
            userId: (int)$data['user_id']
        );
    }

     public function save(System $system): System
    {
        if ($system->id === null) {
            // LÓGICA DE INSERT (já estava correta)
            $sql = "INSERT INTO sistemas (nome, user_id) VALUES (:name, :user_id)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':name' => $system->nome, ':user_id' => $system->userId]);
            $system->id = (int)$this->pdo->lastInsertId();
        } else {
            // LÓGICA DE UPDATE (a peça que faltava)
            $sql = "UPDATE sistemas SET nome = :name WHERE id = :id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name' => $system->nome,
                ':id' => $system->id,
                ':user_id' => $system->userId
            ]);
        }
        return $system;
    }

    public function delete(int $id): bool
    {
        // A lógica de negócio de apagar em cascata fica aqui, dentro de uma transação.
        $this->pdo->beginTransaction();
        try {
            // 1) Apaga os clientes do sistema
            $delCli = $this->pdo->prepare('DELETE FROM clientes WHERE sistema_id = :id');
            $delCli->execute([':id' => $id]);

            // 2) Apaga o próprio sistema
            $delSys = $this->pdo->prepare('DELETE FROM sistemas WHERE id = :id');
            $delSys->execute([':id' => $id]);

            // Se nenhuma linha foi afetada, o sistema não existia.
            if ($delSys->rowCount() === 0) {
                throw new \RuntimeException('Sistema não encontrado para exclusão.');
            }

            $this->pdo->commit();
            return true;

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            // Lança a exceção para o Controller/Router tratar
            throw $e; 
        }
    }
}