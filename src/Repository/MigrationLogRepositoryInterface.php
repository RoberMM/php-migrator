<?php
namespace App\Repository;

interface MigrationLogRepositoryInterface {
    public function getDashboardStats(array $filters, int $userId): array;
}