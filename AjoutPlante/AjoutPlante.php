<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Variables pour les messages
$successMessage = "";
$errorMessage = "";

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Paramètres de connexion
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cannabis";

    // Connexion à la base de données
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Vérifier la connexion
    if ($conn->connect_error) {
        $errorMessage = "Échec de la connexion : " . $conn->connect_error;
    } else {
        // Inclure la bibliothèque QR Code
        require_once 'phpqrcode/qrlib.php';

        // Vérifier les champs obligatoires
        if (!isset($_POST['identifiant'], $_POST['type'], $_POST['etat'], $_POST['description'], $_POST['emplacement'])) {
            $errorMessage = "Tous les champs du formulaire sont requis.";
        } else {
            // Récupérer et filtrer les entrées
            $identifiant = htmlspecialchars($_POST['identifiant']);
            $type = htmlspecialchars($_POST['type']);
            $etat = htmlspecialchars($_POST['etat']);
            $description = htmlspecialchars($_POST['description']);
            $emplacement = htmlspecialchars($_POST['emplacement']);

            // Générer le contenu du QR code
            $qrContent = "ID: $identifiant\nType: $type\nÉtat: $etat\nDescription: $description\nEmplacement: $emplacement";

            // Créer le dossier pour stocker les QR codes
            $qrDir = 'qrcodes/';
            if (!file_exists($qrDir)) {
                if (!mkdir($qrDir, 0777, true)) {
                    $errorMessage = "Impossible de créer le dossier QR code.";
                }
            }

            // Générer le QR code
            $qrFileName = $qrDir . 'qr_' . uniqid() . '.png';
            QRcode::png($qrContent, $qrFileName, QR_ECLEVEL_L, 4);

            // Insérer les données dans la base de données
            $stmt = $conn->prepare("INSERT INTO plantes (identifiant, type, etat, description, emplacement, qr_code) 
                                    VALUES (?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                $errorMessage = "Erreur SQL : " . $conn->error;
            } else {
                $stmt->bind_param("ssssss", $identifiant, $type, $etat, $description, $emplacement, $qrFileName);

                if ($stmt->execute()) {
                    header("Location: AfficherPlantes.php");
                    exit();
                    
                } else {
                    $errorMessage = "Erreur lors de l'ajout : " . $stmt->error;
                }

                $stmt->close();
            }
        }

        // Fermer la connexion
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une plante de cannabis</title>
    <link rel="stylesheet" href="AjoutPlante.css">
</head>
<body>
    <div class="form-container">
        <h2>Ajouter une plante de cannabis</h2>

        <!-- Affichage des messages -->
        <?php if (!empty($successMessage)): ?>
            <p style="color: green;"><?php echo $successMessage; ?></p>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <p style="color: red;"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <form action="AjoutPlante.php" method="POST">
            <div class="input-group">
                <label for="identifiant">Identifiant</label>
                <input type="text" id="identifiant" name="identifiant" required>
            </div>
            <div class="input-group">
                <label for="type">Type</label>
                <input type="text" id="type" name="type" required>
            </div>
            <div class="input-group">
                <label for="etat">État</label>
                <select id="etat" name="etat" required>
                    <option value="tres bien">Très bien</option>
                    <option value="bien">Bien</option>
                    <option value="moyen">Moyen</option>
                    <option value="mauvais">Mauvais</option>
                </select>
            </div>
            <div class="input-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="input-group">
                <label for="emplacement">Emplacement</label>
                <input type="text" id="emplacement" name="emplacement" required>
            </div>
            <button type="submit" class="btn">Ajouter la plante</button>
        </form>

        <!-- Affichage du QR Code si succès -->
        <?php if (!empty($successMessage) && file_exists($qrFileName)): ?>
            <h3>QR Code généré :</h3>
            <img src="<?php echo $qrFileName; ?>" alt="QR Code">
        <?php endif; ?>
    </div>
</body>
</html>
