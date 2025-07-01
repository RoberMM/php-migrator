<?php
// src/Controller/UserController.php
namespace App\Controller;

use App\Repository\UserRepositoryInterface;
use App\Repository\SystemRepositoryInterface; // <-- 1. Importa os repositórios necessários
use App\Repository\ClientRepositoryInterface;
use App\Entity\User;
use InvalidArgumentException;

class UserController
{
    // 2. O construtor agora recebe todas as dependências
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private SystemRepositoryInterface $systemRepository,
        private ClientRepositoryInterface $clientRepository,
        private string $basePath
    ) {}

    /**
     * Exibe a página com a lista de todos os usuários.
     */
    public function showList(): void
    {
        // Controle de Acesso: Apenas administradores podem ver esta página
        if (($_SESSION['user_data']['nivel'] ?? 'none') !== 'admin') {
            http_response_code(403); // Erro "Forbidden" (Acesso Proibido)
            require __DIR__ . '/../View/errors/403.php'; // (Crie este arquivo de erro, se desejar)
            exit;
        }

        // Busca os dados para o conteúdo principal da página
        $users = $this->userRepository->findAll();

        // 3. LÓGICA PARA BUSCAR OS DADOS DO MENU LATERAL
        $userId = $_SESSION['user_id'];
        $systems = $this->systemRepository->findByUserId($userId);
        $clientsBySystem = [];
        foreach ($systems as $system) {
            $clientsBySystem[$system->id] = $this->clientRepository->findBySystemId($system->id);
        }

        // 4. Prepara todos os dados e renderiza a view
        $viewData = [
            'pageTitle' => 'Gerenciamento de Usuários',
            'users' => $users,
            'userData' => $_SESSION['user_data'],
            'basePath' => $this->basePath,
            'systems' => $systems, // <-- Agora com dados reais
            'clientsBySystem' => $clientsBySystem, // <-- Agora com dados reais
            'selectedClientId' => 0 // Nenhum cliente está ativo nesta página
        ];

        require_once __DIR__ . '/../View/user_list_view.php';
    }

    /**
     * Cria um novo usuário a partir dos dados do formulário.
     */
    public function create(array $postData): User
    {
        $login = $postData['login'] ?? '';
        $nome = $postData['nome'] ?? '';
        $nivel = $postData['nivel'] ?? 'consultor';
        $senha = $postData['senha'] ?? '';

        if (empty($login) || empty($nome) || empty($senha) || empty($nivel)) {
            throw new InvalidArgumentException("Todos os campos são obrigatórios.");
        }
        
        $hashedPassword = password_hash($senha, PASSWORD_DEFAULT);

        // O ID é 0 porque será gerado pelo banco de dados
        $newUser = new User(0, $login, $nome, $hashedPassword, $nivel);

        // O repositório salva e retorna a entidade FINAL com o ID correto
        return $this->userRepository->save($newUser);
    }
}