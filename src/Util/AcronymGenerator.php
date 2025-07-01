<?php
// src/Util/AcronymGenerator.php
namespace App\Util;

use PDO;
use RuntimeException;

class AcronymGenerator
{
    /**
     * Gera uma sigla única para um texto, verificando a existência em uma tabela de destino.
     *
     * @param string $texto      O texto base para gerar a sigla (ex: "LegalOne")
     * @param PDO    $pdo        A conexão com o banco de dados de DESTINO para fazer a verificação.
     * @param string $tableName  A tabela de DESTINO onde a sigla deve ser única (ex: 'tab_acao').
     * @param string $columnName A coluna da tabela de DESTINO a ser verificada (ex: 'sigla').
     * @param int    $maxLen     O tamanho máximo da sigla.
     * @return string            A sigla única gerada.
     * @throws RuntimeException  Se não for possível gerar uma sigla única após todas as tentativas.
     */
    public static function generateUnique(string $texto, PDO $pdo, string $tableName, string $columnName, int $maxLen = 8): string
    {
        // 1. Limpa e padroniza o texto de entrada
        $map = ['Á'=>'A','À'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A', 'á'=>'a',
                'É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E', 'é'=>'e',
                'Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I', 'í'=>'i',
                'Ó'=>'O','Ò'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O', 'ó'=>'o',
                'Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U', 'ú'=>'u',
                'Ç'=>'C', 'ç'=>'c', 'Ñ'=>'N', 'ñ'=>'n'];

        $base = strtoupper(strtr($texto, $map));
        $base = preg_replace('/[^A-Z0-9 ]+/', '', $base);
        $base = preg_replace('/\s+/', ' ', trim($base));

        if ($base === '') $base = 'X'; // Garante que nunca seja vazio

        // 2. Prepara a consulta de verificação de unicidade uma única vez
        // CORREÇÃO CRÍTICA: Usamos $tableName e $columnName para tornar a função genérica e segura.
        $stmtCheck = $pdo->prepare("SELECT 1 FROM `$tableName` WHERE `$columnName` = ? LIMIT 1");

        // 3. Estratégia 1: Tenta com substrings
        $palavras = explode(' ', $base);
        $candidatos = [];

        if (count($palavras) === 1) { // Se for uma palavra só
            $p = $palavras[0];
            for ($i = 1; $i <= min($maxLen, strlen($p)); $i++) {
                $candidatos[] = substr($p, 0, $i);
            }
        } else { // Se for mais de uma palavra
            for ($i = 1; $i <= min(4, $maxLen); $i++) {
                $combo = '';
                foreach ($palavras as $parte) {
                    $combo .= substr($parte, 0, min($i, strlen($parte)));
                    if (strlen($combo) >= $maxLen) break;
                }
                $candidatos[] = substr($combo, 0, $maxLen);
            }
        }
        
        foreach (array_unique($candidatos) as $sig) {
            $stmtCheck->execute([$sig]);
            if (!$stmtCheck->fetchColumn()) {
                return $sig; // Encontrou uma sigla única, retorna!
            }
        }

        // 4. Estratégia 2: Tenta com acrônimo + número
        $acronimo = implode('', array_map(fn($p) => $p[0] ?? '', $palavras));
        for ($i = 1; $i <= 99; $i++) {
            $sig = substr($acronimo, 0, $maxLen - strlen((string)$i)) . $i;
            $stmtCheck->execute([$sig]);
            if (!$stmtCheck->fetchColumn()) {
                return $sig;
            }
        }

        // 5. Estratégia 3 (Plano B): Tenta com valores aleatórios
        for ($i = 0; $i < 1000; $i++) {
            $hex = strtoupper(bin2hex(random_bytes(4)));
            $sig = substr($hex, 0, $maxLen);
            $stmtCheck->execute([$sig]);
            if (!$stmtCheck->fetchColumn()) {
                return $sig;
            }
        }

        // Se todas as 3 estratégias falharem, lança um erro.
        throw new RuntimeException('Não foi possível gerar sigla única para o texto: ' . $texto);
    }
}