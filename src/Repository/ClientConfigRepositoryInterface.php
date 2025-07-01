<?php

// src/Repository/ClientConfigRepositoryInterface.php
namespace App\Repository;

use App\Entity\ClientConfig;

interface ClientConfigRepositoryInterface
{
    public function find(int $userId, int $clientId): ?ClientConfig;

    public function save(ClientConfig $config): ClientConfig;

    public function delete(int $userId, int $clientId): bool;

    /**
     * NOVO: Deleta todas as configurações para uma lista de IDs de cliente.
     * @param int[] $clientIds Um array de IDs de clientes.
     */
    public function deleteByClientIds(array $clientIds): bool;
}