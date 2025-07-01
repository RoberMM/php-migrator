<?php
// src/Controller/LoginController.php
namespace App\Controller;

use App\Service\AuthService;

class LoginController
{
    /**
     * Usando a promoção de propriedades do construtor (PHP 8+).
     * Isso declara e atribui as propriedades $authService e $basePath automaticamente.
     */
    public function __construct(
        private AuthService $authService,
        private string $basePath
    ) {}

    /**
     * Lida com a submissão do formulário de login (requisições POST).
     */
    public function handleLoginRequest(): void
    {
        // 1. Valida o token CSRF
        if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->showLoginPage("Erro de validação. Por favor, tente novamente.");
            return;
        }
        
        // Token válido, podemos invalidá-lo para não ser usado novamente
        unset($_SESSION['csrf_token']);

        $login = $_POST['login'] ?? '';
        $senha = $_POST['senha'] ?? '';

        // 2. Tenta fazer o login
        if ($this->authService->login($login, $senha)) {
            // 3. Se o login deu certo, AGORA regeneramos a sessão.
            session_regenerate_id(true);

            // 4. E então redirecionamos para o dashboard
            $redirectUrl = empty($this->basePath) ? '/' : $this->basePath;
            header("Location: " . $redirectUrl);
            exit;
        }
        
        // Se o login falhou, mostra a página com erro.
        $this->showLoginPage("Usuário ou senha inválidos.");
    }

    /**
     * Prepara os dados e exibe a página de login.
     */
    public function showLoginPage(?string $error = null): void
    {
        // 1. Garante que um token exista na sessão
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // 2. Prepara um array com TODOS os dados que a view precisa
        $viewData = [
            'error'      => $error,
            'csrf_token' => $_SESSION['csrf_token'],
            'basePath'   => $this->basePath
        ];
        
        // 3. Apenas chama a view. A view terá acesso à variável $viewData.
        require_once __DIR__ . '/../View/login_view.php';
    }
}