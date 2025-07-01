<?php
// src/Controller/CsvImporterController.php
namespace App\Controller;

use App\Repository\SystemRepositoryInterface;
use App\Repository\ClientRepositoryInterface;
use App\Service\CsvImporterService;
use InvalidArgumentException;
use Throwable;

class CsvImporterController
{
    // O construtor permanece o mesmo, recebendo as dependências.
    public function __construct(
        private SystemRepositoryInterface $systemRepository,
        private ClientRepositoryInterface $clientRepository,
        private CsvImporterService $importerService,
        private string $basePath
    ) {}

    /**
     * Prepara os dados para o layout e exibe a página do importador.
     * (Este método não sofreu alterações)
     */
    public function showPage(): void
    {
        $userId = $_SESSION['user_id'];
        $systems = $this->systemRepository->findByUserId($userId);
        $clientsBySystem = [];
        foreach ($systems as $system) {
            $clientsBySystem[$system->id] = $this->clientRepository->findBySystemId($system->id);
        }

        $viewData = [
            'pageTitle' => 'Importador de CSV',
            'userData' => $_SESSION['user_data'],
            'basePath' => $this->basePath,
            'systems' => $systems,
            'clientsBySystem' => $clientsBySystem,
            'selectedClientId' => 0
        ];
        
        require_once __DIR__ . '/../View/csv_importer_view.php';
    }

    /**
     * NOVO: Endpoint #1 - Inicia a Importação (Método: POST)
     * Rota sugerida: /api/csv/initiate
     *
     * Valida os dados, salva o arquivo .zip e as configurações na sessão do usuário.
     * Retorna um ID único para que o frontend possa se conectar ao stream de logs.
     */
    public function initiateImport(): void
    {
        header('Content-Type: application/json');
        try {
            // Aumenta limites para o upload
            ini_set('upload_max_filesize', '100M');
            ini_set('post_max_size', '100M');

            $connectionConfig = $_POST['conexao'] ?? [];
            $clearTables = !empty($_POST['limpar_tabelas']);
            $zipFile = $_FILES['csv_zip_file'] ?? null;

            // Validações essenciais
            if (!$zipFile || $zipFile['error'] !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException('O arquivo .zip com os CSVs é obrigatório. Erro de upload: ' . ($zipFile['error'] ?? 'N/A'));
            }
            if (empty($connectionConfig['dbName']) || empty($connectionConfig['ip']) || empty($connectionConfig['usuario'])) {
                throw new InvalidArgumentException('Dados de conexão com o banco de destino estão incompletos.');
            }
            
            // Gera um ID único para esta importação específica
            $importId = 'csv_import_' . uniqid();
            
            // Cria um diretório de uploads temporário se não existir
            $tempUploadsDir = sys_get_temp_dir() . '/csv_uploads/';
            if (!is_dir($tempUploadsDir)) {
                mkdir($tempUploadsDir, 0777, true);
            }
            
            // Move o arquivo para um local persistente que sobreviverá ao fim desta requisição
            $savedFilePath = $tempUploadsDir . $importId . '.zip';
            if (!move_uploaded_file($zipFile['tmp_name'], $savedFilePath)) {
                throw new \RuntimeException('Falha crítica ao salvar o arquivo de upload no servidor.');
            }

            // Guarda todas as informações necessárias na sessão, usando o ID como chave
            $_SESSION[$importId] = [
                'connection' => $connectionConfig,
                'clearTables' => $clearTables,
                'filePath' => $savedFilePath
            ];

            echo json_encode(['success' => true, 'importId' => $importId]);

        } catch (InvalidArgumentException $e) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (Throwable $e) {
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Erro interno no servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * NOVO: Endpoint #2 - Stream de Progresso (Método: GET)
     * Rota sugerida: /api/csv/stream-log?id=...
     *
     * Estabelece uma conexão Server-Sent Events (SSE) e transmite os logs
     * gerados pelo CsvImporterService em tempo real.
     */
    public function streamImportProgress(): void
    {
        $importId = $_GET['id'] ?? null;
        if (!$importId || empty($_SESSION[$importId])) {
            http_response_code(404); // Not Found
            echo json_encode(['success' => false, 'message' => 'ID de importação inválido ou expirado.']);
            return;
        }
        
        $importData = $_SESSION[$importId];

        // 1. Configurar os headers para Server-Sent Events
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Para Nginx

        // 2. Desabilitar o buffer de saída do PHP para enviar dados imediatamente
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
        }
        ini_set('zlib.output_compression', 0);
        ini_set('output_buffering', 'off');
        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        // 3. Definir a função de callback que será passada para o serviço
        $logCallback = function(string $message) {
            // Formato padrão do SSE: "data: {json_encoded_message}\n\n"
            echo "data: " . json_encode($message) . "\n\n";
            flush(); // Envia o output para o navegador imediatamente
        };

        try {
            // Aumenta o tempo de execução para importações longas
            set_time_limit(600); // 10 minutos

            // 4. Chamar o serviço, passando a configuração e o callback
            $this->importerService->run(
                $importData['connection'],
                $importData['filePath'],
                $importData['clearTables'],
                $logCallback
            );

        } catch (Throwable $e) {
            // Em caso de erro fatal no serviço, envia uma última mensagem de log
            $logCallback("❌ ERRO CRÍTICO: " . $e->getMessage());
        } finally {
            // 5. Bloco de limpeza: sempre será executado, com erro ou sucesso
            if (file_exists($importData['filePath'])) {
                unlink($importData['filePath']);
            }
            unset($_SESSION[$importId]);

            // Envia um evento customizado 'close' para o cliente saber que o processo acabou
            echo "event: close\n";
            echo "data: Processo finalizado no servidor.\n\n";
            flush();
        }
    }

    // REMOVIDO: O método startImport() original foi substituído pelos dois métodos acima.
}