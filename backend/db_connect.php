<?php
$servername = "localhost";
$username = "root"; // Remplacez par votre nom d'utilisateur MySQL
$password = "";     // Remplacez par votre mot de passe MySQL
$dbname = "education_platform_db"; // Nom de la base de données

// Tenter de se connecter à MySQL sans spécifier de base de données
$conn = new mysqli($servername, $username, $password);

// Vérifier la connexion au serveur MySQL
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Échec de la connexion au serveur MySQL.',
        'error_details' => $conn->connect_error
    ]);
    exit();
}

// Vérifier si la base de données existe, sinon la créer
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_create_db) === TRUE) {
    // Base de données créée ou déjà existante.
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la création de la base de données.',
        'error_details' => $conn->error
    ]);
    $conn->close();
    exit();
}

// Sélectionner la base de données
if (!$conn->select_db($dbname)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la sélection de la base de données.',
        'error_details' => $conn->error
    ]);
    $conn->close();
    exit();
}
