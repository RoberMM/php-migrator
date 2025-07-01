<?php
namespace App\Repository;

use App\Entity\ImportJob; // Não vamos usar a entidade em todos os retornos para simplificar
use PDO;

class MySqlImportJobRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Cria um novo registro de job na tabela e retorna o ID (chave primária).
     *
     * @param array $data Os dados a serem inseridos.
     * @return int O ID do registro recém-criado.
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO import_jobs (import_id, user_id, status, payload, logs) 
                VALUES (:import_id, :user_id, :status, :payload, :logs)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':import_id' => $data['import_id'],
            ':user_id' => $data['user_id'],
            ':status' => $data['status'] ?? 'pending',
            ':payload' => $data['payload'],
            ':logs' => $data['logs'] ?? ''
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Encontra um job pelo seu ID único de importação e pelo ID do usuário.
     * Retorna um array associativo para ser facilmente convertido em JSON.
     *
     * @param string $importId O ID da importação (ex: 'csv_import_...').
     * @param int $userId O ID do usuário logado.
     * @return array|null O job como um array ou null se não for encontrado.
     */
    public function findByImportIdAndUserId(string $importId, int $userId): ?array
    {
        $sql = "SELECT * FROM import_jobs WHERE import_id = :import_id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':import_id' => $importId, ':user_id' => $userId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Adiciona uma nova linha de log ao campo 'logs' de um job existente.
     * Usa CONCAT para evitar problemas de concorrência.
     *
     * @param int $jobId O ID (chave primária) do job.
     * @param string $message A mensagem a ser adicionada.
     */
    public function appendLog(int $jobId, string $message): void
    {
        // IFNULL garante que, se o campo 'logs' for NULL, ele comece como uma string vazia.
        $sql = "UPDATE import_jobs SET logs = CONCAT(IFNULL(logs, ''), :message) WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        // Adiciona uma quebra de linha automaticamente
        $stmt->execute([':message' => $message . PHP_EOL, ':id' => $jobId]);
    }

    /**
     * Atualiza o status de um job.
     *
     * @param int $jobId O ID (chave primária) do job.
     * @param string $status O novo status ('processing', 'completed', 'failed').
     */
    public function updateStatus(int $jobId, string $status): void
    {
        $sql = "UPDATE import_jobs SET status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':status' => $status, ':id' => $jobId]);
    }

    /**
     * Este método é para o script Worker.
     * Ele encontra o job pendente mais antigo e o trava na transação
     * para que outro processo worker não o pegue ao mesmo tempo.
     *
     * @return array|null O job como um array ou null se não houver jobs pendentes.
     */
    public function findAndLockPendingJob(): ?array
    {
        // Este método deve ser chamado dentro de uma transação no seu worker.
        // Ex: $this->pdo->beginTransaction();
        $sql = "SELECT * FROM import_jobs WHERE status = 'pending' ORDER BY created_at ASC LIMIT 1 FOR UPDATE";
        $stmt = $this->pdo->query($sql);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($job) {
            // Marca imediatamente como 'processing' dentro da mesma transação
            $this->updateStatus($job['id'], 'processing');
        }

        // A transação seria finalizada no worker.
        // Ex: $this->pdo->commit();
        
        return $job ?: null;
    }
}