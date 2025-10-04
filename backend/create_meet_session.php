<?php
ini_set('display_errors', 0); // Désactive l'affichage des erreurs à l'écran
ini_set('log_errors', 1);    // Active l'enregistrement des erreurs dans le fichier log
error_reporting(E_ALL);      // Rapporte toutes les erreurs PHP

header('Content-Type: application/json');
require_once 'db_connect.php'; // Inclut le fichier qui établit la connexion $conn (mysqli)
require_once 'auth.php';

// Assurez-vous que la connexion $conn est disponible globalement
global $conn; 

// Vérifier si l'utilisateur est connecté et est un enseignant
if (!is_logged_in() || !is_teacher($conn)) {
    error_log("Accès non autorisé: is_logged_in=" . (is_logged_in() ? 'true' : 'false') . ", is_teacher=" . (is_teacher($conn) ? 'true' : 'false'));
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit();
}
error_log("Vérification d'accès réussie: l'utilisateur est un enseignant connecté.");

$input = json_decode(file_get_contents('php://input'), true);
error_log("Données reçues pour create_meet_session: " . print_r($input, true));

$group_id = $input['group_id'] ?? null;
$matiere_id = $input['matiere_id'] ?? null;
$start_time = $input['start_time'] ?? null; // Contient la date et l'heure complètes

// Validation des données
if (empty($group_id) || empty($matiere_id) || empty($start_time)) {
    error_log("Validation échouée: group_id=" . $group_id . ", matiere_id=" . $matiere_id . ", start_time=" . $start_time);
    echo json_encode(['success' => false, 'message' => 'Le groupe, la matière et la date/heure de la séance sont requis.']);
    exit();
}
error_log("Validation initiale réussie.");

try {
    // Utiliser la connexion mysqli existante
    $enseignant_id = $_SESSION['user_id'];
    error_log("Enseignant ID: " . $enseignant_id);

    // 1. Vérifier si le groupe appartient bien à l'enseignant connecté et récupérer l'annonce_id
    error_log("Préparation de la requête SELECT groupes...");
    $stmt = $conn->prepare("
        SELECT 
            g.id as group_id,
            g.matiere_id,
            a.id as annonce_id
        FROM 
            groupes g
        JOIN 
            annonce a ON g.matiere_id = a.matiere_id AND g.enseignant_id = a.enseignant_id
        WHERE 
            g.id = ? AND g.enseignant_id = ? AND g.matiere_id = ?
        LIMIT 1
    ");
    if (!$stmt) {
        error_log("Erreur de préparation de la requête (SELECT groupes): " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Erreur interne du serveur.']);
        exit();
    }
    error_log("Requête SELECT groupes préparée. Liaison des paramètres: group_id=" . $group_id . ", enseignant_id=" . $enseignant_id . ", matiere_id=" . $matiere_id);
    $stmt->bind_param("iii", $group_id, $enseignant_id, $matiere_id);
    error_log("Exécution de la requête SELECT groupes...");
    $stmt->execute();
    error_log("Récupération des résultats de SELECT groupes...");
    $result = $stmt->get_result();
    $group_info = $result->fetch_assoc();
    $stmt->close();
    error_log("Résultats de SELECT groupes récupérés. group_info: " . print_r($group_info, true));

    if (!$group_info) {
        error_log("Vérification groupe/matière/enseignant échouée pour group_id=" . $group_id . ", matiere_id=" . $matiere_id . ", enseignant_id=" . $enseignant_id);
        echo json_encode(['success' => false, 'message' => 'Groupe ou matière non valide pour cet enseignant ou annonce associée introuvable.']);
        exit();
    }
    error_log("Vérification groupe/matière/enseignant réussie. Annonce ID: " . $group_info['annonce_id']);

    $annonce_id = $group_info['annonce_id'];

    // 2. Insérer la nouvelle séance dans la table 'seance'
    error_log("Préparation de la requête INSERT seance...");
    $stmt = $conn->prepare("
        INSERT INTO seance (annonce_id, groupe_id, date_seance)
        VALUES (?, ?, ?)
    ");
    if (!$stmt) {
        error_log("Erreur de préparation de la requête (INSERT seance): " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Erreur interne du serveur.']);
        exit();
    }
    error_log("Requête INSERT seance préparée. Liaison des paramètres: annonce_id=" . $annonce_id . ", group_id=" . $group_id . ", start_time=" . $start_time);
    $stmt->bind_param("iis", $annonce_id, $group_id, $start_time);
    error_log("Exécution de la requête INSERT seance...");
    $stmt->execute();
    $stmt->close();
    error_log("Insertion de la séance réussie pour annonce_id=" . $annonce_id . ", groupe_id=" . $group_id . ", date_seance=" . $start_time);

    $meet_link = 'https://meet.google.com/qjm-wrkk-cbj'; // Lien Google Meet fixe

    echo json_encode(['success' => true, 'message' => 'Séance créée avec succès.', 'meet_link' => $meet_link]);

} catch (Exception $e) { // Utiliser Exception pour capturer toutes les erreurs
    error_log("Erreur lors de la création de la séance: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la création de la séance.', 'error_details' => $e->getMessage()]);
}
?>
