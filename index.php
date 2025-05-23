<?php
require 'includes/config.php';
require 'includes/auth.php';
require 'includes/funciones.php';

$auth = new Auth();

// Verificar si el usuario está logueado para redirigir adecuadamente
if ($auth->isLoggedIn()) {
    if ($auth->isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: vacantes.php');
    }
    exit;
}

// Obtener las últimas vacantes activas (máximo 6)
$vacantes = obtenerVacantes(null, true);
$ultimas_vacantes = array_slice($vacantes, 0, 6);

// Obtener todas las categorías
$categorias = obtenerCategorias();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobFinder - Encuentra tu trabajo ideal</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos personalizados -->
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/img/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
        }
        
        .vacante-card {
            transition: transform 0.3s;
            height: 100%;
        }
        
        .vacante-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .category-badge {
            font-size: 0.9rem;
            padding: 5px 10px;
        }
        
        .company-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 50%;
            border: 1px solid #eee;
            padding: 5px;
            background: white;
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-briefcase me-2"></i>JobFinder
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vacantes.php">Vacantes</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Categorías
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categorias as $categoria): ?>
                                <li>
                                    <a class="dropdown-item" href="vacantes.php?categoria=<?= $categoria['id'] ?>">
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="login.php" class="btn btn-outline-light me-2">
                        <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
                    </a>
                    <a href="registro.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i> Registrarse
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sección Hero -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Encuentra el trabajo de tus sueños</h1>
            <p class="lead mb-5">Explora entre cientos de ofertas laborales y da el siguiente paso en tu carrera profesional</p>
            
            <!-- Buscador -->
            <form action="vacantes.php" method="GET" class="row g-3 justify-content-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" name="busqueda" placeholder="Puesto, empresa o palabras clave">
                        <button class="btn btn-primary btn-lg" type="submit">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Últimas vacantes -->
    <section class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4">Últimas ofertas publicadas</h2>
            <a href="vacantes.php" class="btn btn-outline-primary">Ver todas</a>
        </div>
        
        <div class="row g-4">
            <?php if (empty($ultimas_vacantes)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No hay vacantes disponibles en este momento. Vuelve pronto.</div>
                </div>
            <?php else: ?>
                <?php foreach ($ultimas_vacantes as $vacante): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card vacante-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <?php if ($vacante['logo_empresa']): ?>
                                        <img src="<?= UPLOAD_DIR_EMPRESAS . htmlspecialchars($vacante['logo_empresa']) ?>" 
                                             alt="<?= htmlspecialchars($vacante['empresa']) ?>" 
                                             class="company-logo me-3">
                                    <?php else: ?>
                                        <div class="company-logo d-flex align-items-center justify-content-center bg-light text-secondary me-3">
                                            <i class="fas fa-building fs-4"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <span class="badge category-badge bg-primary mb-1">
                                            <?= htmlspecialchars($vacante['categoria_nombre']) ?>
                                        </span>
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
                                        <?= date('d M Y', strtotime($vacante['fecha_publicacion'])) ?>
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
    </section>

    <!-- Categorías destacadas -->
    <section class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-5">Explora por categorías</h2>
            
            <div class="row g-4">
                <?php foreach ($categorias as $categoria): ?>
                    <?php
                    $total_vacantes_categoria = 0;
                    foreach ($vacantes as $v) {
                        if ($v['categoria_id'] == $categoria['id'] && $v['activa']) {
                            $total_vacantes_categoria++;
                        }
                    }
                    ?>
                    <div class="col-6 col-md-3">
                        <a href="vacantes.php?categoria=<?= $categoria['id'] ?>" class="text-decoration-none">
                            <div class="card h-100 text-center hover-shadow">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <i class="fas fa-laptop-code fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="card-title"><?= htmlspecialchars($categoria['nombre']) ?></h5>
                                    <p class="card-text small text-muted">
                                        <?= $total_vacantes_categoria ?> vacante<?= $total_vacantes_categoria == 1 ? '' : 's' ?> disponible<?= $total_vacantes_categoria == 1 ? '' : 's' ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4">¿Eres una empresa buscando talento?</h2>
            <p class="lead mb-5">Publica tus vacantes y encuentra a los mejores profesionales</p>
            <a href="login.php" class="btn btn-light btn-lg px-4">
                <i class="fas fa-briefcase me-2"></i> Publicar vacante
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">JobFinder</h5>
                    <p>La mejor plataforma para encontrar tu próximo empleo o el talento que tu empresa necesita.</p>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h5 class="mb-3">Enlaces</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white text-decoration-none">Inicio</a></li>
                        <li class="mb-2"><a href="vacantes.php" class="text-white text-decoration-none">Vacantes</a></li>
                        <li class="mb-2"><a href="login.php" class="text-white text-decoration-none">Login</a></li>
                        <li><a href="registro.php" class="text-white text-decoration-none">Registro</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h5 class="mb-3">Contacto</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Av. Principal 123, Ciudad</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +1 234 567 890</li>
                        <li><i class="fas fa-envelope me-2"></i> info@jobfinder.com</li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 class="mb-3">Síguenos</h5>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4 bg-secondary">
            <div class="text-center">
                <p class="mb-0 small">&copy; <?= date('Y') ?> JobFinder. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script para animaciones -->
    <script>
        // Animación para las cards al aparecer
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.vacante-card');
            
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Inicializar tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>