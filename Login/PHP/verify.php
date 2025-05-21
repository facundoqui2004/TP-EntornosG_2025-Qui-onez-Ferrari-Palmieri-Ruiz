<?php
include_once '../PHP/config.inc';

$mensaje = "";
$mail = isset($_GET['mail']) ? $_GET['mail'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_code = $_POST['code'];
    $mail = $_POST['mail'];

    $sql = "SELECT verification_code FROM users WHERE mail = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $mail);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $db_code);

    $fetch_ok = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt); // ✅ Cerramos el primer statement

    if ($fetch_ok && $user_code === $db_code) {
        $update = mysqli_prepare($link, "UPDATE users SET is_verified = 1 WHERE mail = ?");
        mysqli_stmt_bind_param($update, "s", $mail);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update); // ✅ Cerramos también este
        $mensaje = "¡Correo verificado correctamente! Ahora puedes <a href='login.php'>iniciar sesión</a>.";
        sleep(10); // Espera 3 segundos
        header("location: login.php");
    } else {
        $mensaje = "Código incorrecto. Por favor, intenta de nuevo.";
    }
    
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar Correo</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body id="register">
<div class="wrapper">
    <h2>Verificación de Correo</h2>

    <?php if (!empty($mensaje)) echo "<div class='info'>$mensaje</div>"; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <div class="form-group">
            <label for="code">Ingresa el código enviado a tu correo</label>
            <input type="text" id="code" name="code" required>
        </div>

        <input type="hidden" name="mail" value="<?php echo htmlspecialchars($_GET['mail'] ?? ''); ?>">

        <div class="form-actions">
            <input type="submit" value="Verificar">
        </div>
    </form>
</div>
</body>
</html>
