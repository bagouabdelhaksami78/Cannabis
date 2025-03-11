<?php
// Prevent duplicate session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../connexion/login.php");
    exit();
}
?>

<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cannabis";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Initialiser les filtres
$search = $_GET['search'] ?? '';
$etat = $_GET['etat'] ?? '';

// Récupérer les statistiques des plantes
$totalPlantsQuery = "SELECT COUNT(*) AS total FROM plantes";
$totalPlants = ($conn->query($totalPlantsQuery))->fetch_assoc()['total'] ?? 0;

$stats = [
    "tres bien" => 0,
    "bien" => 0,
    "moyen" => 0,
    "mauvais" => 0
];

// Compter les plantes pour chaque état
foreach ($stats as $etatKey => &$count) {
    $query = "SELECT COUNT(*) AS count FROM plantes WHERE etat = '$etatKey'";
    $result = $conn->query($query);
    $count = ($result->num_rows > 0) ? $result->fetch_assoc()['count'] : 0;
}

// Construire la requête SQL avec les filtres
$sql = "SELECT * FROM plantes WHERE 1";

if (!empty($search)) {
    $sql .= " AND (identifiant LIKE '%$search%' OR type LIKE '%$search%' OR description LIKE '%$search%' OR emplacement LIKE '%$search%')";
}
if (!empty($etat)) {
    $sql .= " AND etat = '$etat'";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Liste des Plantes</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
            body {
            font-family: Arial, sans-serif;
            background: url('../Images/4651578.jpg') no-repeat center center fixed;
            background-size: cover;
            padding: 20px;
            position: relative;
        }
            
        
        .dashboard, .filter-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
        }
                .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 8px;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-button {
            padding: 10px 15px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .logout-button:hover {
            background-color: #c82333;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            font-size: 16px;
            font-weight: bold;
        }
        .stat-box {
            padding: 10px;
            border-radius: 5px;
            width: 155px;
            text-align: center;
        }
        .total { background-color: #007BFF; color: white; }
        .very-good { background-color: #4CAF50; color: white; }
        .good { background-color: #8BC34A; color: white; }
        .medium { background-color: #FFC107; color: white; }
        .bad { background-color: #F44336; color: white; }
        .plant-container {
         display: flex;
         flex-wrap: wrap;
         gap: 20px;
         justify-content: center; /* Centers items horizontally */
         align-items: center; /* Centers items vertically (optional) */
        }

        .plant-card { 
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            width: 280px;
            text-align: center;
        }
        .plant-card img {
            width: 150px;
            height: 150px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .button-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .add-button {
            width: 280px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .add-button:hover {
            background-color: #218838;
        }
        .filter-form input, .filter-form select {
            padding: 10px;
            width: 250px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .filter-form button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .filter-form button:hover {
            background-color: #0056b3;
        }
    
    .edit-button, .delete-button {
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        margin: 5px;
    }
    .edit-button {
        background-color: #ffc107;
        color: white;
    }
    .edit-button:hover {
        background-color: #e0a800;
    }
    .delete-button {
        background-color: #dc3545;
        color: white;
    }
    .delete-button:hover {
        background-color: #c82333;
    }
    .charts-wrapper {
    display: flex;
    justify-content: space-around; /* Alignement horizontal */
    flex-wrap: wrap; /* Passe à la ligne si l'écran est trop petit */
    gap: 20px; /* Espacement entre les graphiques */
    }

    .chart-container {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        text-align: center;
        margin-bottom: 20px;
        width: 45%; /* Ajuster la largeur pour 2 graphiques côte à côte */
        min-width: 350px; /* Éviter qu'ils deviennent trop petits */
    }
            /* Mode sombre */
        .dark-mode {
            background-color: #121212;
            color: white;
        }

        /* Changer la couleur des cartes et des sections */
        .dark-mode .dashboard,
        .dark-mode .filter-form,
        .dark-mode .chart-container,
        .dark-mode .plant-card {
            background-color: #1e1e1e;
            color: white;
            border: 1px solid #444;
        }

        /* Changer la couleur des boutons */
        .dark-mode .theme-toggle {
            background-color: #ffc107;
            color: black;
        }

        .dark-mode .logout-button {
            background-color: #ff5555;
        }

        /* Mode clair */
        .theme-toggle {
            padding: 10px 15px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .theme-toggle:hover {
            background-color: #555;
        }

        .footer {
            text-align: center;
            padding: 15px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            position: relative;
            bottom: 0;
            width: 100%;
            margin-top: 20px;
            border-radius: 8px;
        }

        .back-to-top {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .back-to-top:hover {
            background-color: #0056b3;
        }

    </style>
</head>
<body>
<!-- Header with Navbar -->
<header class="header">
    <div class="logo">🌿 Cannabis Dashboard</div>
    <div class="user-info">
        <span>Bienvenue, <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong></span>
        <button class="theme-toggle" onclick="toggleDarkMode()">🌙 Mode Sombre</button>
        <button class="logout-button" onclick="window.location.href='../connexion/login.php'">🚪 Déconnexion</button>
    </div>
</header>

    <!-- Dashboard -->
    <div class="dashboard">
        <h2>Tableau de Bord</h2>
        <div class="stats">
            <div class="stat-box total">📊 Total Plantes: <strong><?php echo $totalPlants; ?></strong></div>
            <div class="stat-box very-good">🌱 Très Bien: <strong><?php echo $stats["tres bien"]; ?></strong></div>
            <div class="stat-box good">✅ Bien: <strong><?php echo $stats["bien"]; ?></strong></div>
            <div class="stat-box medium">⚠️ Moyen: <strong><?php echo $stats["moyen"]; ?></strong></div>
            <div class="stat-box bad">❌ Mauvais: <strong><?php echo $stats["mauvais"]; ?></strong></div>
        </div>
    </div>

    


    <!-- Search & Filter Form -->
    <div class="filter-form">
        <h2>Filtrer les Plantes</h2>
        <form method="GET">
            <input type="text" name="search" placeholder="Rechercher par nom, type, description..." value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="etat">
                <option value="">-- Filtrer par État --</option>
                <option value="tres bien" <?php if ($etat == "tres bien") echo "selected"; ?>>Très bien</option>
                <option value="bien" <?php if ($etat == "bien") echo "selected"; ?>>Bien</option>
                <option value="moyen" <?php if ($etat == "moyen") echo "selected"; ?>>Moyen</option>
                <option value="mauvais" <?php if ($etat == "mauvais") echo "selected"; ?>>Mauvais</option>
            </select>

            <button type="submit">Rechercher</button>
            <button type="button" onclick="window.location.href='?';">Réinitialiser</button>
        </form>
    </div>

    <div class="button-container">
        <button class="add-button" onclick="window.location.href='AjoutPlante.php'">Ajouter Plante</button>
    </div>

    <div class="plant-container">
    <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="plant-card">
            <h3>(ID: <?php echo htmlspecialchars($row['id']); ?>)</h3>
            <p><strong>Nom:</strong> <?php echo htmlspecialchars($row['identifiant']); ?></p>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($row['type']); ?></p>
            <p><strong>État:</strong> <?php echo htmlspecialchars($row['etat']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
            <p><strong>Emplacement:</strong> <?php echo htmlspecialchars($row['emplacement']); ?></p>
            
            <?php if (!empty($row['qr_code']) && file_exists($row['qr_code'])) { ?>
                <img src="<?php echo htmlspecialchars($row['qr_code']); ?>" alt="QR Code">
            <?php } else { ?>
                <p>QR Code non disponible</p>
            <?php } ?>

            <!-- Buttons for Editing and Deleting -->
            <div class="button-container">
                <button class="edit-button" onclick="window.location.href='editPlant.php?id=<?php echo $row['id']; ?>'">✏️ Modifier</button>
                <button class="delete-button" onclick="confirmDelete(<?php echo $row['id']; ?>)">🗑️ Supprimer</button>
            </div>
        </div>
    <?php } ?>
    </div>
    <script>
    function confirmDelete(id) {
        if (confirm("Voulez-vous vraiment supprimer cette plante ?")) {
            fetch('deletePlant.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload(); // Refresh the page
                } else {
                    alert("Erreur : " + data.message);
                }
            })
            .catch(error => console.error("Erreur AJAX : ", error));
        }
    }
</script>
<<div class="charts-wrapper">
    <!-- Graphique en barres -->
    <div class="chart-container">
        <h2>État des Plantes</h2>
        <canvas id="etatChart" width="400" height="200"></canvas>
    </div>

    <!-- Graphique circulaire -->
    <div class="chart-container">
        <h2>Répartition des Plantes</h2>
        <canvas id="etatPieChart" width="400" height="200"></canvas>
    </div>

    <script>
    var ctxBar = document.getElementById('etatChart').getContext('2d');
    var ctxPie = document.getElementById('etatPieChart').getContext('2d');

    var statsData = [<?php echo $stats["tres bien"]; ?>, <?php echo $stats["bien"]; ?>, <?php echo $stats["moyen"]; ?>, <?php echo $stats["mauvais"]; ?>];

    var colors = ['#4CAF50', '#8BC34A', '#FFC107', '#F44336'];

    // Graphique en barres
    var etatChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Très bien', 'Bien', 'Moyen', 'Mauvais'],
            datasets: [{
                label: 'Nombre de Plantes',
                data: statsData,
                backgroundColor: colors
            }]
        }
    });

    // Graphique circulaire
    var etatPieChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Très bien', 'Bien', 'Moyen', 'Mauvais'],
            datasets: [{
                data: statsData,
                backgroundColor: colors
            }]
        }
    });
    </script>

    <footer class="footer">
    <p>&copy; <?php echo date("Y"); ?> Cannabis. Tous droits réservés. Bagou Abdelhak Sami</p>
    <button onclick="scrollToTop()" class="back-to-top">⬆️ Retour en haut</button>
    </footer>

    <script>
    function toggleDarkMode() {
        document.body.classList.toggle("dark-mode");

        // Sauvegarde la préférence dans le localStorage
        if (document.body.classList.contains("dark-mode")) {
            localStorage.setItem("theme", "dark");
        } else {
            localStorage.setItem("theme", "light");
        }
    }

    // Appliquer le mode sombre au chargement de la page si activé
    document.addEventListener("DOMContentLoaded", () => {
        if (localStorage.getItem("theme") === "dark") {
            document.body.classList.add("dark-mode");
        }
    });
    </script>
    <script>
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>

<?php $conn->close(); ?> 

