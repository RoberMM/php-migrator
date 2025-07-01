<?php
// src/Service/CsvImporterService.php
namespace App\Service;

use App\Factory\ExternalConnectionFactory;
use PDO;
use ZipArchive;
use RuntimeException;
use Throwable;

class CsvImporterService
{
    private PDO $pdo;
    private string $tempDir;

    /** @var callable|null */
    private $logCallback = null;

    public function run(array $connectionConfig, string $zipFilePath, bool $clearTables, callable $logCallback): void
    {
        $this->logCallback = $logCallback;

        try {
            $this->log("🚀 Iniciando processo de importação...");
        
            $this->pdo = ExternalConnectionFactory::create(
                $connectionConfig['tipo'],
                $connectionConfig['ip'],
                $connectionConfig['porta'] ?? '',
                $connectionConfig['dbName'],
                $connectionConfig['usuario'],
                $connectionConfig['senha']
            );
            $this->log("✔️ Conexão com o banco de dados de destino estabelecida.");

            $this->tempDir = sys_get_temp_dir() . '/' . uniqid('csv_import_');
            if (!mkdir($this->tempDir, 0777, true) && !is_dir($this->tempDir)) {
                throw new RuntimeException('Não foi possível criar o diretório temporário.');
            }

            $this->unzipFiles($zipFilePath);
            $csvFiles = glob($this->tempDir . '/*.csv');
            if (empty($csvFiles)) {
                throw new RuntimeException("Nenhum arquivo .csv encontrado dentro do .zip.");
            }
            
            // ALTERAÇÃO: Adiciona flag para controle do espaçamento
            $isFirstFile = true; 
            foreach ($csvFiles as $filePath) {
                // ALTERAÇÃO: Adiciona linha em branco entre os logs de cada arquivo
                if (!$isFirstFile) {
                    $this->log(""); 
                }

                $tableName = pathinfo($filePath, PATHINFO_FILENAME);
                if ($clearTables) {
                    $this->clearTable($tableName);
                }
                $this->importCsvToTable($filePath, $tableName);

                $isFirstFile = false; // Atualiza a flag
            }

            $this->log(""); // Espaçamento final
            $this->log("✅ Processo de importação finalizado!");

        } finally {
             if (!empty($this->tempDir) && is_dir($this->tempDir)) {
                $this->cleanup();
             }
        }
    }

    private function importCsvToTable(string $filePath, string $tableName): void
    {
        $this->log("📄 Lendo arquivo: " . basename($filePath));
        if (($handle = fopen($filePath, 'r')) === FALSE) {
            $this->log("❌ Erro ao abrir o arquivo " . basename($filePath));
            return;
        }

        $header = fgetcsv($handle, 0, ';');
        if (!$header || empty(array_filter($header))) {
            $this->log("❌ Cabeçalho inválido ou vazio em " . basename($filePath));
            fclose($handle);
            return;
        }
        
        $sampleRow = fgetcsv($handle, 0, ';') ?: [];
        $this->createTableIfNotExists($tableName, $header, $sampleRow);
        
        rewind($handle);
        fgetcsv($handle, 0, ';');

        $batch = [];
        $batchSize = 500;
        $totalInFile = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== FALSE) {
            if (count($row) != count($header)) continue;
            
            $cleanedRow = array_map([$this, 'cleanData'], $row);
            $batch[] = $cleanedRow;
            $totalInFile++;

            if (count($batch) >= $batchSize) {
                $this->bulkInsertData($tableName, $header, $batch);
                // ALTERAÇÃO: Log de lote foi removido para um output mais limpo.
                $batch = [];
            }
        }
        
        if (!empty($batch)) {
            $this->bulkInsertData($tableName, $header, $batch);
        }
        
