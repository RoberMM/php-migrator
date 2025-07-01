<?php
// src/Controller/TodoController.php
namespace App\Controller;

use App\Repository\TodoRepositoryInterface;
use App\Entity\Todo;
use InvalidArgumentException;

class TodoController 
{
    public function __construct(private TodoRepositoryInterface $todoRepository) {}

    /**
     * Busca e retorna a lista de tarefas do usuário logado.
     */
    public function list(): array 
    {
        // O repositório faz a busca e já retorna um array de objetos Todo.
        return $this->todoRepository->findAllByUser($_SESSION['user_id']);
    }

    /**
     * Salva (cria ou edita) um item da lista.
     * Retorna a entidade Todo salva.
     */
    public function save(array $data): Todo 
    {
        // 1. Coleta e valida os dados de entrada
        $title = trim($data['title'] ?? '');
        if (empty($title)) {
            throw new InvalidArgumentException('A descrição da tarefa é obrigatória.');
        }

        $id = !empty($data['id']) ? (int)$data['id'] : null;
        $clienteId = !empty($data['cliente_id']) ? (int)$data['cliente_id'] : null;
        $tempoPrev = trim($data['tempo_prev'] ?? '');

        // 2. Cria a entidade Todo com os dados
        $todo = new Todo(
            userId: $_SESSION['user_id'],
            title: $title,
            id: $id,
            clienteId: $clienteId,
            tempoPrev: $tempoPrev
        );
        
        // 3. Chama o repositório para salvar e retorna o resultado
        // O método save do repositório já lida com INSERT ou UPDATE
        return $this->todoRepository->save($todo);
    }

    /**
     * Exclui um item da lista.
     * Retorna true se a exclusão foi bem-sucedida.
     */
    public function delete(int $id): bool 
    {
        // Apenas delega a chamada para o repositório, passando o ID do usuário para segurança.
        return $this->todoRepository->delete($id, $_SESSION['user_id']);
    }
    
    /**
     * Alterna o estado de 'concluído' de um item.
     * Retorna true se a alteração foi bem-sucedida.
     */
    public function toggle(array $data): bool
    {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            throw new InvalidArgumentException('ID da tarefa inválido.');
        }

        return $this->todoRepository->toggle($id, $_SESSION['user_id']);
    }

    /**
     * Atualiza a ordem de uma lista de itens.
     * Retorna true se a alteração foi bem-sucedida.
     */
    public function updateOrder(array $data): bool
    {
        $rows = $data['rows'] ?? [];
        if (empty($rows) || !is_array($rows)) {
            throw new InvalidArgumentException('Dados de ordenação inválidos.');
        }
        
        return $this->todoRepository->updateOrder($rows, $_SESSION['user_id']);
    }
}