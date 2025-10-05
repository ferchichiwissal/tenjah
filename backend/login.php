<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Préparer et exécuter la requête SQL pour récupérer l'utilisateur et son rôle
    $stmt = $conn->prepare("
        SELECT 
            u.id, 
            u.nom, 
            u.prenom, 
            u.mot_de_passe,
            r.shortname AS role_shortname
        FROM 
            utilisateur u
        JOIN 
            role_assignment ra ON u.id = ra.user_id
        JOIN 
            role r ON ra.role_id = r.id
        WHERE 
            u.email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $nom, $prenom, $hashed_password, $role_shortname);
        $stmt->fetch();

        // Vérifier le mot de passe
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
            $_SESSION['role'] = $role_shortname; // Stocker le rôle dans la session
            header("Location: ../frontend/index.php"); // Rediriger vers la page d'accueil après connexion
            exit();
        } else {
            $_SESSION['error_message'] = "Mot de passe incorrect.";
            header("Location: ../frontend/login.html");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Aucun compte trouvé avec cette adresse email ou rôle non assigné.";
        header("Location: ../frontend/login.html");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
