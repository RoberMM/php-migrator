<?php
// src/Service/AuthService.php
namespace App\Service;

use App\Repository\UserRepositoryInterface;

class AuthService
{
    // O construtor agora só recebe as dependências, sem executar nenhuma ação.
    public function __construct(private UserRepositoryInterface $userRepository)
    {
        // O controle da sessão agora é responsabilidade 100% do public/index.php
    }

    /**
     * Valida as credenciais e prepara a sessão em caso de sucesso.
     */
    public function login(string $login, string $plainPassword): bool
    {
        $user = $this->userRepository->findByLogin($login);

        if ($user === null) {
            return false;
        }

        if (password_verify($plainPassword, $user->hashedPassword)) {
            // Prepara a sessão para o usuário autenticado.
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_data'] = [
                'id' => $user->id,
                'login' => $user->login,
                'nivel' => $user->nivel
            ];
            return true;
        }

        return false;
    }

    /**
     * Verifica se há um usuário logado na sessão.
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Destrói a sessão do usuário (logout).
     */
    public function logout(): void
    {
        // Limpa todas as variáveis da sessão
        $_SESSION = [];

        // Destrói a sessão
        session_destroy();
    }
}