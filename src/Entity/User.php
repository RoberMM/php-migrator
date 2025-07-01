<?php
// src/Entity/User.php
namespace App\Entity;

class User
{
    /**
     * @param int $id O ID do usuário no banco.
     * @param string $login O login/username do usuário.
     * @param string $hashedPassword A senha já hashada, vinda do banco.
     */
    public function __construct(
        public readonly int $id,
        public readonly string $login,
        public string $nome,
        public readonly string $hashedPassword,
        public readonly string $nivel // <-- NOVA PROPRIEDADE
    ) {}
}