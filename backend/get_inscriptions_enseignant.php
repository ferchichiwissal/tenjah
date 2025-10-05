<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id']) || $_SESSION['role_shortname'] !== 'teacher') {
        echo json_encode(['success' => false, 'message' => 'Accès non autorisé. Seuls les enseignants connectés peuvent voir les inscriptions.']);
        exit();
    }

    $enseignant_user_id = $_SESSION['user_id'];

    // Récupérer l'ID de l'enseignant à partir de son user_id
    $stmt = $conn->prepare("SELECT id FROM enseignant WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête enseignant: " . $conn->error);
    }
    $stmt->bind_param("i", $enseignant_user_id);
    $stmt->execute();
    $stmt->bind_result($enseignant_id);
    $stmt->fetch();
    $stmt->close();

    if (!$enseignant_id) {
        echo json_encode(['success' => false, 'message' => 'Enseignant non trouvé.']);
        exit();
    }

    // Récupérer les inscriptions pour les annonces de cet enseignant
    $sql = "SELECT 
                m.id AS matiere_id,
                m.nom AS matiere_nom,
                el.id AS eleve_id,
                u.nom AS eleve_nom,
                u.prenom AS eleve_prenom,
                u.email AS eleve_email,
                i.id AS inscription_id,
                i.groupe_id,
                a.titre AS annonce_titre,
                a.niveau AS annonce_niveau
            FROM inscription i
            JOIN eleve el ON i.eleve_id = el.id
            JOIN utilisateur u ON el.user_id = u.id
            JOIN annonce a ON i.annonce_id = a.id
            JOIN matiere m ON a.matiere_id = m.id
            WHERE a.enseignant_id = ?
            ORDER BY m.nom, u.nom";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête inscriptions: " . $conn->error);
    }
    $stmt->bind_param("i", $enseignant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $matieres_data = [];
    while ($row = $result->fetch_assoc()) {
        $matiere_id = $row['matiere_id'];
        $matiere_nom = $row['matiere_nom'];

        if (!isset($matieres_data[$matiere_id])) {
            $matieres_data[$matiere_id] = [
                'matiere_id' => $matiere_id,
                'matiere_nom' => $matiere_nom,
                'students' => [],
                'groups' => [],
                'unassigned_students' => []
            ];
        }

        $student = [
            'eleve_id' => $row['eleve_id'],
            'eleve_nom' => $row['eleve_nom'],
            'eleve_prenom' => $row['eleve_prenom'],
            'eleve_email' => $row['eleve_email'],
            'inscription_id' => $row['inscription_id'],
            'groupe_id' => $row['groupe_id'],
            'annonce_titre' => $row['annonce_titre'],
            'annonce_niveau' => $row['annonce_niveau']
        ];
        $matieres_data[$matiere_id]['students'][] = $student;
    }
    $stmt->close();

    // Récupérer les groupes pour chaque matière
    foreach ($matieres_data as $matiere_id => &$data) {
        $stmt_groups = $conn->prepare("SELECT id, nom FROM groupes WHERE matiere_id = ? AND enseignant_id = ?");
        if (!$stmt_groups) {
            throw new Exception("Erreur de préparation de la requête groupes: " . $conn->error);
        }
        $stmt_groups->bind_param("ii", $matiere_id, $enseignant_id);
        $stmt_groups->execute();
        $result_groups = $stmt_groups->get_result();

        while ($group_row = $result_groups->fetch_assoc()) {
            $group_id = $group_row['id'];
            $data['groups'][$group_id] = [
                'id' => $group_id,
                'nom' => $group_row['nom'],
                'students' => []
            ];
        }
        $stmt_groups->close();

        // Affecter les élèves aux groupes ou aux non affectés
        foreach ($data['students'] as $student) {
            if ($student['groupe_id'] !== null && isset($data['groups'][$student['groupe_id']])) {
                $data['groups'][$student['groupe_id']]['students'][] = $student;
            } else {
                $data['unassigned_students'][] = $student;
            }
        }
        // Convertir les groupes de tableau associatif en tableau indexé pour le JSON
        $data['groups'] = array_values($data['groups']);
    }
    unset($data); // Rompre la référence de la dernière itération

    echo json_encode(['success' => true, 'matieres' => array_values($matieres_data)]);

} catch (Exception $e) {
    error_log("Erreur dans get_inscriptions_enseignant.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Une erreur est survenue lors du traitement de votre demande.', 'error_details' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
} finally {
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}
?>
