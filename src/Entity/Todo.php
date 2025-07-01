<?php
// src/Entity/Todo.php
namespace App\Entity;

class Todo
{
    public function __construct(
        public int $userId,
        public string $title,
        public ?int $id = null,
        public ?int $clienteId = null,
        public ?string $tempoPrev = null,
        public bool $completed = false,
        public int $ordem = 0
    ) {}
}