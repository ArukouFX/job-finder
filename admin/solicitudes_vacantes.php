<?php
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/funciones.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = (new Database())->getConnection();

// Aprobar o rechazar solicitud
if (isset($_GET['aprobar'])) {
    $id = intval($_GET['aprobar']);
    // Obtener datos de la solicitud
    $stmt = $db->prepare("SELECT * FROM solicitudes_vacantes WHERE id = ?");
    $stmt->execute([$id]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($solicitud) {
        // Insertar en vacantes
        $stmt2 = $db->prepare("INSERT INTO vacantes (titulo, descripcion, empresa, ubicacion, salario, categoria_id, contacto_email, logo_empresa, activa, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
        $stmt2->execute([
            $solicitud['titulo'],
            $solicitud['descripcion'],
            $solicitud['empresa'],
            $solicitud['ubicacion'],
            $solicitud['salario'],
            $solicitud['categoria_id'],
            $solicitud['contacto_email'],
            $solicitud['logo_empresa'],
            $_SESSION['user_id']
        ]);
        // Marcar como aprobada
        $db->prepare("UPDATE solicitudes_vacantes SET estado = 'aprobada' WHERE id = ?")->execute([$id]);
    }
    header('Location: solicitudes_vacantes.php');
    exit;
}
if (isset($_GET['rechazar'])) {
    $id = intval($_GET['rechazar']);
    $db->prepare("UPDATE solicitudes_vacantes SET estado = 'rechazada' WHERE id = ?")->execute([$id]);
    header('Location: solicitudes_vacantes.php');
    exit;
}

// Obtener todas las solicitudes pendientes
$stmt = $db->query("SELECT s.*, u.nombre as usuario_nombre, c.nombre as categoria_nombre FROM solicitudes_vacantes s JOIN usuarios u ON s.usuario_id = u.id JOIN categorias c ON s.categoria_id = c.id ORDER BY s.fecha_solicitud DESC");
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="container mt-4">
    <h2>Solicitudes de Vacantes</h2>
    <div class="card">
        <div class="card-header">
            <h5>Solicitudes recibidas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Empresa</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $sol): ?>
                            <tr>
                                <td><?= htmlspecialchars($sol['titulo']) ?></td>
                                <td><?= htmlspecialchars($sol['categoria_nombre']) ?></td>
                                <td><?= htmlspecialchars($sol['empresa']) ?></td>
                                <td><?= htmlspecialchars($sol['usuario_nombre']) ?></td>
                                <td>
                                    <span class="badge <?= $sol['estado'] === 'pendiente' ? 'bg-warning' : ($sol['estado'] === 'aprobada' ? 'bg-success' : 'bg-danger') ?>">
                                        <?= ucfirst($sol['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($sol['estado'] === 'pendiente'): ?>
                                        <a href="?aprobar=<?= $sol['id'] ?>" class="btn btn-sm btn-success">Aprobar</a>
                                        <a href="?rechazar=<?= $sol['id'] ?>" class="btn btn-sm btn-danger">Rechazar</a>
                                    <?php else: ?>
                                        <span class="text-muted">Sin acciones</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>