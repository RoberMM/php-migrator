<?php
// src/Service/MigrationService.php
namespace App\Service;

use App\Factory\ExternalConnectionFactory;
use App\Repository\ClientConfigRepositoryInterface;
use App\Util\AcronymGenerator;
use PDO;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class MigrationService
{
    private array $mappings;
    private PDO $appPdo; // Conexão com o banco da aplicação (migrador_bd)
    private array $autoIncrementCounters = []; //Guarda os contadores

    public function __construct(
        private ClientConfigRepositoryInterface $configRepository,
        PDO $appPdo
    ) {
        $this->mappings = require __DIR__ . '/../../config/mappings.php';
        $this->appPdo = $appPdo;
    }

    /**
     * NOVO: Orquestra a migração completa para múltiplas tabelas.
     * Este se torna o ponto de entrada principal para a migração.
     */
    public function runFullMigration(int $userId, int $clientId, array $tablesToMigrate): void
    {
        // 1. Cria UM ÚNICO registro para a execução completa
        $stmtRun = $this->appPdo->prepare("INSERT INTO migration_runs (user_id, cliente_id, start_time, status) VALUES (?, ?, NOW(), ?)");
        $stmtRun->execute([$userId, $clientId, 'iniciada']);
        $runId = (int)$this->appPdo->lastInsertId();
        $this->log($runId, 'INFO', 'INICIO_GERAL', "Processo iniciado para: " . implode(', ', $tablesToMigrate));

        $configEntity = $this->configRepository->find($userId, $clientId);
        if (!$configEntity) throw new \RuntimeException('Configuração não encontrada.');

        $allStats = [];
        $totalRowsOverall = 0;
        $hasFailed = false;

        // 2. Itera sobre cada tabela e executa a migração individualmente
        foreach ($tablesToMigrate as $tablePrefix) {
            try {
                // Chama a lógica de migração de tabela única
                $stats = $this->migrateSingleTable($userId, $clientId, $runId, $tablePrefix, $configEntity->config);
                $allStats[] = $stats;
                $totalRowsOverall += $stats['destination_count'];
            } catch (\Throwable $e) {
                $this->log($runId, 'ERROR', 'FALHA_TABELA', "Falha ao migrar '$tablePrefix': " . $e->getMessage());
                $hasFailed = true;
                break; 
            }
        }
        
        // 3. Finaliza a execução e salva o JSON com TODAS as estatísticas
        $finalStatus = $hasFailed ? 'falhou' : 'concluida';
        $this->updateRunStatus($runId, $finalStatus, $totalRowsOverall, $allStats);
        $this->updateStatus($userId, $clientId, $finalStatus);
    }

    /**
     * Migra uma única tabela com base no prefixo de configuração
     * e retorna um array com as estatísticas.
     */
    private function migrateSingleTable(int $userId, int $clientId, int $runId, string $tablePrefix, array $configEntityData): array
    {
        // 1. Valida o mapa e busca as configs
        $mapping = $this->mappings[$tablePrefix] ?? null;
        if (!$mapping) throw new RuntimeException("Mapa de migração não encontrado para '$tablePrefix'.");

        $tableCfg = $configEntityData[$tablePrefix] ?? [];
        if (empty($tableCfg)) throw new RuntimeException("Configuração para a tabela '$tablePrefix' não encontrada.");

        $destTable = $mapping['target_table'];
        
        // 2. Conecta aos bancos
        $sourceCfg = $configEntityData['conexao']['origem'];
        $destCfg = $configEntityData['conexao']['destino'];
        $pdoOrigem = ExternalConnectionFactory::create($sourceCfg['tipo'], $sourceCfg['ip'], $sourceCfg['porta'] ?? '', $sourceCfg['dbName'], $sourceCfg['usuario'] ?? '', $sourceCfg['senha'] ?? '');
        $pdoDestino = ExternalConnectionFactory::create($destCfg['tipo'], $destCfg['ip'], $destCfg['porta'] ?? '', $destCfg['dbName'], $destCfg['usuario'] ?? '', $destCfg['senha'] ?? '');

        // 3. Garante que a tabela De-Para exista
        $this->ensureDeParaTableExists($pdoDestino);

        // 4. LÓGICA DE LIMPEZA DE DADOS (SE SOLICITADO)
        if (!empty($tableCfg['limpar_dados'])) {
            $this->log($runId, 'INFO', 'INICIO_LIMPEZA', "Iniciando limpeza para a tabela '$destTable'.");
            $pdoDestino->beginTransaction();
            try {
                $stmtCleanTarget = $pdoDestino->prepare("DELETE FROM `$destTable` WHERE update_usuario = -999");
                $stmtCleanTarget->execute();
                $stmtCleanDePara = $pdoDestino->prepare("DELETE FROM mig_de_para WHERE tabela_3c = ?");
                $stmtCleanDePara->execute([$destTable]);
                $pdoDestino->commit();
                $this->log($runId, 'INFO', 'FIM_LIMPEZA', "Limpeza para a tabela '$destTable' concluída.");
            } catch (Throwable $e) {
                $pdoDestino->rollBack();
                throw new RuntimeException("Falha ao limpar dados antigos para '$destTable': " . $e->getMessage(), 0, $e);
            }
        }

        // 5. Prepara os contadores e os inserts
        $this->prepareAutoIncrementCounters($pdoDestino, $destTable, $mapping['columns']);
        $destColumns = array_keys($mapping['columns']);
        $placeholders = array_map(fn($col) => ":$col", $destColumns);
        $insertSql = "INSERT INTO `$destTable` (`" . implode('`, `', $destColumns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
        $stmtDestino = $pdoDestino->prepare($insertSql);
        $stmtDePara = $pdoDestino->prepare("INSERT INTO mig_de_para (tabela_3c, chave_3c, tabela_origem, chave_origem) VALUES (:tabela_3c, :chave_3c, :tabela_origem, :chave_origem)");
        
        $tabelaOrigem = $tableCfg['tabela_origem'] ?? 'desconhecida';
        $limit = (int)($tableCfg['batch_size'] ?? 5000);
        $destKeyColumn = $mapping['destination_key_column'];
        $offset = 0;
        $totalRowsProcessed = 0;
        
        $queryForCount = preg_replace('/\s+order\s+by\s+.*$/i', '', $tableCfg['query']);
        $countSql = "SELECT COUNT(*) FROM (" . $queryForCount . ") AS subquery";
        $sourceTotalCount = (int)$pdoOrigem->query($countSql)->fetchColumn();
        $this->log($runId, 'INFO', 'CONTAGEM_ORIGEM', "Tabela '$tablePrefix': $sourceTotalCount registros encontrados na origem.");

        // 6. Inicia o loop de migração em lotes
        while (true) {
            $queryComLimite = $this->applyBatchingToQuery($tableCfg['query'], $sourceCfg['tipo'], $limit, $offset);
            $sourceRows = $pdoOrigem->query($queryComLimite)->fetchAll(PDO::FETCH_ASSOC);

            if (empty($sourceRows)) break;

            $pdoDestino->beginTransaction();
            try {
                foreach ($sourceRows as $row) {
                    $paramsToExecute = [];
                    foreach ($mapping['columns'] as $destCol => $staticRule) {
                        // Garante que a regra estática seja um array (nossa proteção)
                            if (!is_array($staticRule)) {
                                throw new \RuntimeException("Erro de configuração para a coluna '$destCol'");
                            }

                            // Pega a regra específica que o usuário configurou no formulário
                            $userRule = $tableCfg[$destCol] ?? [];
                            if (is_string($userRule)) {
                                $userRule = ['type' => 'source_column', 'value' => $userRule];
                            }
                            
                            $value = null;
                            
                            // O TIPO da operação é sempre ditado pelo mapa estático. É uma regra do sistema.
                            $ruleType = $staticRule['type'] ?? 'source_column';

                            switch ($ruleType) {
                                case 'source_column':
                                    // O NOME da coluna de origem vem do que o usuário digitou ('value' na regra salva).
                                    $sourceColumnName = $userRule['value'] ?? null;
                                    $value = $sourceColumnName ? ($row[$sourceColumnName] ?? null) : null;
                                    break;
                                
                                case 'generate_acronym':
                                    // O NOME da coluna de origem (para gerar o texto) vem do que o usuário digitou.
                                    $sourceColumnName = $userRule['value'] ?? null;
                                    $sourceText = $sourceColumnName ? ($row[$sourceColumnName] ?? '') : '';
                                    
                                    // O tamanho máximo vem da regra estática.
                                    $maxLength = $staticRule['max_length'] ?? 8;
                                    $value = AcronymGenerator::generateUnique($sourceText, $pdoDestino, $destTable, $destCol, $maxLength);
                                    break;
                                
                                case 'sub_query':
                                    // Os parâmetros da sub-query vêm inteiramente da regra do usuário.
                                    $keyFromMainRow = $row[$userRule['source_key']] ?? null;
                                    if ($keyFromMainRow !== null) {
                                        $value = $this->executeSubQuery($pdoOrigem, $userRule['sql'], $keyFromMainRow);
                                    }
                                    break;
                                case 'concatenated_source':
                                    // Pega a string da regra que o usuário salvou (ex: "NOME; ' - '; CIDADE")
                                    $ruleString = $userRule['value'] ?? '';
                                    // Chama a nossa função de ajuda para interpretar a regra e construir o valor final
                                    $value = $this->parseAndBuildConcatenatedString($ruleString, $row);
                                    break;
                                case 'custom_de_para':
                                    $sourceKeyValue = $row[$userRule['source_key']] ?? null;
                                    $value = $this->executeCustomDePara($userRule['rules'], $sourceKeyValue);
                                    break;
                                case 'de_para_lookup':
                                    $sourceKeyValue = $row[$userRule['source_key']] ?? null;
                                    $sourceTableName = $userRule['source_table'] ?? null;
                                    if ($sourceKeyValue && $sourceTableName) {
                                        $value = $this->executeDeParaLookup($pdoDestino, $sourceTableName, $sourceKeyValue);
                                    }
                                    break;
                                case 'sub_query_de_para_lookup':
                                    // Pega a chave da linha principal (ex: o email do contato)
                                    $subQueryKeyValue = $row[$userRule['sub_query_key']] ?? null;
                                    
                                    if ($subQueryKeyValue !== null) {
                                        // Passo 1: Executa a sub-query na ORIGEM para encontrar a chave intermediária (ex: o ID da pessoa no sistema legado)
                                        $intermediateKey = $this->executeSubQuery($pdoOrigem, $userRule['sql'], $subQueryKeyValue);

                                        if ($intermediateKey !== null) {
                                            // Passo 2: Usa a chave intermediária para fazer a busca na tabela DE-PARA no DESTINO
                                            $value = $this->executeDeParaLookup($pdoDestino, $userRule['source_table'], $intermediateKey);
                                        }
                                    }
                                    break;
                                
                                // O resto dos casos usam o mapa estático, pois não são configuráveis pelo usuário.
                                case 'auto_increment':
                                    $value = $this->autoIncrementCounters[$destCol];
                                    $this->autoIncrementCounters[$destCol]++;
                                    break;
                                case 'session':
                                    $value = $_SESSION[$staticRule['value']] ?? null;
                                    break;
                                case 'datetime':
                                    $value = date('Y-m-d H:i:s');
                                    break;
                                case 'default':
                                    $value = $staticRule['value'];
                                    break;
                            }

                            // Aplica as regras de segurança e "plano B" do mapa estático
                            if ($value === null && isset($staticRule['default_if_null'])) {
                                $value = $staticRule['default_if_null'];
                            }
                            if (isset($staticRule['max_length']) && is_string($value)) {
                                $value = mb_substr($value, 0, $staticRule['max_length'], 'UTF-8');
                            }
                            
                            $paramsToExecute[":$destCol"] = $value;
                    }
                    $stmtDestino->execute($paramsToExecute);
                    $stmtDePara->execute([
                        ':tabela_3c' => $destTable, 
                        ':chave_3c' => $paramsToExecute[':' . $destKeyColumn], 
                        ':tabela_origem' => $tabelaOrigem, 
                        ':chave_origem' => $row[$tableCfg['campo_de']]
                    ]);
                }
                $pdoDestino->commit();
            } catch (Throwable $e) {
                $pdoDestino->rollBack();
                throw $e;
            }
            
            $totalRowsProcessed += count($sourceRows);
            $offset += $limit;
            $this->updateStatus($userId, $clientId, "rodando: $totalRowsProcessed de $sourceTotalCount para a tabela '$tablePrefix'");
            $this->log($runId, 'DEBUG', 'LOTE_CONCLUIDO', "Lote para '$tablePrefix' processado. Total até agora: $totalRowsProcessed");
        }

        // 7. Retorna as estatísticas desta tabela para o orquestrador principal
        $this->log($runId, 'SUCCESS', 'TABLE_DONE', "Tabela '$destTable' concluída com sucesso.", ['table' => $destTable]);
        return [
            'table' => $destTable,
            'source_count' => $sourceTotalCount,
            'destination_count' => $totalRowsProcessed
        ];
    }

    /**
     * Interpreta as regras customizadas de "De-Para" e retorna o valor correspondente.
     * Esta versão é segura e não usa eval().
     */
    private function executeCustomDePara(?string $rulesStr, $sourceValue): mixed
    {
        if (empty($rulesStr) || $sourceValue === null) {
            return null;
        }

        $map = [];
        // Quebra a string de regras em linhas individuais
        $lines = preg_split('/\\r\\n|\\r|\\n/', $rulesStr);

        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine)) {
                continue;
            }

            // Separa a linha em chave e valor usando '=>'
            $parts = explode('=>', $trimmedLine, 2);
            if (count($parts) !== 2) {
                continue; // Pula linhas mal formatadas
            }

            // Limpa e remove aspas da chave de entrada
            $from = trim($parts[0]);
            $from = trim($from, "'\"");

            // Limpa e remove aspas do valor de saída (se houver)
            $to = trim($parts[1]);
            if (preg_match('/^([\'"])(.*)\1$/', $to, $matches)) {
                // Se o valor estiver entre aspas, usamos o conteúdo
                $to = $matches[2];
            } elseif (strtolower($to) === 'null') {
                // Permite definir um valor nulo
                $to = null;
            }

            $map[$from] = $to;
        }

        // Retorna o valor correspondente se a chave existir no mapa, senão retorna nulo.
        return array_key_exists($sourceValue, $map) ? $map[$sourceValue] : null;
    }

    /**
     * NOVO: Método privado que busca o valor na tabela de-para.
     */
    private function executeDeParaLookup(PDO $pdo, string $sourceTable, $sourceKey): ?string
    {
        try {
            $sql = "SELECT chave_3c FROM mig_de_para WHERE tabela_origem = ? AND chave_origem = ? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sourceTable, $sourceKey]);
            $result = $stmt->fetchColumn();
            return $result ?: null; // Retorna o valor ou nulo se não encontrar
        } catch (\Throwable $e) {
            error_log("Busca De-Para falhou: " . $e->getMessage());
            return null;
        }
    }

    private function executeSubQuery(PDO $pdo, string $sql, $keyValue): ?string
    {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$keyValue]); // Assumindo placeholder posicional (?)
            return $stmt->fetchColumn(); // Retorna o valor da primeira coluna da primeira linha
        } catch (\Throwable $e) {
            error_log("Sub-query failed: " . $e->getMessage());
            return null; // Retorna nulo em caso de erro na sub-query
        }
    }

    private function prepareAutoIncrementCounters(PDO $pdo, string $tableName, array $columnsMapping): void
    {
        $this->autoIncrementCounters = [];
        foreach ($columnsMapping as $destCol => $rule) {
            if (($rule['type'] ?? '') === 'auto_increment') {
                // ATUALIZADO: Usa o $tableName recebido como parâmetro
                $stmt = $pdo->prepare("SELECT MAX(`$destCol`) FROM `$tableName`");
                $stmt->execute();
                $maxValue = $stmt->fetchColumn();
                $this->autoIncrementCounters[$destCol] = max((int)$maxValue + 1, (int)$rule['start_value']);
            }
        }
    }

    /**
     * Interpreta uma string de regra de concatenação e constrói o valor final.
     * Ex: "NOME; ' - '; CIDADE" -> "João da Silva - São Paulo"
     */
    private function parseAndBuildConcatenatedString(string $ruleString, array $sourceRow): string
    {
        // Separa a regra em partes usando o ponto e vírgula
        $parts = explode(';', $ruleString);
        $finalString = '';

        foreach ($parts as $part) {
            $trimmedPart = trim($part);

            // Verifica se a parte é um separador literal (entre aspas duplas ou simples)
            if (
                (str_starts_with($trimmedPart, '"') && str_ends_with($trimmedPart, '"')) ||
                (str_starts_with($trimmedPart, "'") && str_ends_with($trimmedPart, "'"))
            ) {
                // Remove as aspas e substitui caracteres de escape como \n por uma quebra de linha real
                $separator = substr($trimmedPart, 1, -1);
                $finalString .= str_replace(['\n', '\t'], ["\n", "\t"], $separator);
            } else {
                // Se não for um separador, é o nome de uma coluna de origem
                $finalString .= $sourceRow[$trimmedPart] ?? '';
            }
        }

        return $finalString;
    }

    // ATUALIZE o método updateRunStatus para aceitar as estatísticas
    private function updateRunStatus(int $runId, string $status, int $rowCount, ?array $stats = null): void
    {
        $sql = "UPDATE migration_runs SET end_time = NOW(), status = ?, total_rows_processed = ?, stats_by_table = ? WHERE id = ?";
        $stmt = $this->appPdo->prepare($sql);

        $jsonStats = $stats ? json_encode($stats) : null;
        
        $stmt->execute([$status, $rowCount, $jsonStats, $runId]);
    }

    /**
     * Atualiza o status da migração no JSON de configuração.
     */
    public function updateStatus(int $userId, int $clientId, string $status): bool
    {
        $configEntity = $this->configRepository->find($userId, $clientId);
        if (!$configEntity) {
            return false;
        }
        
        $configEntity->config['status_migracao'] = $status;
        $this->configRepository->save($configEntity);
        return true;
    }

    public function getStatus(int $userId, int $clientId): string
    {
        // Pega o status da execução mais recente para este cliente/usuário
        $sql = "SELECT status FROM migration_runs WHERE user_id = ? AND cliente_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = $this->appPdo->prepare($sql);
        $stmt->execute([$userId, $clientId]);
        $status = $stmt->fetchColumn();

        // Se não houver nenhuma execução, retorna um status padrão
        return $status ?: 'nenhuma execução';
    }

    private function isCancelled(int $userId, int $clientId): bool {
        return $this->getStatus($userId, $clientId) === 'cancelado';
    }

    // Dentro de MigrationService.php
    private function applyBatchingToQuery(string $sql, string $dbType, int $limit, int $offset): string
    {
        $sql = rtrim(trim($sql), ';');

        if ($dbType === 'sqlsrv') {
            // Esta verificação é crucial
            if (stripos($sql, 'ORDER BY') === false) {
                throw new \InvalidArgumentException("A query para SQL Server deve conter uma cláusula 'ORDER BY' para a paginação em lotes funcionar corretamente.");
            }
            return "$sql OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY";
        }

        // Padrão para MySQL/PostgreSQL
        return "$sql LIMIT $limit OFFSET $offset";
    }

    private function ensureDeParaTableExists(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `mig_de_para` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `tabela_3c` VARCHAR(100) DEFAULT NULL,
                `chave_3c` VARCHAR(100) DEFAULT NULL,
                `tabela_origem` VARCHAR(100) DEFAULT NULL,
                `chave_origem` VARCHAR(100) DEFAULT NULL,
                INDEX `mig_de_para_tabela_3c_IDX` (`tabela_3c`, `chave_origem`),
                INDEX `mig_de_para_tabela_origem_IDX` (`tabela_origem`, `chave_origem`)
            ) COLLATE='latin1_swedish_ci' ENGINE=InnoDB;
        ");
    }

    /**
     * NOVO: Implementação real da função de log.
     */
    private function log(int $runId, string $level, string $event, string $message, ?array $details = null): void
    {
        try {
            $sql = "INSERT INTO migration_logs (migration_run_id, nivel, evento, mensagem, detalhes) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->appPdo->prepare($sql);
            $stmt->execute([ $runId, $level, $event, $message, $details ? json_encode($details) : null ]);
        } catch (\Throwable $e) {
            error_log("FALHA AO GRAVAR LOG: " . $e->getMessage());
        }
    }
}