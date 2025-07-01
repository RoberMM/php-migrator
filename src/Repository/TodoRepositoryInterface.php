<?php
// src/Repository/TodoRepositoryInterface.php
namespace App\Repository;

use App\Entity\Todo;

interface TodoRepositoryInterface
{
    /**
     * Busca todos os itens de um usuário.
     * Substitui a lógica de: getTodos.php
     * @return Todo[]
     */
    public function findAllByUser(int $userId): array;

    /**
     * Salva um item (cria ou atualiza).
     * Substitui a lógica de: saveTodo.php
     */
    public function save(Todo $todo): Todo;

    /**
     * Exclui um item.
     * Substitui a lógica de: deleteTodo.php
     */
    public function delete(int $id, int $userId): bool;

    /**
     * Alterna o estado 'completed' de um item.
     * Substitui a lógica de: toggleTodo.php
     */
    public function toggle(int $id, int $userId): bool;

    /**
     * Atualiza a ordem de múltiplos itens.
     * Substitui a lógica de: updateOrder.php
     */
    public function updateOrder(array $orderData, int $userId): bool;
}