        fclose($handle);
        $this->log("👍 $totalInFile registros processados para `$tableName`.");
    }

    private function bulkInsertData(string $tableName, array $columns, array $batch): void
    {
        if(empty($batch) || empty($columns)) return;

        $columnsSql = '`' . implode('`, `', $columns) . '`';
        $placeholders = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
        $valuesSql = implode(',', array_fill(0, count($batch), $placeholders));

        $sql = "INSERT INTO `$tableName` ($columnsSql) VALUES $valuesSql";
        $stmt = $this->pdo->prepare($sql);
        
        $flatValues = [];
        foreach($batch as $row) {
            $flatValues = array_merge($flatValues, array_values($row));
        }
        
        try {
            $stmt->execute($flatValues);
        } catch (Throwable $e) {
            $this->log("❌ Erro ao inserir lote na tabela `$tableName`: " . $e->getMessage());
        }
    }
    
    // =============================================
    // MÉTODOS DE AJUDA E LOGGING
    // =============================================

    /**
     * ALTERADO: Este método agora envia a mensagem para o callback, em vez de um array interno.
     */
    private function log(string $message): void 
    {
        $formattedMessage = date('[H:i:s] ') . $message;
        if (is_callable($this->logCallback)) {
            // Chama a função que foi passada pelo Controller
            call_user_func($this->logCallback, $formattedMessage);
        }
    }

    private function cleanData($data) {
        if (!is_string($data)) return $data;
        $data = trim($data);
        $data = $this->fixBrokenUtf8($data);
        $data = $this->stripHtml($data);
        $data = $this->removerCaracteresHexadecimais($data);
        return ($data === '' || strtolower($data) === 'nan') ? null : $data;
    }

    private function fixBrokenUtf8($text) {
        // Seu mapa de caracteres... (mantido como no original)
        $map = [
            'Ã¡' => 'á', 'Ã ' => 'à', 'Ã¢' => 'â', 'Ã£' => 'ã',
            'Ãª' => 'ê', 'Ã©' => 'é', 'Ã¨' => 'è', 'Ã­' => 'í',
            'Ã³' => 'ó', 'Ã´' => 'ô', 'Ãµ' => 'õ', 'Ãº' => 'ú',
            'Ã¼' => 'ü', 'Ã§' => 'ç', 'Ã‘' => 'Ñ', 'Ãœ' => 'Ü',
            'Ã‰' => 'É', 'Ã“' => 'Ó', 'Ã‚' => 'Â', 'ÃŽ' => 'Î',
            'Ã' => 'Á', 'Ãƒ' => 'Ã', 'Ã‡' => 'Ç', 'Â ' => ' ', 
            'Â' => '', 'Ã' => 'í', 'â€“' => '–', 'â€”' => '—', 
            'â€˜' => '‘', 'â€™' => '’', 'â€œ' => '“', 'â€' => '”', 
            'â€¦' => '…', 'â€¢' => '•', 'â€' => '†', 'â€¡' => '‡', 
            'â‚¬' => '€'
        ];
        return strtr($text, $map);
    }
    
    private function stripHtml($text) {
        $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return strip_tags($text);
    }
    
    private function removerCaracteresHexadecimais($texto) {
        return preg_replace('/[^\x0A\x0D\x20-\x7EÀ-ÿ]/u', '', $texto);
    }

    private function unzipFiles(string $zipFilePath): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipFilePath) === TRUE) {
            $zip->extractTo($this->tempDir);
            $zip->close();
            $this->log("📦 Arquivo .zip descompactado com sucesso.");
        } else {
            throw new RuntimeException('Falha ao abrir o arquivo .zip.');
        }
    }

    private function clearTable(string $tableName): void
    {
        try {
            // Adiciona aspas para garantir a compatibilidade com nomes de tabelas que são palavras-chave
            $this->pdo->exec("TRUNCATE TABLE `$tableName`");
            $this->log("🗑️ Tabela `$tableName` limpa com sucesso.");
        } catch (Throwable $e) {
            $this->log("⚠️ Aviso ao limpar tabela `$tableName`: A tabela pode não existir ainda. " . $e->getMessage());
        }
    }

    private function createTableIfNotExists(string $tableName, array $columns, array $sampleRow): void
    {
        try {
            $this->pdo->query("SELECT 1 FROM `$tableName` LIMIT 1");
            $this->log("ℹ️ Tabela `$tableName` já existe.");
        } catch (Throwable $e) {
            $this->log("⚠️ Tabela `$tableName` não encontrada. Criando automaticamente...");
            $columnDefs = [];
            foreach ($columns as $i => $colName) {
                if(empty(trim($colName))) continue;

                $value = $sampleRow[$i] ?? '';
                $type = $this->inferColumnType($this->cleanData($value));
                $columnDefs[] = "`$colName` $type";
            }

            if(empty($columnDefs)) {
                throw new RuntimeException("Nenhuma coluna válida encontrada no cabeçalho do CSV para criar a tabela `$tableName`.");
            }

            $columnsSql = implode(", ", $columnDefs);
            
            // =====================================================================
            // ALTERAÇÃO: Lógica para criar o índice de forma inteligente
            // =====================================================================
            $firstColumnName = $columns[0];
            $indexSql = "INDEX `idx_auto_1` (`$firstColumnName`)"; // Índice padrão

            // Pega o tipo de dados inferido para a primeira coluna
            $firstColumnType = $this->inferColumnType($sampleRow[0] ?? '');
            
            // Se o tipo for TEXT ou LONGTEXT, adiciona um limite de 255 caracteres ao índice
            if (stripos($firstColumnType, 'TEXT') !== false) {
                $indexSql = "INDEX `idx_auto_1` (`$firstColumnName`(255))";
            }

            $sql = "CREATE TABLE `$tableName` ($columnsSql, $indexSql) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            // =====================================================================

            try {
                $this->pdo->exec($sql);
                $this->log("🛠️ Tabela `$tableName` criada com sucesso.");
            } catch (Throwable $createError) {
                // Lança a exceção com a query SQL para facilitar a depuração
                throw new RuntimeException("Erro ao criar a tabela `$tableName`: " . $createError->getMessage() . " | SQL: " . $sql);
            }
        }
    }

   /**
     * Inferencia do tipo de coluna a partir de um valor de amostra.
     * Usa BIGINT como padrão para inteiros para máxima compatibilidade.
     */
    private function inferColumnType($value): string
    {
        if (is_null($value) || $value === '') {
            return 'LONGTEXT NULL';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
            return 'DATETIME NULL';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return 'DATE NULL';
        }
        
        // ATENÇÃO: A verificação de decimal deve vir ANTES da verificação de inteiro.
        if (is_numeric($value) && strpos($value, '.') !== false) {
            return 'DECIMAL(20, 4) NULL';
        }
        
        if (is_numeric($value)) {
            // Usa BIGINT como padrão para todos os inteiros. É mais seguro.
            return 'BIGINT NULL';
        }
        
        return 'LONGTEXT NULL';
    }

    private function cleanup(): void
    {
        $this->log("🧹 Limpando arquivos temporários...");
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        rmdir($this->tempDir);
        $this->log("✔️ Limpeza concluída.");
    }
}