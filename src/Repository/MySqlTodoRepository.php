<?php
// src/Repository/MySqlTodoRepository.php
namespace App\Repository;

use App\Entity\Todo;
use PDO;

class MySqlTodoRepository implements TodoRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function findAllByUser(int $userId): array
    {
        // Lógica de getTodos.php
        $sql = "SELECT id, user_id, cliente_id, title, tempo_prev, completed, ordem FROM todos WHERE user_id = :userId ORDER BY completed, ordem";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);

        $todos = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $todos[] = new Todo(
                userId: (int)$row['user_id'],
                title: $row['title'],
                id: (int)$row['id'],
                clienteId: $row['cliente_id'] ? (int)$row['cliente_id'] : null,
                tempoPrev: $row['tempo_prev'],
                completed: (bool)$row['completed'],
                ordem: (int)$row['ordem']
            );
        }
        return $todos;
    }

    public function save(Todo $todo): Todo
    {
        // Lógica de saveTodo.php (unificando INSERT e UPDATE)
        if ($todo->id) {
            // UPDATE (Editar)
            $sql = "UPDATE todos SET cliente_id = :clienteId, title = :title, tempo_prev = :tempoPrev WHERE id = :id AND user_id = :userId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':clienteId' => $todo->clienteId,
                ':title' => $todo->title,
                ':tempoPrev' => $todo->tempoPrev,
                ':id' => $todo->id,
                ':userId' => $todo->userId
            ]);
        } else {
            // INSERT (Criar)
            // 1. Pega a próxima ordem
            $nextQ = $this->pdo->prepare("SELECT COALESCE(MAX(ordem), -1) + 1 FROM todos WHERE user_id = ?");
            $nextQ->execute([$todo->userId]);
            $ordem = (int)$nextQ->fetchColumn();
            $todo->ordem = $ordem;

            // 2. Insere o novo registro
            $sql = "INSERT INTO todos (user_id, cliente_id, title, tempo_prev, ordem, completed) VALUES (:userId, :clienteId, :title, :tempoPrev, :ordem, :completed)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':userId' => $todo->userId,
                ':clienteId' => $todo->clienteId,
                ':title' => $todo->title,
                ':tempoPrev' => $todo->tempoPrev,
                ':ordem' => $todo->ordem,
                ':completed' => (int)$todo->completed
            ]);
            $todo->id = (int)$this->pdo->lastInsertId();
        }
        return $todo;
    }

    public function delete(int $id, int $userId): bool
    {
        // Lógica de deleteTodo.php
        $sql = "DELETE FROM todos WHERE id = :id AND user_id = :userId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':userId' => $userId]);
    }

    public function toggle(int $id, int $userId): bool
    {
        // Lógica de toggleTodo.php
        $sql = "UPDATE todos SET completed = 1 - completed WHERE id = :id AND user_id = :userId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':userId' => $userId]);
    }

    public function updateOrder(array $orderData, int $userId): bool
    {
        // Lógica de updateOrder.php
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE todos SET ordem = :ordem WHERE id = :id AND user_id = :userId");
            foreach ($orderData as $item) {
                $stmt->execute([
                    ':ordem' => (int)$item['ordem'],
                    ':id' => (int)$item['id'],
                    ':userId' => $userId
                ]);
            }
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e; // Lança a exceção para o Controller/Router tratar
        }
    }
}