<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ofertas de Empleo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container">
        <a class="navbar-brand" href="/ofertas-empleo/index.php">JobFinder</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/ofertas-empleo/vacantes.php">Vacantes</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'usuario'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/ofertas-empleo/solicitar_vacante.php">
                                <i class="fas fa-plus-circle"></i> Solicitar vacante
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/ofertas-empleo/perfil.php?id=<?= $_SESSION['user_id'] ?>">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_nombre']) ?>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="/ofertas-empleo/admin/solicitudes_vacantes.php">Solicitudes de vacantes</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="/ofertas-empleo/logout.php">Cerrar sesión</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/ofertas-empleo/login.php">Iniciar Sesión</a></li>
                    <li class="nav-item"><a class="nav-link" href="/ofertas-empleo/registro.php">Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>