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

// Obtener ID de la vacante desde la URL
$vacante_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener información de la vacante
$stmt = $db->prepare("SELECT id, titulo, empresa FROM vacantes WHERE id = ?");
$stmt->execute([$vacante_id]);
$vacante = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirigir si la vacante no existe
if (!$vacante) {
    header('Location: vacantes.php');
    exit;
}

// Obtener solicitantes para esta vacante
$stmt = $db->prepare("SELECT p.id, p.mensaje, p.fecha_postulacion, 
                      u.id as usuario_id, u.nombre, u.email, u.foto, u.linkedin
                      FROM postulaciones p
                      JOIN usuarios u ON p.usuario_id = u.id
                      WHERE p.vacante_id = ?
                      ORDER BY p.fecha_postulacion DESC");
$stmt->execute([$vacante_id]);
$solicitantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-users me-2"></i>
                    Solicitantes para: <?= htmlspecialchars($vacante['titulo']) ?>
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="vacantes.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver a Vacantes
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información de la vacante -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="card-title"><?= htmlspecialchars($vacante['titulo']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($vacante['empresa']) ?></h6>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-primary fs-6">
                                <?= count($solicitantes) ?> Postulante<?= count($solicitantes) !== 1 ? 's' : '' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listado de solicitantes -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Listado de Candidatos</h5>
                        <div>
                            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Buscar candidatos...">
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (empty($solicitantes)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay candidatos postulados para esta vacante.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50px">Foto</th>
                                        <th>Nombre</th>
                                        <th>Contacto</th>
                                        <th>LinkedIn</th>
                                        <th>Postulación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solicitantes as $solicitante): ?>
                                        <tr>
                                            <!-- Foto -->
                                            <td>
                                                <?php if ($solicitante['foto']): ?>
                                                    <img src="../<?= UPLOAD_DIR_USUARIOS . htmlspecialchars($solicitante['foto']) ?>" 
                                                         alt="Foto de <?= htmlspecialchars($solicitante['nombre']) ?>" 
                                                         class="rounded-circle" width="40" height="40">
                                                <?php else: ?>
                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <!-- Nombre -->
                                            <td>
                                                <strong><?= htmlspecialchars($solicitante['nombre']) ?></strong>
                                            </td>
                                            
                                            <!-- Contacto -->
                                            <td>
                                                <a href="mailto:<?= htmlspecialchars($solicitante['email']) ?>" 
                                                   class="text-decoration-none" 
                                                   title="Enviar correo">
                                                    <i class="fas fa-envelope me-1 text-primary"></i>
                                                    <?= htmlspecialchars($solicitante['email']) ?>
                                                </a>
                                            </td>
                                            
                                            <!-- LinkedIn -->
                                            <td>
                                                <?php if ($solicitante['linkedin']): ?>
                                                    <a href="<?= htmlspecialchars($solicitante['linkedin']) ?>" 
                                                       target="_blank" 
                                                       class="text-decoration-none"
                                                       title="Ver perfil en LinkedIn">
                                                        <i class="fab fa-linkedin me-1 text-primary"></i>
                                                        Perfil
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No disponible</span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <!-- Fecha de postulación -->
                                            <td>
                                                <?= date('d/m/Y H:i', strtotime($solicitante['fecha_postulacion'])) ?>
                                            </td>
                                            
                                            <!-- Acciones -->
                                            <td>
                                                <!-- Botón para ver mensaje -->
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#mensajeModal<?= $solicitante['id'] ?>"
                                                        title="Ver mensaje">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <!-- Enlace al perfil del usuario -->
                                                <a href="../perfil.php?id=<?= $solicitante['usuario_id'] ?>" 
                                                   class="btn btn-sm btn-outline-info"
                                                   title="Ver perfil completo"
                                                   target="_blank">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                                
                                                <!-- Modal para el mensaje -->
                                                <div class="modal fade" id="mensajeModal<?= $solicitante['id'] ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">
                                                                    Mensaje de <?= htmlspecialchars($solicitante['nombre']) ?>
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <?php if (!empty($solicitante['mensaje'])): ?>
                                                                    <div class="p-3 bg-light rounded">
                                                                        <?= nl2br(htmlspecialchars($solicitante['mensaje'])) ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="alert alert-info mb-0">
                                                                        El candidato no incluyó un mensaje adicional.
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                <a href="mailto:<?= htmlspecialchars($solicitante['email']) ?>" class="btn btn-primary">
                                                                    <i class="fas fa-envelope me-1"></i> Contactar
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($solicitantes)): ?>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            Mostrando <?= count($solicitantes) ?> candidato<?= count($solicitantes) !== 1 ? 's' : '' ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Script para búsqueda -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('tbody tr');
    
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>