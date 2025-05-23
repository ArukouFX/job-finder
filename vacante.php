<?php
require 'includes/config.php';
require 'includes/db.php';
require 'includes/funciones.php';

$db = (new Database())->getConnection();

// Obtener el ID de la vacante desde la URL
$vacante_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si no hay ID, redirigir a vacantes
if ($vacante_id <= 0) {
    header('Location: vacantes.php');
    exit;
}

// Obtener datos de la vacante
$stmt = $db->prepare("SELECT v.*, c.nombre as categoria_nombre FROM vacantes v JOIN categorias c ON v.categoria_id = c.id WHERE v.id = ? AND v.activa = 1");
$stmt->execute([$vacante_id]);
$vacante = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vacante) {
    // Vacante no encontrada o inactiva
    include 'includes/header.php';
    echo '<div class="container mt-5"><div class="alert alert-danger">Vacante no encontrada o no disponible.</div></div>';
    include 'includes/footer.php';
    exit;
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <?php if ($vacante['logo_empresa']): ?>
                            <img src="<?= UPLOAD_DIR_EMPRESAS . htmlspecialchars($vacante['logo_empresa']) ?>"
                                 alt="<?= htmlspecialchars($vacante['empresa']) ?>"
                                 class="company-logo me-3" style="width:70px;height:70px;object-fit:contain;border-radius:50%;border:1px solid #eee;">
                        <?php else: ?>
                            <div class="company-logo d-flex align-items-center justify-content-center bg-light text-secondary me-3" style="width:70px;height:70px;border-radius:50%;">
                                <i class="fas fa-building fs-3"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <span class="badge bg-primary mb-1"><?= htmlspecialchars($vacante['categoria_nombre']) ?></span>
                            <h3 class="mb-1"><?= htmlspecialchars($vacante['titulo']) ?></h3>
                            <p class="mb-0 text-muted"><?= htmlspecialchars($vacante['empresa']) ?></p>
                            <p class="mb-0 text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($vacante['ubicacion']) ?></p>
                        </div>
                    </div>
                    <p class="mb-3"><?= nl2br(htmlspecialchars($vacante['descripcion'])) ?></p>
                    <?php if ($vacante['salario']): ?>
                        <p class="fw-bold text-success mb-3">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            <?= htmlspecialchars($vacante['salario']) ?>
                        </p>
                    <?php endif; ?>
                    <p class="mb-1"><strong>Publicado:</strong> <?= date('d/m/Y', strtotime($vacante['fecha_publicacion'])) ?></p>
                    <p class="mb-1"><strong>Email de contacto:</strong> <a href="mailto:<?= htmlspecialchars($vacante['contacto_email']) ?>"><?= htmlspecialchars($vacante['contacto_email']) ?></a></p>
                </div>
                <div class="card-footer bg-white">
                    <?php
                    // Mostrar botón de postulación solo a usuarios comunes logueados
                    if (isset($_SESSION['user_id']) && isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'usuario') {
                        ?>
                        <a href="postular.php?id=<?= $vacante['id'] ?>" class="btn btn-success">
                            <i class="fas fa-paper-plane me-1"></i> Postularme a esta vacante
                        </a>
                    <?php } else { ?>
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i> Inicia sesión para postularte
                        </a>
                    <?php } ?>
                    <a href="vacantes.php" class="btn btn-secondary ms-2">Volver a vacantes</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>