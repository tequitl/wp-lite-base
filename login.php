<?php
require_once('wp-blog-header.php');

$db = new SQLite3('wp-content/database/.ht.sqlite');
$db->busyTimeout(5000);
$db->exec('PRAGMA journal_mode = WAL');

$error_message = '';

if (isset($_POST['submit'])) {
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = 'Por favor complete todos los campos.';
    } else {
        $query = "SELECT * FROM wp_users WHERE user_login = ?";
        $stmt = $db->prepare($query);
        $stmt->bindValue(1, $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);

        if ($user && wp_check_password($password, $user['user_pass'])) {
            // Iniciar sesión exitosa
            session_start();
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['username'] = $user['user_login'];
            header('Location: index.php');
            exit();
        } else {
            $error_message = 'Usuario o contraseña incorrectos.';
        }
    }
    $stmt->close();
}
$db->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Web Descentralizada</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #a8e6cf 0%, #dcedc1 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            width: 100%;
            max-width: 400px;
            margin: 1rem;
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #34495e;
            font-weight: 500;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.3rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #a8e6cf;
            outline: none;
        }

        .submit-btn {
            background: linear-gradient(45deg, #a8e6cf 0%, #dcedc1 100%);
            color: #2c3e50;
            border: none;
            padding: 1rem;
            width: 100%;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        .error-message {
            background-color: #ffd3d3;
            color: #d63031;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .register-link {
            text-align: center;
            margin-top: 1rem;
        }

        .register-link a {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Iniciar Sesión</h1>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="submit" class="submit-btn">Iniciar Sesión</button>
        </form>
        
        <div class="register-link">
            <p>¿No tienes una cuenta? <a href="registro-descentralizado.html">Regístrate aquí</a></p>
        </div>
    </div>
</body>
</html>