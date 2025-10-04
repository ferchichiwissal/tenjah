<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'auth.php'; // Pour vérifier le rôle de l'utilisateur

$response = ['success' => false, 'message' => ''];

// Vérifier si l'utilisateur est connecté et est un enseignant
if (!is_logged_in() || !is_teacher($conn)) {
    $response['message'] = 'Accès non autorisé. Seuls les enseignants peuvent créer des annonces.';
    echo json_encode($response);
    exit();
}

$teacher_id = get_current_user_id(); // Récupérer l'ID de l'enseignant connecté

$input = json_decode(file_get_contents('php://input'), true);

$matiere_id = $input['matiere_id'] ?? null;
$new_matiere_name = $input['new_matiere_name'] ?? null;
$titre = $input['titre'] ?? null;
$description = $input['description'] ?? null;
$niveau = $input['niveau'] ?? null;
$prix_unitaire = $input['prix_unitaire'] ?? null;

// Validation des entrées
if (empty($titre) || empty($description) || empty($niveau) || empty($prix_unitaire)) {
    $response['message'] = 'Tous les champs obligatoires (titre, description, niveau, prix unitaire) doivent être remplis.';
    echo json_encode($response);
    exit();
}

if (!is_numeric($prix_unitaire) || $prix_unitaire < 0) {
    $response['message'] = 'Le prix unitaire doit être un nombre positif.';
    echo json_encode($response);
    exit();
}

// Utiliser la connexion MySQLi
// Pas de transaction directe avec MySQLi sans gestion manuelle complexe,
// mais nous pouvons simuler un comportement transactionnel simple pour les insertions.

// Gérer la matière
if ($new_matiere_name) {
    // Vérifier si la nouvelle matière existe déjà
    $stmt = $conn->prepare("SELECT id FROM matiere WHERE nom = ?");
    $stmt->bind_param("s", $new_matiere_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_matiere = $result->fetch_assoc();
    $stmt->close();

    if ($existing_matiere) {
        $matiere_id = $existing_matiere['id'];
    } else {
        // Insérer la nouvelle matière
        $stmt = $conn->prepare("INSERT INTO matiere (nom) VALUES (?)");
        $stmt->bind_param("s", $new_matiere_name);
        if (!$stmt->execute()) {
            $response['message'] = 'Erreur lors de l\'ajout de la nouvelle matière: ' . $stmt->error;
            echo json_encode($response);
            exit();
        }
        $matiere_id = $conn->insert_id;
        $stmt->close();
    }
}

if (!$matiere_id) {
    $response['message'] = 'Veuillez sélectionner une matière ou en ajouter une nouvelle.';
    echo json_encode($response);
    exit();
}

// Créer l'annonce
$stmt = $conn->prepare("INSERT INTO annonce (enseignant_id, matiere_id, titre, description, niveau, prix_unitaire) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    $response['message'] = 'Erreur de préparation de la requête: ' . $conn->error;
    echo json_encode($response);
    exit();
}
$stmt->bind_param("iisssd", $teacher_id, $matiere_id, $titre, $description, $niveau, $prix_unitaire);

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Annonce créée avec succès.';
} else {
    $response['message'] = 'Erreur lors de la création de l\'annonce: ' . $stmt->error;
}
$stmt->close();

echo json_encode($response);
?>
