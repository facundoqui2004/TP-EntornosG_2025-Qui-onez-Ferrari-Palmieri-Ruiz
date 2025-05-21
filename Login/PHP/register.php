<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

// Include config file
include_once '../PHP/config.inc';

// Inicializamos variables
$username = $password = $confirm_password = $role = $mail = "";
$username_err = $password_err = $confirm_password_err = $mail_err = "";
$verification_code = "";

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
    // Validar correo
    if (empty(trim($_POST["mail"]))) {
        $mail_err = "Por favor ingresa un correo electrónico.";
    } elseif (!filter_var(trim($_POST["mail"]), FILTER_VALIDATE_EMAIL)) {
        $mail_err = "Formato de correo inválido.";
    } else {
        $sql = "SELECT id FROM users WHERE mail = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_mail);
            $param_mail = trim($_POST["mail"]);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $mail_err = "Este correo ya está registrado.";
                } else {
                    $mail = trim($_POST["mail"]);
                }
            } else {
                echo "Algo salió mal. Intenta de nuevo.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // enviar token de verificación al correo



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
    $verification_code = substr(md5(uniqid(mt_rand(), true)), 0, 6); // Código aleatorio de 6 caracteres

    // Si no hay errores
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($mail_err)) {

        $verification_code = bin2hex(random_bytes(3)); // Código de 6 caracteres

        $sql = "INSERT INTO users (username, password, role, estado, mail, verification_code) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssiss", $param_username, $param_password, $param_role, $param_estado, $param_mail, $param_code);

            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_role = $role;
            $param_estado = $estado;
            $param_mail = $mail;
            $param_code = $verification_code;

            if (mysqli_stmt_execute($stmt)) {

                // Enviar correo de verificación
                $mailSender = new PHPMailer(true);
                try {
                    $mailSender->isSMTP();
                    $mailSender->Host = 'smtp.gmail.com';
                    $mailSender->SMTPAuth = true;
                    $mailSender->Username = 'yonosoyquinio@gmail.com'; // <-- Cambia esto
                    $mailSender->Password = 'dlul hwet lxhg wffy'; // <-- Cambia esto
                    $mailSender->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mailSender->Port = 587;

                    $mailSender->setFrom('yonosoyquinio@gmail.com', 'Verificación Colibrí');
                    $mailSender->addAddress($mail, $username);
                    $mailSender->Subject = 'Tu código de verificación';
                    $mailSender->Body = "Tu código de verificación es: <b>$verification_code</b>";

                    $mailSender->isHTML(true);
                    $mailSender->send();

                    header("Location: ../PHP/verify.php?mail=" . urlencode($mail));
                    exit;
                } catch (Exception $e) {
                    echo "Error al enviar correo: {$mailSender->ErrorInfo}";
                }

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
            <label>Correo Electrónico</label>
            <input type="mail" name="mail" value="<?php echo htmlspecialchars($mail); ?>">
            <?php if (!empty($mail_err)) echo "<div class='error'>$mail_err</div>"; ?>

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
