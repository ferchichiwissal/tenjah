<?php
require_once 'db_connect.php';

$schema_sql = file_get_contents('database/schema.sql');

if ($schema_sql === false) {
    die("Erreur: Impossible de lire le fichier schema.sql");
}

// Utiliser la connexion mysqli ($conn) de db_connect.php
if ($conn->multi_query($schema_sql)) {
    do {
        // Stocker le premier résultat pour pouvoir passer au suivant
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "Schéma de la base de données exécuté avec succès.\n";
} else {
    die("Erreur lors de l'exécution du schéma : " . $conn->error);
}

// Insérer les rôles de base si la table 'role' est vide
$check_roles_sql = "SELECT COUNT(*) FROM role";
$result_roles = $conn->query($check_roles_sql);
if ($result_roles && $result_roles->fetch_row()[0] == 0) {
    $insert_roles_sql = "
    INSERT INTO role (shortname, name) VALUES ('student', 'Élève');
    INSERT INTO role (shortname, name) VALUES ('teacher', 'Enseignant');
    INSERT INTO role (shortname, name) VALUES ('admin', 'Administrateur');
    ";
    if ($conn->multi_query($insert_roles_sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        echo "Rôles de base insérés avec succès.\n";
    } else {
        die("Erreur lors de l'insertion des rôles : " . $conn->error);
    }
}

// Fermer la connexion après l'exécution du schéma
$conn->close();
?>
