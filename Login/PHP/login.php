<?php
session_start();

// Redirige si ya está logueado
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: welcome.php");
    exit;
}

// Conexión
include_once "../PHP/config.inc";

// Variables
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Procesa el login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar usuario
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor ingresa tu nombre de usuario.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validar contraseña
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingresa tu contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Si no hay errores, consulta en la DB
    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id, username, password, role, estado FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role, $estado);

                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Si es dueño y no está aprobado
                            if ($role === 'dueno' && $estado == 0) {
                                $login_err = "Tu cuenta de dueño aún no fue aprobada por el administrador.";
                            } else {
                                // Login exitoso
                                session_start();
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;
                                $_SESSION["role"] = $role;

                                header("location: welcome.php");
                                exit;
                            }
                        } else {
                            $login_err = "Usuario o contraseña incorrectos.";
                        }
                    }
                } else {
                    $login_err = "Usuario o contraseña incorrectos.";
                }
            } else {
                echo "Error al ejecutar la consulta.";
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
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body id="login">

<main>
    <div class="title">
        <h1>Iniciar Sesión</h1>
    </div>
    <div class="container">


        <img class="fit-picture" src="../IMG/coli.png" alt="Foto de usuario" />


        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label>Nombre de Usuario</label><br>
            <input type="text" name="username" value="<?php echo $username; ?>"><br>
            <span class="error"><?php echo $username_err; ?></span><br>

            <label>Contraseña</label><br>
            <input type="password" name="password"><br>
            <span class="error"><?php echo $password_err; ?></span><br><br>

            <input type="submit" value="Ingresar" class="login-btn">
        </form>
        <?php 
        if(!empty($login_err)){
            echo '<p style="color: red;">' . $login_err . '</p>';
        }
        ?>
        <hr>
        <p class="register-text">¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a>.</p>
    </div>
</main>

</div>
</body>
</html>
