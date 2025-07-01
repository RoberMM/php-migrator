<?php

// src/Entity/System.php
namespace App\Entity;

class System
{
    public function __construct(
        public string $nome, // <-- 'readonly' removido daqui
        public readonly int $userId,
        public ?int $id = null
    ) {}
}