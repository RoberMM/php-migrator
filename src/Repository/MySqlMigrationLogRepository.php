<?php
namespace App\Repository;

use PDO;

class MySqlMigrationLogRepository implements MigrationLogRepositoryInterface {
    
    public function __construct(private PDO $pdo) {}

    /**
     * Busca todas as estatísticas e logs para o dashboard.
     * VERSÃO FINAL E COMPLETA:
     * - Calcula KPIs reais (taxa de sucesso, tempo médio).
     * - Busca dados do gráfico apenas da última execução de cada cliente.
     * - Filtra os logs corretamente por cliente, nível e evento.
     */
    public function getDashboardStats(array $filters, int $userId): array
    {
        // --- 1. PREPARAÇÃO DOS FILTROS ---
        $baseParams = [':user_id' => $userId];
        $baseConditions = 'user_id = :user_id';
        if (!empty($filters['cliente_id'])) {
            $baseConditions .= " AND cliente_id = :cliente_id";
            $baseParams[':cliente_id'] = (int)$filters['cliente_id'];
        }

        // --- 2. CÁLCULO DOS KPIs ---
        // Usamos uma sub-query para pegar apenas a ÚLTIMA execução de cada cliente que se encaixa no filtro
        $kpiSubQuery = "SELECT MAX(id) FROM migration_runs WHERE $baseConditions GROUP BY cliente_id";
        
        $kpiSql = "
            SELECT 
                COUNT(id) as total_runs, 
                SUM(CASE WHEN status = 'concluida' THEN 1 ELSE 0 END) as successful_runs,
                SUM(total_rows_processed) as total_records, 
                AVG(TIMESTAMPDIFF(SECOND, start_time, end_time)) as avg_time
            FROM migration_runs
            WHERE id IN ($kpiSubQuery)
        ";
        $stmtKpi = $this->pdo->prepare($kpiSql);
        $stmtKpi->execute($baseParams);
        $kpiData = $stmtKpi->fetch(PDO::FETCH_ASSOC);

        $total_migracoes = (int)($kpiData['total_runs'] ?? 0);
        $registros_migrados = (int)($kpiData['total_records'] ?? 0);
        $tempo_medio = (int)($kpiData['avg_time'] ?? 0);
        $taxa_sucesso = ($total_migracoes > 0) ? round(((int)($kpiData['successful_runs'] ?? 0) / $total_migracoes) * 100) : 0;

        // --- 3. BUSCA DOS DADOS DO GRÁFICO ---
        $chartSql = "SELECT stats_by_table FROM migration_runs WHERE id IN ($kpiSubQuery) AND status = 'concluida'";
        $stmtChart = $this->pdo->prepare($chartSql);
        $stmtChart->execute($baseParams);
        $chartRawData = $stmtChart->fetchAll(PDO::FETCH_COLUMN);

        $chartData = ['labels' => [], 'sourceData' => [], 'destData' => []];
        foreach($chartRawData as $jsonStat) {
            $statsArray = json_decode($jsonStat ?? '[]', true);
            if (is_array($statsArray)) {
                if (isset($statsArray[0]) && is_array($statsArray[0])) $statsArray = $statsArray[0];
                foreach($statsArray as $stat) {
                    if(is_array($stat)) {
                        $chartData['labels'][] = $stat['table'] ?? 'N/A';
                        $chartData['sourceData'][] = $stat['source_count'] ?? 0;
                        $chartData['destData'][] = $stat['destination_count'] ?? 0;
                    }
                }
            }
        }
        
        // --- 4. BUSCA DOS LOGS FILTRADOS ---
        $logsConditions = [];
        $logsParams = [];
        // Se um cliente foi selecionado, os logs devem pertencer às execuções dele
        if (!empty($filters['cliente_id'])) {
            $logsConditions[] = "migration_run_id IN ($kpiSubQuery)";
            $logsParams = array_merge($logsParams, $baseParams);
        }
        if (!empty($filters['nivel'])) {
            $logsConditions[] = "nivel = :nivel";
            $logsParams[':nivel'] = $filters['nivel'];
        }
        if (!empty($filters['evento'])) {
            $logsConditions[] = "evento LIKE :evento";
            $logsParams[':evento'] = '%' . $filters['evento'] . '%';
        }
        $logsWhereClause = empty($logsConditions) ? "" : "WHERE " . implode(" AND ", $logsConditions);

        $sqlLogs = "SELECT nivel, evento, mensagem, detalhes FROM migration_logs $logsWhereClause ORDER BY id DESC LIMIT 100";
        $stmtLogs = $this->pdo->prepare($sqlLogs);
        $stmtLogs->execute($logsParams);
        $logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

        // --- 5. RETORNO DE TODOS OS DADOS ---
        return [
            'total_migracoes' => $total_migracoes,
            'registros_migrados' => $registros_migrados,
            'logs' => $logs,
            'chartData' => $chartData,
            'taxa_sucesso' => $taxa_sucesso,
            'tempo_medio' => $tempo_medio,
        ];
    }

    /**
     * Constrói a cláusula WHERE e os parâmetros para as consultas de log
     * com base nos filtros fornecidos.
     *
     * @param array $filters O array de filtros (vindo de $_GET).
     * @param int $userId O ID do usuário logado, para filtrar os logs.
     * @return array [string, array] A string da cláusula WHERE e o array de parâmetros.
     */
    private function buildWhereClause(array $filters, int $userId): array
    {
        $conditions = ['user_id = :user_id'];
        $params = [':user_id' => $userId];

        if (!empty($filters['nivel'])) {
            $conditions[] = "nivel = :nivel";
            $params[':nivel'] = $filters['nivel'];
        }

        if (!empty($filters['evento'])) {
            $conditions[] = "evento LIKE :evento";
            $params[':evento'] = '%' . $filters['evento'] . '%';
        }
        
        // Retorna a string pronta para ser usada, ex: "WHERE user_id = :user_id AND nivel = :nivel"
        return ["WHERE " . implode(" AND ", $conditions), $params];
    }
}