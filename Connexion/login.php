<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification des identifiants
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Simulated user credentials (Replace with database authentication later)
    $utilisateurs = [
        'admin' => '1234', // Exemples d'identifiants
        'user' => 'pass'
    ];

    if (isset($utilisateurs[$username]) && $utilisateurs[$username] === $password) {
        $_SESSION['user'] = $username; // Store username in session
        
        // Debugging: Print session values before redirection
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";

        // Debugging: Check if headers have already been sent
        if (!headers_sent()) {
            header("Location: ../AjoutPlante/AfficherPlantes.php");
            exit();
        } else {
            echo "<p style='color:red;'>Erreur : Les en-têtes ont déjà été envoyés.</p>";
        }
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion de Cannabis</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #218838;
        }
        p {
            margin-top: 15px;
            color: #555;
        }
        a {
            color: #28a745;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Connexion</h2>

        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Se connecter</button>
        </form>
        <p>Pas encore de compte? <a href="register.php">S'inscrire</a></p>
    </div>
</body>
</html>
