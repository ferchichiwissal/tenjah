<?php
session_start();
header('Content-Type: application/json');

require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_shortname'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$group_name = $input['nom'] ?? ''; // Changed from group_name to nom
$matiere_id = $input['matiere_id'] ?? '';
$students = $input['students'] ?? []; // New: Get selected students

if (empty($group_name) || empty($matiere_id)) {
    echo json_encode(['success' => false, 'message' => 'Le nom du groupe et l\'ID de la matière sont requis.']);
    exit();
}

$enseignant_user_id = $_SESSION['user_id'];

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

    if (!$enseignant_id) {
        echo json_encode(['success' => false, 'message' => 'Enseignant non trouvé.']);
        exit();
    }

    // Vérifier si l'enseignant est bien associé à cette matière via une annonce
    $stmt = $conn->prepare("SELECT COUNT(*) FROM annonce WHERE enseignant_id = ? AND matiere_id = ?");
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête annonce: " . $conn->error);
    }
    $stmt->bind_param("ii", $enseignant_id, $matiere_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $annonce_count = $result->fetch_assoc()['COUNT(*)'];
    $stmt->close();

    if ($annonce_count == 0) {
        echo json_encode(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à créer un groupe pour cette matière.']);
        exit();
    }

    // Début de la transaction
    $conn->begin_transaction();

    // Insérer le nouveau groupe
    $stmt = $conn->prepare("INSERT INTO groupes (nom, matiere_id, enseignant_id) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête INSERT groupes: " . $conn->error);
    }
    $stmt->bind_param("sii", $group_name, $matiere_id, $enseignant_id);
    $stmt->execute();
    $new_group_id = $conn->insert_id; // Get the ID of the newly created group
    $stmt->close();

    // Assign selected students to the new group
    if (!empty($students)) {
        $stmt_assign = $conn->prepare("UPDATE inscription SET groupe_id = ? WHERE id = ?");
        if (!$stmt_assign) {
            throw new Exception("Erreur de préparation de la requête UPDATE inscription: " . $conn->error);
        }
        foreach ($students as $student) {
            $stmt_assign->bind_param("ii", $new_group_id, $student['inscription_id']);
            $stmt_assign->execute();
        }
        $stmt_assign->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Groupe créé et élèves affectés avec succès.']);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Erreur de création de groupe ou d'affectation d'élèves: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du groupe ou de l\'affectation des élèves.', 'error_details' => $e->getMessage()]);
} finally {
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}
?>
