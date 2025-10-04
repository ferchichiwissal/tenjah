<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role']; // 'student' ou 'teacher'

    // Validation simple côté serveur
    if (empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        die("Tous les champs sont requis.");
    }

    if ($password !== $confirm_password) {
        die("Les mots de passe ne correspondent pas.");
    }

    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM utilisateur WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Cette adresse email est déjà utilisée.");
    }
    $stmt->close();

    // Hacher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insérer l'utilisateur dans la table 'utilisateur'
    $stmt = $conn->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $firstname, $lastname, $email, $hashed_password);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Récupérer l'ID du rôle
        $role_id = null;
        $stmt_role = $conn->prepare("SELECT id FROM role WHERE shortname = ?");
        $stmt_role->bind_param("s", $role);
        $stmt_role->execute();
        $stmt_role->bind_result($role_id);
        $stmt_role->fetch();
        $stmt_role->close();

        if ($role_id) {
            // Assigner le rôle à l'utilisateur dans la table 'role_assignment'
            $stmt_assign_role = $conn->prepare("INSERT INTO role_assignment (user_id, role_id) VALUES (?, ?)");
            $stmt_assign_role->bind_param("ii", $user_id, $role_id);
            $stmt_assign_role->execute();
            $stmt_assign_role->close();

            // Si l'utilisateur est un enseignant, créer un enregistrement dans la table 'enseignant'
            if ($role === 'teacher') {
                $stmt_teacher = $conn->prepare("INSERT INTO enseignant (user_id) VALUES (?)");
                $stmt_teacher->bind_param("i", $user_id);
                $stmt_teacher->execute();
                $stmt_teacher->close();
            } elseif ($role === 'student') { // Ajouter la logique pour les élèves
                $stmt_student = $conn->prepare("INSERT INTO eleve (user_id) VALUES (?)");
                $stmt_student->bind_param("i", $user_id);
                $stmt_student->execute();
                $stmt_student->close();
            }

            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            // Utiliser 'role_shortname' pour la session pour être cohérent avec inscrire_eleve.php
            $_SESSION['role_shortname'] = $role; 
            header("Location: ../frontend/dashboard.html"); // Rediriger vers le tableau de bord après inscription
            exit();
        } else {
            die("Rôle invalide spécifié.");
        }
    } else {
        echo "Erreur lors de l'inscription : " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
