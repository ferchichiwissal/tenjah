<?php
$servername = "localhost";
$username = "root"; // Remplacez par votre nom d'utilisateur MySQL
$password = "";     // Remplacez par votre mot de passe MySQL
$dbname = "education_platform_db"; // Nom de la base de données

// Tenter de se connecter à MySQL sans spécifier de base de données
$conn = new mysqli($servername, $username, $password);

// Vérifier la connexion au serveur MySQL
if ($conn->connect_error) {
    die("Échec de la connexion au serveur MySQL : " . $conn->connect_error);
}

// Vérifier si la base de données existe, sinon la créer
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_create_db) === TRUE) {
    // echo "Base de données '$dbname' créée ou déjà existante.<br>";
} else {
    die("Erreur lors de la création de la base de données : " . $conn->error);
}

// Sélectionner la base de données
$conn->select_db($dbname);
