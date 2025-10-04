<?php
session_start();
header('Content-Type: application/json');

require_once 'db_connect.php';

$response = ['loggedIn' => false];

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Récupérer les informations de l'utilisateur et son rôle
    $stmt = $conn->prepare("
        SELECT u.nom, u.prenom, r.shortname as role
        FROM utilisateur u
        JOIN role_assignment ra ON u.id = ra.user_id
        JOIN role r ON ra.role_id = r.id
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Mettre à jour la session avec le rôle correct
        $_SESSION['role_shortname'] = $user['role']; // Stocker le rôle sous 'role_shortname'

        $response = [
            'loggedIn' => true,
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'role' => $user['role'] // Conserver 'role' pour la réponse JSON du frontend
        ];
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
