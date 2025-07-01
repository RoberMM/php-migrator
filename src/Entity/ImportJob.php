<?php
namespace App\Entity;

class ImportJob
{
    public function __construct(
        public ?int $id,
        public string $importId,
        public int $userId,
        public string $status,
        public string $payload, // Mantemos como string (JSON)
        public ?string $logs,
        public string $createdAt,
        public string $updatedAt
    ) {}
}