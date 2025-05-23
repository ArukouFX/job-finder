<?php
require 'includes/config.php';
require 'includes/auth.php';
require 'includes/funciones.php';

$auth = new Auth();

// Obtener filtros
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
$busqueda = trim($_GET['busqueda'] ?? '');

// Obtener categorías para el filtro
$categorias = obtenerCategorias();

// Obtener vacantes activas según filtros
$vacantes = obtenerVacantes($categoria_id, true);

// Filtrar por búsqueda si corresponde
if ($busqueda) {
    $vacantes = array_filter($vacantes, function($v) use ($busqueda) {
        $texto = strtolower($v['titulo'] . ' ' . $v['empresa'] . ' ' . $v['descripcion']);
        return strpos($texto, strtolower($busqueda)) !== false;
    });
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">Ofertas de Empleo</h2>
    <form class="row g-3 mb-4" method="GET">
        <div class="col-md-4">
            <select name="categoria" class="form-select">
                <option value="">Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoria_id == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <input type="text" name="busqueda" class="form-control" placeholder="Buscar por título, empresa o palabra clave" value="<?= htmlspecialchars($busqueda) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-search me-1"></i> Buscar
            </button>
        </div>
    </form>

    <div class="row g-4">
        <?php if (empty($vacantes)): ?>
            <div class="col-12">
                <div class="alert alert-info">No se encontraron vacantes con los filtros seleccionados.</div>
            </div>
        <?php else: ?>
            <?php foreach ($vacantes as $vacante): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <?php if ($vacante['logo_empresa']): ?>
                                    <img src="<?= UPLOAD_DIR_EMPRESAS . htmlspecialchars($vacante['logo_empresa']) ?>"
                                         alt="<?= htmlspecialchars($vacante['empresa']) ?>"
                                         class="company-logo me-3" style="width:60px;height:60px;object-fit:contain;border-radius:50%;border:1px solid #eee;">
                                <?php else: ?>
                                    <div class="company-logo d-flex align-items-center justify-content-center bg-light text-secondary me-3" style="width:60px;height:60px;border-radius:50%;">
                                        <i class="fas fa-building fs-4"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="badge bg-primary mb-1"><?= htmlspecialchars($vacante['categoria_nombre']) ?></span>
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($vacante['titulo']) ?></h5>
                                    <p class="card-text text-muted small mb-0"><?= htmlspecialchars($vacante['empresa']) ?></p>
                                    <p class="card-text text-muted small">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= htmlspecialchars($vacante['ubicacion']) ?>
                                    </p>
                                </div>
                            </div>
                            <p class="card-text mb-3">
                                <?= substr(strip_tags($vacante['descripcion']), 0, 100) ?>...
                            </p>
                            <?php if ($vacante['salario']): ?>
                                <p class="fw-bold text-success mb-3">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    <?= htmlspecialchars($vacante['salario']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?= date('d/m/Y', strtotime($vacante['fecha_publicacion'])) ?>
                                </small>
                                <a href="vacante.php?id=<?= $vacante['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    Ver detalles
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>