<?php

// src/Repository/SystemRepositoryInterface.php
namespace App\Repository;

use App\Entity\System;

interface SystemRepositoryInterface
{
    /** @return System[] */
    public function findByUserId(int $userId): array;

    public function save(System $system): System;

    public function delete(int $id): bool;

    public function find(int $id): ?System;
}