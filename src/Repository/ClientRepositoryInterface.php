<?php
// src/Repository/ClientRepositoryInterface.php
namespace App\Repository;
use App\Entity\Client;

interface ClientRepositoryInterface 
{
    /** @return Client[] */
    public function findBySystemId(int $systemId): array;
    
    public function save(Client $client): Client;

    public function find(int $id): ?Client;
    
    public function delete(int $id): bool;

    /**
     * NOVO: Busca todos os clientes.
     * @return Client[]
     */
    public function findAll(int $userId): array;
}