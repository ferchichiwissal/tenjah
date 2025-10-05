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
    ob_clean(); // Nettoyer le tampon de sortie avant d'envoyer le JSON
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id']; // C'est l'ID de l'utilisateur, pas directement l'ID de l'élève

    // 1. Récupérer l'ID de l'élève (eleve_id) à partir de l'ID de l'utilisateur (user_id)
    $stmt_eleve = $conn->prepare("SELECT id FROM eleve WHERE user_id = ?");
    if ($stmt_eleve === false) {
        throw new Exception("Erreur de préparation de la requête eleve: " . $conn->error);
    }
    $stmt_eleve->bind_param('i', $user_id);
    $stmt_eleve->execute();
    $result_eleve = $stmt_eleve->get_result();
    $eleve_data = $result_eleve->fetch_assoc();
    $stmt_eleve->close();

    if (!$eleve_data) {
        $response['message'] = 'Aucun profil élève trouvé pour cet utilisateur.';
        ob_clean();
        echo json_encode($response);
        exit();
    }

    $eleve_id = $eleve_data['id'];

try {
    // 2. Préparer la requête principale pour récupérer les sessions de l'élève
    $stmt = $conn->prepare("
        SELECT
            s.id AS session_id,
            s.date_seance AS date_heure_debut,
            g.id AS id_groupe,
            g.nom AS nom_groupe,
            m.id AS id_matiere,
            m.nom AS nom_matiere
        FROM
            seance s
        JOIN
            groupes g ON s.groupe_id = g.id
        JOIN
            matiere m ON g.matiere_id = m.id
        JOIN
            inscription i ON g.id = i.groupe_id
        WHERE
            i.eleve_id = ?
        ORDER BY
            s.date_seance ASC
    ");

    if ($stmt === false) {
        throw new Exception("Erreur de préparation de la requête: " . $conn->error);
    }

    // Lier le paramètre avec l'eleve_id récupéré
    $stmt->bind_param('i', $eleve_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sessions = $result->fetch_all(MYSQLI_ASSOC);

    // Ajouter le lien Meet fixe à chaque session
    foreach ($sessions as &$session) {
        $session['lien_meet'] = 'https://meet.google.com/qjm-wrkk-cbj'; // Lien Google Meet fixe
    }
    unset($session); // Rompre la référence du dernier élément

    $response['success'] = true;
    $response['sessions'] = $sessions;

    $stmt->close();

} catch (Exception $e) {
    $response['message'] = 'Erreur de base de données: ' . $e->getMessage();
    $response['error'] = $e->getMessage();
}

ob_clean(); // Nettoyer le tampon de sortie avant d'envoyer le JSON
echo json_encode($response);
