<?php
header('Content-Type: application/json'); // Return JSON response

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
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Check if ID is provided
if (isset($_POST['id'])) {
    $id = intval($_POST['id']); // Sanitize input

    // Check if plant exists before deleting
    $checkQuery = "SELECT * FROM plantes WHERE id = $id";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        // Delete plant
        $sql = "DELETE FROM plantes WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["success" => true, "message" => "Plante supprimée avec succès"]);
        } else {
            echo json_encode(["success" => false, "message" => "Erreur de suppression: " . $conn->error]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Erreur: Plante non trouvée"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "ID non fourni"]);
}

// Close connection
$conn->close();
?>
