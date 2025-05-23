<?php
require 'includes/config.php';
require 'includes/auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || $auth->isAdmin()) {
    header('Location: login.php');
    exit;
}

$db = (new Database())->getConnection();
$usuario_id = $_SESSION['user_id'];
$vacante_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mensaje = '';
$error = '';
$success = false;

// Obtener datos de la vacante
$stmt = $db->prepare("SELECT v.*, c.nombre as categoria_nombre FROM vacantes v JOIN categorias c ON v.categoria_id = c.id WHERE v.id = ? AND v.activa = 1");
$stmt->execute([$vacante_id]);
$vacante = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vacante) {
    header('Location: vacantes.php');
    exit;
}

// Verificar si ya está postulado
$stmt = $db->prepare("SELECT id FROM postulaciones WHERE vacante_id = ? AND usuario_id = ?");
$stmt->execute([$vacante_id, $usuario_id]);
$ya_postulado = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$ya_postulado) {
    $mensaje = trim($_POST['mensaje'] ?? '');

    $stmt = $db->prepare("INSERT INTO postulaciones (vacante_id, usuario_id, mensaje) VALUES (?, ?, ?)");
    if ($stmt->execute([$vacante_id, $usuario_id, $mensaje])) {
        $success = true;
    } else {
        $error = "Ocurrió un error al postularte. Intenta nuevamente.";
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Postular a: <?= htmlspecialchars($vacante['titulo']) ?></h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            ¡Tu postulación fue enviada correctamente!
                            <a href="vacantes.php" class="btn btn-sm btn-primary ms-2">Ver más vacantes</a>
                        </div>
                    <?php elseif ($ya_postulado): ?>
                        <div class="alert alert-info">
                            Ya te has postulado a esta vacante.
                            <a href="vacantes.php" class="btn btn-sm btn-primary ms-2">Ver más vacantes</a>
                        </div>
                    <?php else: ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Mensaje para el empleador (opcional)</label>
                                <textarea name="mensaje" class="form-control" rows="4" maxlength="1000" placeholder="Cuéntale al empleador por qué eres el candidato ideal..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Enviar postulación</button>
                            <a href="vacantes.php" class="btn btn-secondary ms-2">Cancelar</a>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Detalles de la Vacante</h5>
                </div>
                <div class="card-body">
                    <h5><?= htmlspecialchars($vacante['titulo']) ?></h5>
                    <p class="mb-1"><strong>Empresa:</strong> <?= htmlspecialchars($vacante['empresa']) ?></p>
                    <p class="mb-1"><strong>Categoría:</strong> <?= htmlspecialchars($vacante['categoria_nombre']) ?></p>
                    <p class="mb-1"><strong>Ubicación:</strong> <?= htmlspecialchars($vacante['ubicacion']) ?></p>
                    <?php if ($vacante['salario']): ?>
                        <p class="mb-1"><strong>Salario:</strong> <?= htmlspecialchars($vacante['salario']) ?></p>
                    <?php endif; ?>
                    <p class="mb-2"><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($vacante['descripcion'])) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>