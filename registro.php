<?php
require 'includes/config.php';
require 'includes/auth.php';

$nombre = '';
$email = '';
$linkedin = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $linkedin = trim($_POST['linkedin'] ?? '');
    $foto = $_FILES['foto'] ?? null;

    // Validaciones básicas
    if (!$nombre || !$email || !$password) {
        $error = "Todos los campos obligatorios deben ser completados.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } elseif ($linkedin && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
        $error = "El enlace de LinkedIn no es válido.";
    } else {
        // Verificar si el email ya existe
        $db = (new Database())->getConnection();
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "El correo electrónico ya está registrado.";
        } else {
            // Manejar la subida de la foto
            $foto_nombre = null;
            if ($foto && $foto['tmp_name']) {
                $ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $foto_nombre = uniqid('user_') . '.' . $ext;
                    $destino = UPLOAD_DIR_USUARIOS . $foto_nombre;
                    move_uploaded_file($foto['tmp_name'], $destino);
                } else {
                    $error = "La foto debe ser JPG, PNG o GIF.";
                }
            }

            if (!$error) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, foto, linkedin) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$nombre, $email, $hash, $foto_nombre, $linkedin])) {
                    $success = true;
                } else {
                    $error = "Ocurrió un error al registrar el usuario.";
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Registro de Usuario</h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            ¡Registro exitoso! <a href="login.php">Inicia sesión aquí</a>.
                        </div>
                    <?php else: ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Nombre completo *</label>
                                <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($nombre) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Correo electrónico *</label>
                                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña *</label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto de perfil (opcional)</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Enlace de LinkedIn (opcional)</label>
                                <input type="url" name="linkedin" class="form-control" value="<?= htmlspecialchars($linkedin) ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Registrarse</button>
                            <a href="login.php" class="btn btn-link">¿Ya tienes cuenta? Inicia sesión</a>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>