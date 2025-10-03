<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $bio = $_POST['bio'];
    $specialite = $_POST['specialite'];

    // Hacher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Commencer une transaction
    $conn->begin_transaction();

    try {
        // 1. Insérer l'utilisateur dans la table 'utilisateur'
        $stmt_user = $conn->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
        $stmt_user->bind_param("ssss", $nom, $prenom, $email, $hashed_password);
        $stmt_user->execute();
        $user_id = $stmt_user->insert_id;
        $stmt_user->close();

        // 2. Assigner le rôle 'teacher' à cet utilisateur
        // Récupérer l'ID du rôle 'teacher'
        $stmt_role = $conn->prepare("SELECT id FROM role WHERE shortname = 'teacher'");
        $stmt_role->execute();
        $stmt_role->bind_result($role_id);
        $stmt_role->fetch();
        $stmt_role->close();

        if (!$role_id) {
            throw new Exception("Le rôle 'teacher' n'existe pas.");
        }

        // Insérer dans la table 'role_assignment'
        $stmt_role_assign = $conn->prepare("INSERT INTO role_assignment (user_id, role_id) VALUES (?, ?)");
        $stmt_role_assign->bind_param("ii", $user_id, $role_id);
        $stmt_role_assign->execute();
        $stmt_role_assign->close();

        // 3. Insérer les informations spécifiques à l'enseignant dans la table 'enseignant'
        $stmt_teacher = $conn->prepare("INSERT INTO enseignant (user_id, bio, specialite) VALUES (?, ?, ?)");
        $stmt_teacher->bind_param("iss", $user_id, $bio, $specialite);
        $stmt_teacher->execute();
        $stmt_teacher->close();

        // Si tout est bon, valider la transaction
        $conn->commit();

        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'teacher'; // Ajouter le rôle à la session
        header("Location: ../frontend/dashboard.html"); // Rediriger vers le tableau de bord ou une page spécifique enseignant
        exit();

    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $conn->rollback();
        echo "Erreur lors de l'inscription de l'enseignant : " . $e->getMessage();
    }

    $conn->close();
}
?>
