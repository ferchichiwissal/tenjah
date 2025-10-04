<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Désactiver l'affichage des erreurs pour éviter la sortie HTML
session_start();
header('Content-Type: application/json');

// Démarrer la mise en mémoire tampon de sortie
ob_start();

require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_shortname'] !== 'teacher') { // Utiliser role_shortname comme dans get_inscriptions_enseignant.php
    ob_end_clean(); // Nettoyer le tampon avant d'envoyer la réponse JSON
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit();
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Nettoyer le tampon de sortie en cas d'erreur
    ob_end_clean();
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

error_log("DEBUG: Début de get_groups_and_unassigned_students.php");

$matiere_id = $_GET['matiere_id'] ?? null;
error_log("DEBUG: matiere_id reçu: " . ($matiere_id ?? 'null'));

if ($matiere_id === null) {
    error_log("ERREUR: L'ID de la matière est requis.");
    ob_end_clean(); // Nettoyer le tampon avant d'envoyer la réponse JSON
    echo json_encode(['success' => false, 'message' => 'L\'ID de la matière est requis.']);
    exit();
}

$enseignant_user_id = $_SESSION['user_id'];
error_log("DEBUG: enseignant_user_id de session: " . $enseignant_user_id);

try {
    // Récupérer l'ID de l'enseignant à partir de son user_id
    $stmt = $conn->prepare("SELECT id FROM enseignant WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête enseignant: " . $conn->error);
    }
    $stmt->bind_param("i", $enseignant_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $enseignant_id = $result->fetch_assoc()['id'] ?? null;
    $stmt->close();
    error_log("DEBUG: enseignant_id trouvé: " . ($enseignant_id ?? 'null'));

    if (!$enseignant_id) {
        error_log("ERREUR: Enseignant non trouvé pour user_id: " . $enseignant_user_id);
        ob_end_clean(); // Nettoyer le tampon avant d'envoyer la réponse JSON
        echo json_encode(['success' => false, 'message' => 'Enseignant non trouvé.']);
        exit();
    }

    // Vérifier si l'enseignant est bien associé à cette matière via une annonce
    $stmt = $conn->prepare("SELECT COUNT(*) FROM annonce WHERE enseignant_id = ? AND matiere_id = ?");
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête annonces: " . $conn->error);
    }
    $stmt->bind_param("ii", $enseignant_id, $matiere_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $annonce_count = $result->fetch_assoc()['COUNT(*)'];
    $stmt->close();
    error_log("DEBUG: Nombre d'annonces pour enseignant_id {$enseignant_id} et matiere_id {$matiere_id}: " . $annonce_count);

    if ($annonce_count == 0) {
        error_log("ERREUR: Enseignant non autorisé à gérer les groupes pour cette matière.");
        ob_end_clean(); // Nettoyer le tampon avant d'envoyer la réponse JSON
        echo json_encode(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à gérer les groupes pour cette matière.']);
        exit();
    }

    // 1. Récupérer tous les élèves inscrits à cette matière pour cet enseignant
    $stmt_all_students = $conn->prepare("
        SELECT
            el.id AS eleve_id,
            u.nom AS eleve_nom,
            u.prenom AS eleve_prenom,
            u.email AS eleve_email,
            i.id AS inscription_id,
            i.groupe_id,
            a.id AS annonce_id
        FROM inscription i
        JOIN eleve el ON i.eleve_id = el.id
        JOIN utilisateur u ON el.user_id = u.id
        JOIN annonce a ON i.annonce_id = a.id
        WHERE a.matiere_id = ?
        AND a.enseignant_id = ?
        ORDER BY u.nom, u.prenom
    ");
    if (!$stmt_all_students) {
        throw new Exception("Erreur de préparation de la requête all_students: " . $conn->error);
    }
    $stmt_all_students->bind_param("ii", $matiere_id, $enseignant_id);
    $stmt_all_students->execute();
    $all_students_result = $stmt_all_students->get_result();
    $all_students = $all_students_result->fetch_all(MYSQLI_ASSOC);
    $stmt_all_students->close();
    error_log("DEBUG: Nombre d'élèves inscrits récupérés: " . count($all_students));
    error_log("DEBUG: Détails des élèves inscrits: " . json_encode($all_students));


    // 2. Récupérer tous les groupes créés par cet enseignant pour cette matière
    $stmt_groups = $conn->prepare("
        SELECT
            id AS groupe_id,
            nom AS groupe_nom
        FROM groupes
        WHERE matiere_id = ?
        AND enseignant_id = ?
        ORDER BY nom
    ");
    if (!$stmt_groups) {
        throw new Exception("Erreur de préparation de la requête groupes: " . $conn->error);
    }
    $stmt_groups->bind_param("ii", $matiere_id, $enseignant_id);
    $stmt_groups->execute();
    $groups_result = $stmt_groups->get_result();
    $groups_data = $groups_result->fetch_all(MYSQLI_ASSOC);
    $stmt_groups->close();
    error_log("DEBUG: Nombre de groupes récupérés: " . count($groups_data));
    error_log("DEBUG: Détails des groupes: " . json_encode($groups_data));

    $formatted_groups = [];
    foreach ($groups_data as $group) {
        $formatted_groups[$group['groupe_id']] = [
            'id' => $group['groupe_id'],
            'nom' => $group['groupe_nom'],
            'students' => []
        ];
    }
    error_log("DEBUG: Groupes formatés (avant affectation des élèves): " . json_encode($formatted_groups));

    $unassigned_students = [];

    // 3. Affecter les élèves aux groupes ou à la liste des non affectés
    foreach ($all_students as $student) {
        if ($student['groupe_id'] !== null && isset($formatted_groups[$student['groupe_id']])) {
            $formatted_groups[$student['groupe_id']]['students'][] = [
                'eleve_id' => $student['eleve_id'],
                'eleve_nom' => $student['eleve_nom'],
                'eleve_prenom' => $student['eleve_prenom'],
                'eleve_email' => $student['eleve_email'],
                'inscription_id' => $student['inscription_id'],
                'annonce_id' => $student['annonce_id']
            ];
        } else {
            $unassigned_students[] = [
                'eleve_id' => $student['eleve_id'],
                'eleve_nom' => $student['eleve_nom'],
                'eleve_prenom' => $student['eleve_prenom'],
                'eleve_email' => $student['eleve_email'],
                'inscription_id' => $student['inscription_id'],
                'annonce_id' => $student['annonce_id']
            ];
        }
    }
    error_log("DEBUG: Élèves non affectés: " . json_encode($unassigned_students));
    error_log("DEBUG: Groupes formatés (après affectation des élèves): " . json_encode($formatted_groups));


    echo json_encode([
        'success' => true,
        'groups' => array_values($formatted_groups),
        'unassigned_students' => $unassigned_students
    ]);
    error_log("DEBUG: Fin de get_groups_and_unassigned_students.php - Réponse JSON envoyée.");

} catch (Exception $e) {
    error_log("ERREUR CATCHED dans get_groups_and_unassigned_students.php: " . $e->getMessage() . " Trace: " . $e->getTraceAsString());
    ob_end_clean(); // Nettoyer le tampon avant d'envoyer la réponse JSON
    echo json_encode([
        'success' => false,
        'message' => 'Erreur inattendue lors de la récupération des groupes et des élèves.',
        'error_details' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} finally {
    restore_error_handler();
    if (isset($conn) && $conn->ping()) { // Utiliser ping() pour vérifier si la connexion est toujours active
        $conn->close();
        error_log("DEBUG: Connexion à la base de données fermée.");
    }
}
?>
