<?php
/**
 * public/index.php
 * * FRONT CONTROLLER UNIFICADO (VERSÃO FINAL E CORRIGIDA)
 * Responsável por receber TODAS as requisições, inicializar o sistema
 * e direcionar para o Controller apropriado, retornando HTML ou JSON.
 */

// MODO DEBUG: Lembre-se de comentar/remover estas 3 linhas em produção
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. BOOTSTRAP
// =============================================
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
session_start();

// 2. CONFIGURAÇÃO DE ROTA E CAMINHO BASE
// =============================================
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = ($scriptName === '/' || $scriptName === '\\') ? '' : rtrim($scriptName, '/');
$path = substr($requestUri, strlen($basePath));
if (empty($path)) {
    $path = '/';
}
$method = $_SERVER['REQUEST_METHOD'];

// 3. CONTAINER DE DEPENDÊNCIAS
// =============================================
use App\Factory\DatabaseFactory;
use App\Repository\MySqlSystemRepository;
use App\Repository\MySqlClientRepository;
use App\Repository\MySqlClientConfigRepository;
use App\Repository\MySqlUserRepository;
use App\Repository\MySqlTodoRepository;
use App\Repository\MySqlMigrationLogRepository;
use App\Repository\MySqlTemplateRepository;
use App\Repository\MySqlImportJobRepository;
use App\Service\AuthService;
use App\Service\QueryPreviewService;
use App\Service\MigrationService;
use App\Service\SystemService;
use App\Service\CsvImporterService;
use App\Controller\LoginController;
use App\Controller\DashboardController;
use App\Controller\ClientDetailsController;
use App\Controller\ItemController;
use App\Controller\TodoController;
use App\Controller\DashboardApiController;
use App\Controller\QueryApiController;
use App\Controller\ClientConfigController;
use App\Controller\MigrationApiController;
use App\Controller\ConnectionTestController;
use App\Controller\UserController;
use App\Controller\TemplateController;
use App\Controller\ErrorController; 
use App\Controller\CsvImporterController;

try {
    $dbFactory = new DatabaseFactory(__DIR__ . '/../config/database.php');
    $appPdo = $dbFactory->createApplicationConnection();
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(503);
    if (str_starts_with($requestUri ?? '', '/api/')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Serviço indisponível.']);
    } else {
        require __DIR__ . '/../src/View/errors/503.php';
    }
    exit;
}

// -- Repositórios
$userRepository = new MySqlUserRepository($appPdo);
$systemRepository = new MySqlSystemRepository($appPdo);
$clientRepository = new MySqlClientRepository($appPdo);
$clientConfigRepository = new MySqlClientConfigRepository($appPdo);
$todoRepository = new MySqlTodoRepository($appPdo);
$logRepository = new MySqlMigrationLogRepository($appPdo);
$templateRepository = new MySqlTemplateRepository($appPdo);
$jobRepository = new MySqlImportJobRepository($appPdo);

// -- Serviços
$authService = new AuthService($userRepository);
$queryPreviewService = new QueryPreviewService($clientConfigRepository);
$migrationService = new MigrationService($clientConfigRepository, $appPdo);
$systemService = new SystemService($systemRepository, $clientRepository, $clientConfigRepository);
$csvImporterService = new CsvImporterService();

// -- Controllers
$loginController = new LoginController($authService, $basePath);
$dashboardController = new DashboardController($systemRepository, $clientRepository, $basePath);
$clientDetailsController = new ClientDetailsController($clientConfigRepository, $clientRepository, $systemRepository, $basePath);
$itemController = new ItemController($systemRepository, $clientRepository, $systemService, $clientConfigRepository, $templateRepository);
$todoController = new TodoController($todoRepository);
$dashboardApiController = new DashboardApiController($logRepository);
$queryApiController = new QueryApiController($queryPreviewService);
$clientConfigController = new ClientConfigController($clientConfigRepository, $clientRepository);
$migrationApiController = new MigrationApiController($migrationService);
$connectionTestController = new ConnectionTestController();
$userController = new UserController($userRepository, $systemRepository, $clientRepository, $basePath);
$templateController = new TemplateController($templateRepository, $systemRepository, $clientRepository, $basePath);
$errorController = new ErrorController($systemRepository, $clientRepository, $basePath);
$csvImporterController = new CsvImporterController($systemRepository, $clientRepository, $csvImporterService, $basePath);


