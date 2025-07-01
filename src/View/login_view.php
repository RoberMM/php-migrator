<?php
/**
 * src/View/login_view.php
 * * Esta view renderiza o formulário de login.
 * Ela recebe todos os seus dados através do array $viewData,
 * que é preparado e passado pelo LoginController.
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login – Migrador PHP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css"> 
</head>
<body class="hold-transition login-page">

<div class="login-box">
    <div class="login-logo"><a href="#"><b>Migrador</b>PHP</a></div>
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg mb-4">Autentique-se para continuar</p>

            <?php 
            // Acessa a mensagem de erro diretamente do array $viewData
            if (!empty($viewData['error'])): 
            ?>
                <div class="alert alert-danger text-center py-2"><?= htmlspecialchars($viewData['error']) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars($viewData['basePath']) ?>/login" autocomplete="off">
            
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($viewData['csrf_token'] ?? '') ?>">
                
                <div class="input-group mb-3">
                    <input type="text" name="login" class="form-control" placeholder="Usuário" required autofocus>
                    <div class="input-group-append"><div class="input-group-text"><i class="fas fa-user"></i></div></div>
                </div>

                <div class="input-group mb-4">
                    <input type="password" name="senha" id="pwdField" class="form-control" placeholder="Senha" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <a href="#" id="togglePwd" class="text-info" style="text-decoration: none;"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block mb-2">Entrar</button>
            </form>
            
            <p class="mb-0 text-center mt-3"><a href="<?= htmlspecialchars($viewData['basePath']) ?>/esqueci-senha">Esqueci minha senha</a></p>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Script para o botão de mostrar/ocultar senha
    $('#togglePwd').on('click', function(e) {
        e.preventDefault();
        const pwdField = $('#pwdField');
        const isPassword = pwdField.attr('type') === 'password';
        
        pwdField.attr('type', isPassword ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
</script>
</body>
</html>