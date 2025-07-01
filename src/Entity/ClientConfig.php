<?php

// src/Entity/ClientConfig.php
namespace App\Entity;

class ClientConfig
{
    public function __construct(
        public readonly int $userId,
        public readonly int $clientId,
        public array $config, // Armazenamos como array para facilitar a manipulação
        public string $migrationStatus = 'livre',
        public ?int $id = null
    ) {}
}