// 4. ROTEADOR
// =============================================
if (str_starts_with($path, '/api/')) {
    /*********************************/
    /* LÓGICA DA API                 */
    /*********************************/
    header('Content-Type: application/json');
    if (!$authService->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
        exit;
    }

    $route = str_replace('/api', '', $path);

    try {
        switch ($method) {
            // ===================================
            // ROTAS GET (BUSCAR DADOS)
            // ===================================
            case 'GET':
                switch (true) {
                    // Retorna dados para os KPIs e logs do Dashboard
                    case $route === '/dashboard-data':
                        $dashboardData = $dashboardApiController->getData($_GET, $_SESSION['user_id']);
                        echo json_encode(['success' => true, 'data' => $dashboardData]);
                        break;

                    // Retorna a lista de tarefas (To-Do) do usuário
                    case $route === '/todos':
                        echo json_encode(['success' => true, 'todos' => $todoController->list()]);
                        break;

                    // Retorna a lista de TODOS os clientes (para preencher selects)
                    case $route === '/clients':
                        // Agora passamos o ID do usuário logado para o método
                        $clients = $clientRepository->findAll($_SESSION['user_id']);
                        echo json_encode(['success' => true, 'data' => $clients]);
                        break;


                    case $route === '/templates':
                        if (($_SESSION['user_data']['nivel'] ?? 'none') !== 'admin') {
                            throw new Exception("Acesso negado.");
                        }
                        $data = $templateController->list();
                        echo json_encode(['success' => true, 'data' => $data]);
                        break;

                    // Retorna os detalhes de um cliente específico
                    case preg_match('/^\/clients\/(\d+)$/', $route, $matches):
                        echo json_encode(['success' => true, 'data' => $clientRepository->find((int)$matches[1])]);
                        break;

                    // Retorna os clientes de um sistema específico
                    case preg_match('/^\/systems\/(\d+)\/clients$/', $route, $matches):
                        echo json_encode(['success' => true, 'data' => $clientRepository->findBySystemId((int)$matches[1])]);
                        break;
                    
                    // Retorna o status atual de uma migração
                    case preg_match('/^\/migrations\/status\/(\d+)$/', $route, $matches):
                        $status = $migrationApiController->getStatus((int)$matches[1]);
                        echo json_encode(['success' => true, 'status' => $status]);
                        break;

                    case '/csv/stream-log':
                        $csvImporterController->streamImportProgress();
                        break;

                    default:
                        throw new Exception('Rota GET da API não encontrada');
                }
                break;

            // ===================================
            // ROTAS POST (CRIAR DADOS)
            // ===================================
            case 'POST':
                switch ($route) {
                    // Cria um novo Sistema ou Cliente
                    case '/items':
                        $newItem = $itemController->create($_POST);
                        http_response_code(201);
                        echo json_encode(['success' => true, 'message' => 'Item criado!', 'data' => $newItem]);
                        break;

                    // Cria uma nova tarefa (To-Do)
                    case '/todos':
                        $newTodo = $todoController->save($_POST);
                        http_response_code(201);
                        echo json_encode(['success' => true, 'message' => 'Tarefa criada!', 'data' => $newTodo]);
                        break;

                    // Executa uma query para preview
                    case '/query-preview':
                        $rows = $queryApiController->preview($_POST);
                        echo json_encode(['success' => true, 'rows' => $rows]);
                        break;
                    
                    // Inicia um processo de migração
                    case '/migrations/start':
                        $migrationApiController->start($_POST);
                        echo json_encode(['success' => true, 'message' => 'Migração iniciada com sucesso!']);
                        break;
                        
                    // Sinaliza para cancelar uma migração
                    case '/migrations/cancel':
                        $migrationApiController->cancel($_POST);
                        echo json_encode(['success' => true, 'message' => 'Sinal de cancelamento enviado.']);
                        break;
                        
                    case '/templates':
                        if (($_SESSION['user_data']['nivel'] ?? 'none') !== 'admin') throw new Exception("Acesso negado.");
                        // O roteador passa os dados e monta a resposta
                        $templateController->create($_POST, $_FILES);
                        echo json_encode(['success' => true, 'message' => 'Template criado com sucesso!']);
                        break;

                    // Testa uma conexão de banco de dados
                    case '/connections/test':
                        $connectionTestController->test($_POST['connectionData']);
                        echo json_encode(['success' => true, 'message' => 'Conexão bem-sucedida!']);
                        break;
                        
                    case '/users':
                        // Garante que apenas admins possam criar usuários
                        if ($_SESSION['user_data']['nivel'] !== 'admin') {
                            throw new Exception("Acesso negado.");
                        }
                        $userController->create($_POST);
                        echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso!']);
                        break;
                        
                    case '/csv/initiate': // <-- CORREÇÃO: Removido o /api/ daqui
                        $csvImporterController->initiateImport();
                        break;

                    default:
                        throw new Exception('Rota POST da API não encontrada');
                }
                break;

            // ===================================
            // ROTAS PUT (ATUALIZAR DADOS)
            // ===================================
            case 'PUT':
                switch (true) {
                    // Atualiza um Sistema ou Cliente
                    case $route === '/items':
                        parse_str(file_get_contents('php://input'), $putData);
                        $itemController->update($putData);
                        echo json_encode(['success' => true, 'message' => 'Item atualizado!']);
                        break;

                    // Atualiza a ordem da To-Do List
                    case $route === '/todos/order':
                        $requestData = json_decode(file_get_contents('php://input'), true);
                        $todoController->updateOrder($requestData);
                        echo json_encode(['success' => true]);
                        break;

                    // Alterna o status 'completed' de uma tarefa
                    case $route === '/todos/toggle':
                        parse_str(file_get_contents('php://input'), $putData);
                        $todoController->toggle($putData);
                        echo json_encode(['success' => true]);
                        break;
                    
                    // Atualiza uma tarefa existente
                    case preg_match('/^\/todos\/(\d+)$/', $route, $matches):
                        $id = (int)$matches[1];
                        parse_str(file_get_contents('php://input'), $putData);
                        $putData['id'] = $id;
                        $todoController->save($putData);
                        echo json_encode(['success' => true, 'message' => 'Tarefa atualizada!']);
                        break;

                    // Salva a configuração do cliente (auto-save)
                    case preg_match('/^\/client-configs\/(\d+)$/', $route, $matches):
                        $clientId = (int)$matches[1];
                        $formData = json_decode(file_get_contents('php://input'), true);
                        $clientConfigController->update($clientId, $formData);
                        echo json_encode(['success' => true, 'message' => 'Salvo']);
                        break;

                    default:
                        throw new Exception('Rota PUT da API não encontrada');
                }
                break;

            // ===================================
            // ROTAS DELETE (EXCLUIR DADOS)
            // ===================================
            case 'DELETE':
                switch (true) {
                    // Exclui um Sistema ou Cliente
                    case $route === '/items':
                        parse_str(file_get_contents('php://input'), $deleteData);
                        $itemController->delete($deleteData);
                        echo json_encode(['success' => true, 'message' => 'Item excluído!']);
                        break;

                    // Exclui uma tarefa
                    case preg_match('/^\/todos\/(\d+)$/', $route, $matches):
                        $id = (int)$matches[1];
                        $todoController->delete($id);
                        echo json_encode(['success' => true]);
                        break;
                        
                    case preg_match('/^\/templates\/(\d+)$/', $route, $matches):
                        if (($_SESSION['user_data']['nivel'] ?? 'none') !== 'admin') {
                            throw new Exception("Acesso negado.");
                        }
                        $templateController->delete((int)$matches[1]);
                        echo json_encode(['success' => true, 'message' => 'Template excluído com sucesso.']);
                        break;
                        
                    default:
                        throw new Exception('Rota DELETE da API não encontrada');
                }
                break;

            default:
                throw new Exception('Método não permitido na API');
        }
    } catch (InvalidArgumentException $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } catch (Throwable $e) {
        error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
    }

} else {
    // --- LÓGICA DAS PÁGINAS (VERSÃO FINAL COMPLETA) ---
    switch (true) {
        // Rota para a página de login
        case $path === '/login':
            if ($method === 'GET') {
                $loginController->showLoginPage();
            } elseif ($method === 'POST') {
                $loginController->handleLoginRequest();
            }
            break;
        
        // Rota para fazer o logout
        case $path === '/logout':
            $authService->logout();
            header('Location: ' . $basePath . '/login');
            exit;

        // Rota para a página principal/dashboard
        case $path === '/':
        case $path === '/dashboard':
            if (!$authService->isLoggedIn()) { header('Location: ' . $basePath . '/login'); exit; }
            $dashboardController->show();
            break;

        // Rota para a página de gerenciamento de usuários
        case $path === '/usuarios':
            if (!$authService->isLoggedIn()) { header('Location: ' . $basePath . '/login'); exit; }
            $userController->showList();
            break;

        // ROTA PARA A PÁGINA DE TEMPLATES (VERSÃO FINAL E CORRIGIDA)
        case $path === '/templates':
            if (!$authService->isLoggedIn() || ($_SESSION['user_data']['nivel'] ?? 'none') !== 'admin') {
                header('Location: ' . $basePath . '/login'); exit;
            }
            // Agora chama o método do controller, que prepara todos os dados necessários
            $templateController->showPage();
            break;

        // Rota dinâmica para detalhes do cliente (ex: /cliente/123)
        case preg_match('/^\/cliente\/(\d+)$/', $path, $matches):
            if (!$authService->isLoggedIn()) { header('Location: ' . $basePath . '/login'); exit; }
            $clientDetailsController->show((int)$matches[1]);
            break;
        
        // Rota dinâmica para exportar o JSON (ex: /export-config/123)
        case preg_match('/^\/export-config\/(\d+)$/', $path, $matches):
            if (!$authService->isLoggedIn()) { header('Location: ' . $basePath . '/login'); exit; }
            $clientConfigController->exportJson((int)$matches[1]);
            break;
        case '/funcionalidades/importador-csv':
            if (!$authService->isLoggedIn()) { header('Location: ' . $basePath . '/login'); exit; }
            $csvImporterController->showPage();
            break;

        // Se nenhuma rota corresponder, exibe a página 404
        default:
            $errorController->showNotFoundPage();
            break;
    }
}