<?php
// Démarrer la session si ce n'est pas déjà fait
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si un utilisateur est connecté.
 * @return bool Vrai si l'utilisateur est connecté, faux sinon.
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Récupère l'ID de l'utilisateur connecté.
 * @return int|null L'ID de l'utilisateur ou null si non connecté.
 */
function get_current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Vérifie si l'utilisateur connecté a un rôle spécifique.
 * @param mysqli $conn La connexion à la base de données.
 * @param string $role_shortname Le nom court du rôle à vérifier (ex: 'teacher', 'student').
 * @return bool Vrai si l'utilisateur a le rôle, faux sinon.
 */
function has_role(mysqli $conn, string $role_shortname): bool {
    if (!is_logged_in()) {
        return false;
    }

    $user_id = get_current_user_id();

    $stmt = $conn->prepare("
        SELECT COUNT(ra.id)
        FROM role_assignment ra
        JOIN role r ON ra.role_id = r.id
        WHERE ra.user_id = ? AND r.shortname = ?
    ");
    $stmt->bind_param("is", $user_id, $role_shortname);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
}

/**
 * Vérifie si l'utilisateur connecté est un enseignant.
 * @param mysqli $conn La connexion à la base de données.
 * @return bool Vrai si l'utilisateur est un enseignant, faux sinon.
 */
function is_teacher(mysqli $conn): bool {
    return has_role($conn, 'teacher');
}

/**
 * Vérifie si l'utilisateur connecté est un élève.
 * @param mysqli $conn La connexion à la base de données.
 * @return bool Vrai si l'utilisateur est un élève, faux sinon.
 */
function is_student(mysqli $conn): bool {
    return has_role($conn, 'student');
}

/**
 * Redirige l'utilisateur vers la page de connexion s'il n'est pas connecté.
 * @param string $redirect_url L'URL vers laquelle rediriger après la connexion (optionnel).
 */
function redirect_if_not_logged_in(string $redirect_url = 'frontend/login.html'): void {
    if (!is_logged_in()) {
        // Stocker l'URL actuelle pour rediriger l'utilisateur après la connexion
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Redirige l'utilisateur s'il n'a pas le rôle requis.
 * @param mysqli $conn La connexion à la base de données.
 * @param string $role_shortname Le nom court du rôle requis.
 * @param string $redirect_url L'URL vers laquelle rediriger si le rôle n'est pas présent.
 */
function redirect_if_not_role(mysqli $conn, string $role_shortname, string $redirect_url = 'frontend/dashboard.html'): void {
    if (!has_role($conn, $role_shortname)) {
        header("Location: $redirect_url");
        exit();
    }
}

?>
