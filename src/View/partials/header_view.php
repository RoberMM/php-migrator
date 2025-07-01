<?php
/**
 * src/View/partials/header_view.php
 *
 * Esta view renderiza o cabeçalho e o menu lateral.
 * Ela recebe todos os seus dados através do array $viewData.
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($viewData['pageTitle'] ?? 'Migrador PHP') ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($viewData['basePath']) ?>/assets/css/style.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div id="alertContainer"
        class="position-fixed top-0 end-0 p-3"
        style="z-index: 1080; width: 350px; max-width: 90vw;">
    </div>
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-dark">

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="javascript:void(0);" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?= htmlspecialchars($viewData['basePath']) ?>/" class="nav-link">
                        <i class="nav-icon fas fa-home"></i> Home
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center gap-2">
                
                <li class="nav-item dropdown">
                    <a id="btnNotif" href="#" class="nav-link dropdown-toggle position-relative" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                        <i class="far fa-bell"></i>
                        <span class="d-none d-sm-inline ms-1">Notificações</span>
                        <span id="notificationBadge" class="badge bg-warning navbar-badge d-none">0</span>
                    </a>
                    <div id="notificationList" class="dropdown-menu dropdown-menu-lg dropdown-menu-end dropdown-menu-dark" aria-labelledby="btnNotif">
                        <div class="notifications-container">
                            <span class="dropdown-item no-notification">Você não tem novas notificações</span>
                        </div>
                        <div id="notificationsFooter" class="notifications-footer" style="display:none">
                            <a href="#" id="markAllRead">Marcar como lido</a>
                            <a href="#" id="clearNotifications">Limpar Notificações</a>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a id="btnLogout" href="<?= htmlspecialchars($viewData['basePath']) ?>/logout" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalLogout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="d-none d-sm-inline ms-1">Sair</span>
                    </a>
                </li>
            </ul>
        </nav>
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="<?= htmlspecialchars($viewData['basePath']) ?>/" class="brand-link">
                <span class="brand-text font-weight-light">Migrador PHP</span>
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" data-accordion="false" role="menu">

                        <li class="nav-item menu-open">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-th"></i>
                                <p>Sistemas <i class="right fas fa-angle-left"></i></p>
                            </a>

                            <ul class="nav nav-treeview">
                                <?php foreach ($viewData['systems'] as $sys): ?>
                                    <?php
                                        $isCurrent = in_array($viewData['selectedClientId'], array_map(fn($c) => $c->id, $viewData['clientsBySystem'][$sys->id] ?? []));
                                    ?>
                                    <li class="nav-item <?= $isCurrent ? 'menu-open' : '' ?>">
                                        <a href="#" class="nav-link <?= $isCurrent ? 'active' : '' ?>">
                                            <i class="far fa-folder nav-icon"></i>
                                            <p><?= htmlspecialchars($sys->nome) ?><i class="right fas fa-angle-left"></i></p>
                                        </a>
                                        <ul class="nav nav-treeview">
                                            <?php foreach (($viewData['clientsBySystem'][$sys->id] ?? []) as $c): ?>
                                                <li class="nav-item">
                                                    <a href="<?= htmlspecialchars($viewData['basePath']) ?>/cliente/<?= $c->id ?>" class="nav-link <?= $c->id == $viewData['selectedClientId'] ? 'active' : '' ?>">
                                                        <i class="far fa-circle nav-icon"></i>
                                                        <p><?= htmlspecialchars($c->nome) ?></p>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                <?php endforeach; ?>

                                <li class="nav-header">AÇÕES</li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalAddItem">
                                        <i class="fas fa-plus-circle nav-icon text-success"></i>
                                        <p>Adicionar Item</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalEditItem">
                                        <i class="nav-icon fas fa-edit text-warning"></i>
                                        <p>Editar Item</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalDeleteItem">
                                        <i class="nav-icon fas fa-trash-alt text-danger"></i>
                                        <p>Excluir Item</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <?php
                        // CONTROLE DE ACESSO NA VIEW: Mostra este item de menu apenas para admins
                        if (isset($viewData['userData']['nivel']) && $viewData['userData']['nivel'] === 'admin'):
                        ?>
                            <li class="nav-header">ADMINISTRAÇÃO</li>
                            <li class="nav-item">
                                <a href="<?= htmlspecialchars($viewData['basePath']) ?>/usuarios" class="nav-link">
                                    <i class="nav-icon fas fa-users-cog"></i>
                                    <p>Usuários</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= htmlspecialchars($viewData['basePath']) ?>/templates" class="nav-link">
                                    <i class="nav-icon fas fa-cogs"></i>
                                    <p>Templates Padrão</p>
                                </a>
                            </li>
                        <?php endif; ?>

                         <li class="nav-header">FUNCIONALIDADES</li>
                        <li class="nav-item">
                            <a href="<?= htmlspecialchars($viewData['basePath']) ?>/funcionalidades/importador-csv" class="nav-link">
                                <i class="nav-icon fas fa-file-csv"></i>
                                <p>Importador de CSV</p>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>
        </aside>