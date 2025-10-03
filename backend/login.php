<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Préparer et exécuter la requête SQL pour récupérer l'utilisateur
    $stmt = $conn->prepare("SELECT id, nom, prenom, mot_de_passe FROM utilisateur WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $nom, $prenom, $hashed_password);
        $stmt->fetch();

        // Vérifier le mot de passe
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
            header("Location: ../frontend/index.php"); // Rediriger vers la page d'accueil après connexion
            exit();
        } else {
            $_SESSION['error_message'] = "Mot de passe incorrect.";
            header("Location: ../frontend/login.html");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Aucun compte trouvé avec cette adresse email.";
        header("Location: ../frontend/login.html");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
