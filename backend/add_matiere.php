<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'auth.php'; // Assurez-vous que ce fichier gère l'authentification et les rôles

$response = ['success' => false, 'message' => ''];

// Vérifier si l'utilisateur est connecté et est un enseignant
if (!is_logged_in() || !is_teacher($conn)) {
    $response['message'] = "Accès non autorisé. Seuls les enseignants peuvent ajouter des matières.";
    echo json_encode($response);
    $conn->close();
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['nom_matiere']) || empty(trim($data['nom_matiere']))) {
    $response['message'] = "Le nom de la matière est requis.";
    echo json_encode($response);
    $conn->close();
    exit();
}

$nom_matiere = trim($data['nom_matiere']);

try {
    // Vérifier si la matière existe déjà
    $stmt = $conn->prepare("SELECT id FROM matiere WHERE nom = ?");
    $stmt->bind_param("s", $nom_matiere);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response['message'] = "La matière '$nom_matiere' existe déjà.";
    } else {
        // Insérer la nouvelle matière
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO matiere (nom) VALUES (?)");
        $stmt->bind_param("s", $nom_matiere);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Matière '$nom_matiere' ajoutée avec succès.";
            $response['matiere_id'] = $conn->insert_id;
        } else {
            $response['message'] = "Erreur lors de l'ajout de la matière : " . $stmt->error;
        }
    }

} catch (Exception $e) {
    $response['message'] = "Erreur : " . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
