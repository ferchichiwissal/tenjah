<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifier si l'utilisateur est connecté et est un élève
    if (!isset($_SESSION['user_id']) || $_SESSION['role_shortname'] !== 'student') {
        echo json_encode(['success' => false, 'message' => 'Accès non autorisé. Seuls les élèves connectés peuvent s\'inscrire.']);
        exit();
    }

    $eleve_id = $_SESSION['user_id'];
    $annonce_id = filter_input(INPUT_POST, 'annonce_id', FILTER_VALIDATE_INT);

    if (!$annonce_id) {
        echo json_encode(['success' => false, 'message' => 'ID de l\'annonce manquant ou invalide.']);
        exit();
    }

    // Vérifier si l'élève est déjà inscrit à cette annonce
    $stmt = $conn->prepare("SELECT COUNT(*) FROM inscription WHERE eleve_id = ? AND annonce_id = ?");
    $stmt->bind_param("ii", $eleve_id, $annonce_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo json_encode(['success' => false, 'message' => 'Vous êtes déjà inscrit à cette annonce.']);
        exit();
    }

    // Insérer l'inscription
    $stmt = $conn->prepare("INSERT INTO inscription (eleve_id, annonce_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $eleve_id, $annonce_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Inscription réussie!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode de requête non autorisée.']);
}
?>
