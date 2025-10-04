<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$response = ['success' => false, 'message' => ''];

try {
    $stmt = $conn->prepare("SELECT id, nom FROM matiere ORDER BY nom ASC");
    if (!$stmt) {
        throw new Exception("Erreur de préparation de la requête: " . $conn->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $matieres = [];
    while ($row = $result->fetch_assoc()) {
        $matieres[] = $row;
    }
    $stmt->close();

    $response['success'] = true;
    $response['matieres'] = $matieres;

} catch (Exception $e) {
    $response['message'] = 'Erreur de base de données: ' . $e->getMessage();
}

echo json_encode($response);
?>
