-- Table pour les utilisateurs
CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table pour les rôles (student, teacher, admin, etc.)
CREATE TABLE role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shortname VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL
);

-- Table d'assignation de rôle (lie un utilisateur à un rôle)
CREATE TABLE role_assignment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    context_id INT, -- Peut être l'ID d'un cours, d'une catégorie, ou 1 pour le site global
    FOREIGN KEY (user_id) REFERENCES utilisateur(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE CASCADE
);

-- Table pour les enseignants (informations spécifiques aux utilisateurs ayant le rôle d'enseignant)
CREATE TABLE enseignant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE, -- Un enseignant est un utilisateur
    bio TEXT,
    specialite VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES utilisateur(id) ON DELETE CASCADE
);

-- Table pour les annonces de cours
CREATE TABLE annonce (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table pour les groupes (liés à une annonce)
CREATE TABLE groupe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    annonce_id INT NOT NULL,
    nom_groupe VARCHAR(255) NOT NULL,
    description TEXT,
    FOREIGN KEY (annonce_id) REFERENCES annonce(id) ON DELETE CASCADE
);

-- Table de liaison entre Utilisateur et Groupe (relation N-N)
CREATE TABLE groupe_eleve (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    groupe_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES utilisateur(id) ON DELETE CASCADE,
    FOREIGN KEY (groupe_id) REFERENCES groupe(id) ON DELETE CASCADE,
    UNIQUE (user_id, groupe_id) -- Un élève ne peut appartenir qu'une fois à un groupe
);

-- Table pour les séances (liées à une annonce et un groupe)
CREATE TABLE seance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    annonce_id INT NOT NULL,
    groupe_id INT NOT NULL,
    date_seance DATETIME NOT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (annonce_id) REFERENCES annonce(id) ON DELETE CASCADE,
    FOREIGN KEY (groupe_id) REFERENCES groupe(id) ON DELETE CASCADE
);

-- Table pour les participations (d'un utilisateur à une séance)
CREATE TABLE participation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    seance_id INT NOT NULL,
    date_participation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateur(id) ON DELETE CASCADE,
    FOREIGN KEY (seance_id) REFERENCES seance(id) ON DELETE CASCADE,
    UNIQUE (user_id, seance_id) -- Un utilisateur ne peut participer qu'une fois à une séance
);

-- Table pour les évaluations (d'un élève pour un enseignant)
CREATE TABLE evaluation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    eleve_id INT NOT NULL, -- L'utilisateur qui rédige l'évaluation (doit avoir le rôle d'élève)
    enseignant_id INT NOT NULL, -- L'enseignant évalué
    note INT CHECK (note >= 1 AND note <= 5), -- Note de 1 à 5
    commentaire TEXT,
    date_evaluation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (eleve_id) REFERENCES utilisateur(id) ON DELETE CASCADE,
    FOREIGN KEY (enseignant_id) REFERENCES enseignant(id) ON DELETE CASCADE
);

-- Insertion de rôles de base
INSERT INTO role (shortname, name) VALUES ('student', 'Élève');
INSERT INTO role (shortname, name) VALUES ('teacher', 'Enseignant');
INSERT INTO role (shortname, name) VALUES ('admin', 'Administrateur');
