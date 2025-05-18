<?php
// Include config file
include_once '../PHP/config.inc';

// Inicializamos variables
$username = $password = $confirm_password = $role = "";
$username_err = $password_err = $confirm_password_err = "";

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar nombre de usuario
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor ingresa un nombre de usuario.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
        $username_err = "El nombre solo puede tener letras, números y guiones bajos.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $username_err = "Este usuario ya existe.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Algo salió mal. Intenta de nuevo.";
            }
            $stmt->close();
        }
    }

    // Validar contraseña
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingresa una contraseña.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validar confirmación
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Confirma la contraseña.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Las contraseñas no coinciden.";
        }
    }

    // Obtener rol
    $role = $_POST["role"];
    $estado = ($role == "dueno") ? 0 : 1;

    // Si no hay errores, insertar
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO users (username, password, role, estado) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssi", $param_username, $param_password, $param_role, $param_estado);

            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_role = $role;
            $param_estado = $estado;

            if (mysqli_stmt_execute($stmt)) {
                header("location: login.php");
                exit;
            } else {
                echo "Algo salió mal. Intenta más tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body id="register">
<div class="wrapper">
    <h2>Registro</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <div class="form-group">
            <label>Nombre de Usuario</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
            <?php if (!empty($username_err)) echo "<div class='error'>$username_err</div>"; ?>
        </div>

        <div class="form-group">
            <label>Tipo de Usuario</label>
            <select name="role">
                <option value="cliente" <?php if ($role == "cliente") echo "selected"; ?>>Cliente</option>
                <option value="dueno" <?php if ($role == "dueno") echo "selected"; ?>>Dueño</option>
            </select>
        </div>

        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" value="<?php echo htmlspecialchars($password); ?>">
            <?php if (!empty($password_err)) echo "<div class='error'>$password_err</div>"; ?>
        </div>

        <div class="form-group">
            <label>Confirmar Contraseña</label>
            <input type="password" name="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>">
            <?php if (!empty($confirm_password_err)) echo "<div class='error'>$confirm_password_err</div>"; ?>
        </div>

        <div class="form-actions">
            <input type="submit" value="Registrar">
            <input type="reset" value="Limpiar">
        </div>
        <hr>
        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>.</p>
    </form>
</div>
</body>
</html>
