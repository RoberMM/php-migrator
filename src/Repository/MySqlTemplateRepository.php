<?php
// src/Repository/MySqlTemplateRepository.php
namespace App\Repository;

use App\Entity\Template; // Importa a nossa entidade
use PDO;

class MySqlTemplateRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Busca um único template e o retorna como um objeto Template.
     */
    public function find(int $id): ?Template
    {
        $stmt = $this->pdo->prepare("SELECT * FROM template_padroes WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }
        
        // "Hidrata" e retorna o objeto da entidade
        return new Template(
            nome: $data['nome'],
            config_padrao: json_decode($data['config_padrao'], true) ?? [],
            id: (int)$data['id']
        );
    }

    /**
     * Busca todos os templates e retorna uma lista de arrays simples (para selects).
     * Neste caso, retornar um array simples é mais eficiente, pois a view só precisa do ID e do nome.
     */
    public function findAll(): array
    {
        return $this->pdo->query("SELECT id, nome FROM template_padroes ORDER BY nome ASC")
                         ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Salva um novo template a partir de um objeto Template.
     */
    public function create(Template $template): bool
    {
        $sql = "INSERT INTO template_padroes (nome, config_padrao) VALUES (:nome, :config_padrao)";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            ':nome' => $template->nome,
            ':config_padrao' => json_encode($template->config_padrao) // Codifica o array para JSON
        ]);
    }

    /**
     * Exclui um template padrão pelo seu ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM template_padroes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}