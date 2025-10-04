<?php
session_start();
header('Content-Type: application/json');

require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'enseignant') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$group_id = $input['group_id'] ?? null;
$student_ids = $input['student_ids'] ?? []; // Tableau d'IDs d'élèves
$matiere_id = $input['matiere_id'] ?? null; // Ajout de matiere_id pour la validation

if (empty($student_ids) || $matiere_id === null) {
    echo json_encode(['success' => false, 'message' => 'Les IDs des élèves et l\'ID de la matière sont requis.']);
    exit();
}

$enseignant_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Vérifier si l'enseignant est bien le propriétaire du groupe (si un groupe est spécifié)
    // Ou si l'enseignant est bien associé à la matière pour désaffecter des élèves
    if ($group_id !== null) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM groupes WHERE id = :group_id AND enseignant_id = :enseignant_id AND matiere_id = :matiere_id");
        $stmt->execute(['group_id' => $group_id, 'enseignant_id' => $enseignant_id, 'matiere_id' => $matiere_id]);
        if ($stmt->fetchColumn() == 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à modifier ce groupe ou cette matière.']);
            exit();
        }
    } else {
        // Si group_id est null (désaffectation), vérifier que l'enseignant est bien associé à la matière
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM annonces WHERE enseignant_id = :enseignant_id AND matiere_id = :matiere_id");
        $stmt->execute(['enseignant_id' => $enseignant_id, 'matiere_id' => $matiere_id]);
        if ($stmt->fetchColumn() == 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à modifier les élèves pour cette matière.']);
            exit();
        }
    }


    // Mettre à jour la colonne groupe_id dans la table inscription
    // Pour chaque élève, trouver son inscription pour la matière donnée et mettre à jour son groupe_id
    $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    $sql = "UPDATE inscription i
            JOIN annonce a ON i.annonce_id = a.id
            SET i.groupe_id = :group_id
            WHERE i.eleve_id IN ($placeholders)
            AND a.matiere_id = :matiere_id
            AND a.enseignant_id = :enseignant_id"; // S'assurer que l'enseignant est bien le propriétaire de l'annonce

    $stmt = $pdo->prepare($sql);

    $params = [':group_id' => $group_id, ':matiere_id' => $matiere_id, ':enseignant_id' => $enseignant_id];
    foreach ($student_ids as $index => $id) {
        $params[$index + 1] = $id; // Les paramètres positionnels commencent à 1
    }

    // PDO ne supporte pas le mélange de paramètres nommés et positionnels de cette manière avec execute
    // Il faut soit tout nommer, soit tout positionner.
    // Simplifions en utilisant uniquement des paramètres nommés pour les IDs d'élèves aussi.
    $sql = "UPDATE inscription i
            JOIN annonce a ON i.annonce_id = a.id
            SET i.groupe_id = :group_id
            WHERE i.eleve_id IN (" . implode(',', array_map(function($k) { return ":eleve_id_$k"; }, array_keys($student_ids))) . ")
            AND a.matiere_id = :matiere_id
            AND a.enseignant_id = :enseignant_id";

    $stmt = $pdo->prepare($sql);

    $params = [
        ':group_id' => $group_id,
        ':matiere_id' => $matiere_id,
        ':enseignant_id' => $enseignant_id
    ];
    foreach ($student_ids as $index => $id) {
        $params[":eleve_id_$index"] = $id;
    }

    $stmt->execute($params);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Élèves affectés au groupe avec succès.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Erreur d'affectation d'élèves au groupe: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'affectation des élèves au groupe.', 'error_details' => $e->getMessage()]);
}
?>
