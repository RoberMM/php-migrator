<?php require_once __DIR__ . '/partials/header_view.php'; ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between">
            <h1>Gerenciamento de Usuários</h1>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAddUser">
                <i class="fas fa-plus-circle"></i> Adicionar Usuário
            </button>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card"><div class="card-body">
                <table class="table table-bordered">
                    <thead><tr><th>ID</th><th>Nome</th><th>Login</th><th>Nível</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php foreach ($viewData['users'] as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['nome']) ?></td>
                                <td><?= htmlspecialchars($user['login']) ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($user['nivel']) ?></span></td>
                                <td></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div></div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/partials/footer_view.php'; ?>