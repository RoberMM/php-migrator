<?php

// src/Entity/Client.php
namespace App\Entity;

class Client
{
    public function __construct(
        public int $sistema_id, // <-- 'readonly' removido daqui
        public string $nome,
        public ?int $id = null,
        public ?string $created_at = null
    ) {}
}