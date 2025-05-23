<?php
require 'includes/config.php';
require 'includes/auth.php';

$auth = new Auth();
$db = (new Database())->getConnection();

// Obtener el ID del usuario desde la URL
$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si no hay ID, redirigir al inicio
if ($usuario_id <= 0) {
    header('Location: index.php');
    exit;
}

// Obtener datos del usuario
$stmt = $db->prepare("SELECT id, nombre, email, foto, linkedin, rol, fecha_registro FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: index.php');
    exit;
}

// Si es usuario común, obtener postulaciones
$postulaciones = [];
if ($usuario['rol'] === 'usuario') {
    $stmt = $db->prepare(
        "SELECT v.id, v.titulo, v.empresa, v.logo_empresa, v.ubicacion, v.fecha_publicacion, c.nombre as categoria
         FROM postulaciones p
         JOIN vacantes v ON p.vacante_id = v.id
         JOIN categorias c ON v.categoria_id = c.id
         WHERE p.usuario_id = ?
         ORDER BY p.fecha_postulacion DESC"
    );
    $stmt->execute([$usuario_id]);
    $postulaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body d-flex align-items-center">
                    <div class="me-4">
                        <?php if ($usuario['foto']): ?>
                            <img src="<?= UPLOAD_DIR_USUARIOS . htmlspecialchars($usuario['foto']) ?>"
                                 alt="Foto de <?= htmlspecialchars($usuario['nombre']) ?>"
                                 class="rounded-circle" width="100" height="100">
                        <?php else: ?>
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 100px; height: 100px;">
                                <i class="fas fa-user fa-3x text-white"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="mb-1"><?= htmlspecialchars($usuario['nombre']) ?></h3>
                        <p class="mb-1"><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($usuario['email']) ?></p>
                        <p class="mb-1"><i class="fas fa-user-tag me-2"></i><?= ucfirst($usuario['rol']) ?></p>
                        <p class="mb-1"><i class="fas fa-calendar-alt me-2"></i>Registrado el <?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></p>
                        <?php if ($usuario['linkedin']): ?>
                            <p class="mb-0">
                                <a href="<?= htmlspecialchars($usuario['linkedin']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-linkedin me-1"></i> Ver LinkedIn
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($usuario['rol'] === 'usuario'): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Vacantes a las que se ha postulado</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($postulaciones)): ?>
                            <div class="alert alert-info mb-0">
                                Este usuario no se ha postulado a ninguna vacante.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Logo</th>
                                            <th>Título</th>
                                            <th>Empresa</th>
                                            <th>Categoría</th>
                                            <th>Ubicación</th>
                                            <th>Fecha</th>
                                            <th>Ver</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($postulaciones as $vacante): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($vacante['logo_empresa']): ?>
                                                        <img src="<?= UPLOAD_DIR_EMPRESAS . htmlspecialchars($vacante['logo_empresa']) ?>"
                                                             alt="<?= htmlspecialchars($vacante['empresa']) ?>"
                                                             class="rounded" width="40" height="40">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fas fa-building text-secondary"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($vacante['titulo']) ?></td>
                                                <td><?= htmlspecialchars($vacante['empresa']) ?></td>
                                                <td><?= htmlspecialchars($vacante['categoria']) ?></td>
                                                <td><?= htmlspecialchars($vacante['ubicacion']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($vacante['fecha_publicacion'])) ?></td>
                                                <td>
                                                    <a href="vacante.php?id=<?= $vacante['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        Ver
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
            <?php elseif ($usuario['rol'] === 'admin'): ?>
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">Perfil de Administrador</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-0">
                            Este usuario es un administrador del sistema. Puede gestionar vacantes, usuarios y categorías desde el panel de administración.
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>