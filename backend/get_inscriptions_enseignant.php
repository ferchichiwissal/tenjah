<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role_shortname'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé. Seuls les enseignants connectés peuvent voir les inscriptions.']);
    exit();
}

$enseignant_user_id = $_SESSION['user_id'];

// Récupérer l'ID de l'enseignant à partir de son user_id
$stmt = $conn->prepare("SELECT id FROM enseignant WHERE user_id = ?");
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
            m.nom AS matiere_nom,
            a.titre AS annonce_titre,
            u.nom AS eleve_nom,
            u.prenom AS eleve_prenom,
            u.email AS eleve_email,
            i.date_inscription
        FROM inscription i
        JOIN eleve el ON i.eleve_id = el.id
        JOIN utilisateur u ON el.user_id = u.id
        JOIN annonce a ON i.annonce_id = a.id
        JOIN matiere m ON a.matiere_id = m.id
        WHERE a.enseignant_id = ?
        ORDER BY m.nom, a.titre, u.nom";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $enseignant_id);
$stmt->execute();
$result = $stmt->get_result();

$inscriptions = [];
while ($row = $result->fetch_assoc()) {
    $matiere_nom = $row['matiere_nom'];
    if (!isset($inscriptions[$matiere_nom])) {
        $inscriptions[$matiere_nom] = [];
    }
    $inscriptions[$matiere_nom][] = [
        'annonce_titre' => $row['annonce_titre'],
        'eleve_nom' => $row['eleve_nom'],
        'eleve_prenom' => $row['eleve_prenom'],
        'eleve_email' => $row['eleve_email'],
        'date_inscription' => $row['date_inscription']
    ];
}

echo json_encode(['success' => true, 'inscriptions' => $inscriptions]);

$stmt->close();
$conn->close();
?>
