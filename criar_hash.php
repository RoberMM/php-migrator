<?php
// --- COLOQUE A SENHA QUE VOCÊ USA PARA LOGAR AQUI ---
$suaSenha = 'admin'; // Exemplo: troque para a sua senha real
// -----------------------------------------------------

$hashGerado = password_hash($suaSenha, PASSWORD_DEFAULT);

// Estilo simples para facilitar a visualização
echo "<div style='font-family: monospace; padding: 20px; background-color: #f0f0f0; border: 1px solid #ccc;'>";
echo "<h3>Gerador de Hash de Senha</h3>";
echo "<b>Senha em Texto Puro:</b> " . htmlspecialchars($suaSenha) . "<br><br>";
echo "<b>Seu Hash Seguro (copie isso):</b><br>";
echo "<input type='text' value='" . htmlspecialchars($hashGerado) . "' style='width: 100%; padding: 8px; margin-top: 5px;' readonly onclick='this.select()'>";
echo "<p style='font-size: 12px; margin-top: 15px;'>O hash gerado já inclui o 'sal' e o algoritmo, tudo que a função password_verify() precisa.</p>";
echo "</div>";
?>