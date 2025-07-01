<?php
namespace App\Dto;

class DashboardData {
     public function __construct(
        public int $total_migracoes = 0,
        public int $registros_migrados = 0,
        public float $taxa_sucesso = 0.0,
        public int $tempo_medio = 0,
        public array $logs = [],
        public array $chartData = [] // <-- ADICIONE ESTA LINHA
    ) {}
}