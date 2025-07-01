<?php
// src/Repository/UserRepositoryInterface.php
namespace App\Repository;

use App\Entity\User;

interface UserRepositoryInterface
{
    /**
     * Busca um usuário pelo seu login.
     * Retorna um objeto User se encontrar, ou null se não encontrar.
     */
    public function findByLogin(string $login): ?User;

    public function findAll(): array;

    public function save(User $user): User;
}