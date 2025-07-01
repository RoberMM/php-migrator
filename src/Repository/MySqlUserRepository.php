<?php
namespace App\Repository;
use App\Entity\User;
use PDO;

class MySqlUserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    // ATUALIZADO: Adiciona a coluna 'nome' ao SELECT
    public function findByLogin(string $login): ?User
    {
        $sql = "SELECT id, login, nome, senha AS hashedPassword, nivel FROM usuarios WHERE login = :login LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':login' => $login]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new User(
            id: (int)$data['id'], login: $data['login'],
            nome: $data['nome'], // <-- Adicionado
            hashedPassword: $data['hashedPassword'], nivel: $data['nivel']
        );
    }
    
    // ATUALIZADO: Adiciona a coluna 'nome' ao SELECT
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT id, login, nome, nivel FROM usuarios ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Salva um novo usuário no banco e retorna a entidade com o ID correto.
     */
    public function save(User $user): User
    {
        // A lógica de UPDATE entraria aqui se tivéssemos um formulário de edição de usuário
        if ($user->id) {
            // Lógica de UPDATE (não implementada ainda)
            // ...
            return $user;
        }

        // Lógica de INSERT
        $sql = "INSERT INTO usuarios (login, nome, senha, nivel) VALUES (:login, :nome, :senha, :nivel)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':login' => $user->login,
            ':nome' => $user->nome,
            ':senha' => $user->hashedPassword,
            ':nivel' => $user->nivel
        ]);

        // ==============================================================
        // CORREÇÃO: Em vez de modificar o objeto existente, criamos um novo
        // com o ID que o banco de dados acabou de gerar.
        // ==============================================================
        $id = (int)$this->pdo->lastInsertId();
        
        return new User(
            $id,
            $user->login,
            $user->nome,
            $user->hashedPassword,
            $user->nivel
        );
    }
}