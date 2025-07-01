<?php
// src/Entity/Template.php
namespace App\Entity;

class Template
{
    public function __construct(
        public readonly string $nome,
        public readonly array $config_padrao,
        public ?int $id = null
    ) {}
}