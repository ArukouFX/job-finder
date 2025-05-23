<?php
require '../includes/config.php';
require '../includes/auth.php';

$auth = new Auth();

// Verificar autenticación y rol de administrador
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = (new Database())->getConnection();

// Obtener estadísticas para el dashboard
$stats = [
    'total_vacantes' => 0,
    'vacantes_activas' => 0,
    'total_postulaciones' => 0,
    'total_usuarios' => 0
];

// Consulta para obtener estadísticas
try {
    // Total de vacantes
    $stmt = $db->query("SELECT COUNT(*) as total FROM vacantes");
    $stats['total_vacantes'] = $stmt->fetchColumn();
    
    // Vacantes activas
    $stmt = $db->query("SELECT COUNT(*) as total FROM vacantes WHERE activa = 1");
    $stats['vacantes_activas'] = $stmt->fetchColumn();
    
    // Total de postulaciones
    $stmt = $db->query("SELECT COUNT(*) as total FROM postulaciones");
    $stats['total_postulaciones'] = $stmt->fetchColumn();
    
    // Total de usuarios
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'usuario'");
    $stats['total_usuarios'] = $stmt->fetchColumn();
    
    // Últimas vacantes publicadas
    $stmt = $db->query("SELECT v.id, v.titulo, v.empresa, v.fecha_publicacion, c.nombre as categoria 
                       FROM vacantes v 
                       JOIN categorias c ON v.categoria_id = c.id 
                       ORDER BY v.fecha_publicacion DESC 
                       LIMIT 5");
    $ultimas_vacantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Últimos usuarios registrados
    $stmt = $db->query("SELECT id, nombre, email, fecha_registro 
                       FROM usuarios 
                       WHERE rol = 'usuario' 
                       ORDER BY fecha_registro DESC 
                       LIMIT 5");
    $ultimos_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Manejar error
    $error = "Error al obtener estadísticas: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="vacantes.php">
                            <i class="fas fa-briefcase me-2"></i>Vacantes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="categorias.php">
                            <i class="fas fa-tags me-2"></i>Categorías
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="usuarios.php">
                            <i class="fas fa-users me-2"></i>Usuarios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Exportar</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                        <span data-feather="calendar"></span>
                        <?= date('d/m/Y') ?>
                    </button>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Total Vacantes</h6>
                                    <h2 class="card-text"><?= $stats['total_vacantes'] ?></h2>
                                </div>
                                <i class="fas fa-briefcase fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Vacantes Activas</h6>
                                    <h2 class="card-text"><?= $stats['vacantes_activas'] ?></h2>
                                </div>
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Postulaciones</h6>
                                    <h2 class="card-text"><?= $stats['total_postulaciones'] ?></h2>
                                </div>
                                <i class="fas fa-file-alt fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Usuarios</h6>
                                    <h2 class="card-text"><?= $stats['total_usuarios'] ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Últimas vacantes y usuarios -->
            <div class="row">
                <!-- Últimas vacantes -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Últimas Vacantes Publicadas</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Empresa</th>
                                            <th>Categoría</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ultimas_vacantes as $vacante): ?>
                                            <tr>
                                                <td>
                                                    <a href="vacantes.php?action=view&id=<?= $vacante['id'] ?>">
                                                        <?= htmlspecialchars($vacante['titulo']) ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($vacante['empresa']) ?></td>
                                                <td><?= htmlspecialchars($vacante['categoria']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($vacante['fecha_publicacion'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="vacantes.php" class="btn btn-sm btn-primary">Ver todas</a>
                        </div>
                    </div>
                </div>

                <!-- Últimos usuarios -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Últimos Usuarios Registrados</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Fecha Registro</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ultimos_usuarios as $usuario): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="usuarios.php" class="btn btn-sm btn-primary">Ver todos</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>