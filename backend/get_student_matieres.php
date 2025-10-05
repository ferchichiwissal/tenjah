<?php
ob_start(); // Démarre la mise en tampon de sortie
error_reporting(E_ALL);
ini_set('display_errors', 0); // Désactiver l'affichage des erreurs pour éviter les sorties inattendues

session_start();
header('Content-Type: application/json');

require_once 'db_connect.php'; // Ce fichier fournit $conn (mysqli)

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    $response['message'] = 'Accès non autorisé.';
    echo json_encode($response);
    exit();
}

$student_id = $_SESSION['user_id'];

try {
    // Récupérer les matières auxquelles l'élève est inscrit
    $stmt = $conn->prepare("
        SELECT DISTINCT
            m.id AS matiere_id,
            m.nom AS matiere_nom
        FROM
            matiere m
        JOIN
            groupes g ON m.id = g.matiere_id
        JOIN
            inscription i ON g.id = i.groupe_id
        WHERE
            i.eleve_id = ?
        ORDER BY
            m.nom ASC
    ");

    if ($stmt === false) {
        throw new Exception("Erreur de préparation de la requête: " . $conn->error);
    }

    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $matieres = $result->fetch_all(MYSQLI_ASSOC);

    $response['success'] = true;
    $response['matieres'] = $matieres;

    $stmt->close();

} catch (Exception $e) {
    $response['message'] = 'Erreur de base de données: ' . $e->getMessage();
    $response['error'] = $e->getMessage();
}

ob_clean(); // Nettoyer le tampon de sortie avant d'envoyer le JSON
echo json_encode($response);
