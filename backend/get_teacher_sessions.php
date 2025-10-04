<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'auth.php';

global $conn;

if (!is_logged_in() || !is_teacher($conn)) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit();
}

$enseignant_id = get_current_user_id();

try {
    $stmt = $conn->prepare("
        SELECT 
            s.id AS seance_id,
            s.date_seance,
            g.nom AS groupe_nom,
            m.nom AS matiere_nom,
            a.titre AS annonce_titre
        FROM 
            seance s
        JOIN 
            groupes g ON s.groupe_id = g.id
        JOIN 
            matiere m ON g.matiere_id = m.id
        JOIN 
            annonce a ON s.annonce_id = a.id
        WHERE 
            g.enseignant_id = ?
        ORDER BY 
            s.date_seance DESC
    ");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Erreur de préparation de la requête: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $enseignant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sessions = [];

    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }

    $stmt->close();
    echo json_encode(['success' => true, 'sessions' => $sessions]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des séances.', 'error_details' => $e->getMessage()]);
}
?>
