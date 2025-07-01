<?php
namespace App\Entity;

class MigrationLog {
    public function __construct(
        public int $id,
        public string $data,
        public ?int $cliente_id,
        public string $evento,
        public string $mensagem,
        public string $nivel,
        public ?int $quantidade = 0
    ) {}
}