<?php
// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cannabis";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Get plant ID from URL
$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID de plante manquant.");
}

// Fetch plant data
$sql = "SELECT * FROM plantes WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Plante non trouvée.");
}

$plant = $result->fetch_assoc();

// Update plant in database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = $_POST['identifiant'];
    $type = $_POST['type'];
    $etat = $_POST['etat'];
    $description = $_POST['description'];
    $emplacement = $_POST['emplacement'];

    $updateQuery = "UPDATE plantes SET 
        identifiant = '$identifiant', 
        type = '$type', 
        etat = '$etat', 
        description = '$description', 
        emplacement = '$emplacement'
        WHERE id = $id";

    if ($conn->query($updateQuery) === TRUE) {
        header("Location: AfficherPlantes.php?id=" . $id);
        exit();

    } else {
        echo "Erreur de mise à jour: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Plante</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; text-align: center; }
        .form-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); display: inline-block; text-align: left; }
        .form-container h2 { text-align: center; }
        label { font-weight: bold; }
        input, select, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #218838; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Modifier Plante</h2>
    <form method="POST">
        <label>Nom de la Plante:</label>
        <input type="text" name="identifiant" value="<?php echo htmlspecialchars($plant['identifiant']); ?>" required>

        <label>Type:</label>
        <input type="text" name="type" value="<?php echo htmlspecialchars($plant['type']); ?>" required>

        <label>État:</label>
        <select name="etat">
            <option value="tres bien" <?php if ($plant['etat'] == "tres bien") echo "selected"; ?>>Très Bien</option>
            <option value="bien" <?php if ($plant['etat'] == "bien") echo "selected"; ?>>Bien</option>
            <option value="moyen" <?php if ($plant['etat'] == "moyen") echo "selected"; ?>>Moyen</option>
            <option value="mauvais" <?php if ($plant['etat'] == "mauvais") echo "selected"; ?>>Mauvais</option>
        </select>

        <label>Description:</label>
        <textarea name="description"><?php echo htmlspecialchars($plant['description']); ?></textarea>

        <label>Emplacement:</label>
        <input type="text" name="emplacement" value="<?php echo htmlspecialchars($plant['emplacement']); ?>" required>

        <button type="submit">Mettre à Jour</button>
        <button type="button" onclick="window.location.href='index.php'">Annuler</button>
    </form>
</div>

</body>
</html>
