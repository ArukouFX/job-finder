<?php
require '../includes/config.php';
require '../includes/auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = (new Database())->getConnection();

// Obtener todos los usuarios
$stmt = $db->query("SELECT id, nombre, email, foto, linkedin, rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-users me-2"></i>Usuarios registrados</h1>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Listado de usuarios</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($usuarios)): ?>
                        <div class="alert alert-info">No hay usuarios registrados.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Foto</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>LinkedIn</th>
                                        <th>Fecha registro</th>
                                        <th>Perfil</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td>
                                                <?php if ($usuario['foto']): ?>
                                                    <img src="../<?= UPLOAD_DIR_USUARIOS . htmlspecialchars($usuario['foto']) ?>"
                                                         alt="Foto de <?= htmlspecialchars($usuario['nombre']) ?>"
                                                         class="rounded-circle" width="40" height="40">
                                                <?php else: ?>
                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center"
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                                            <td>
                                                <span class="badge <?= $usuario['rol'] === 'admin' ? 'bg-warning' : 'bg-primary' ?>">
                                                    <?= ucfirst($usuario['rol']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($usuario['linkedin']): ?>
                                                    <a href="<?= htmlspecialchars($usuario['linkedin']) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                        <i class="fab fa-linkedin"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                                            <td>
                                                <a href="../perfil.php?id=<?= $usuario['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    Ver perfil
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>