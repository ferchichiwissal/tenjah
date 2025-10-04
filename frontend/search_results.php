<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche </title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">Tenjah</div>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="#">Cours</a></li>
                <li><a href="#">À propos</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
            <div id="user-nav">
                <!-- User profile will be loaded here by JavaScript -->
            </div>
        </nav>
    </header>

    <main>
        <section class="search-results-section">
            <div class="container">
                <h1>Résultats de recherche</h1>
                <div class="announcements-grid">
                    <?php
                    require_once '../backend/db_connect.php';

                    $matiere = $_GET['matiere'] ?? '';
                    $niveau = $_GET['niveau'] ?? '';

                    $sql = "SELECT 
                                a.id AS annonce_id, 
                                a.titre, 
                                a.description, 
                                a.niveau, 
                                a.prix_unitaire,
                                m.nom AS matiere_nom,
                                u.nom AS teacher_nom,
                                u.prenom AS teacher_prenom
                            FROM annonce a
                            JOIN matiere m ON a.matiere_id = m.id
                            JOIN enseignant e ON a.enseignant_id = e.id
                            JOIN utilisateur u ON e.user_id = u.id
                            WHERE 1=1";

                    $params = [];
                    $types = "";

                    if (!empty($matiere)) {
                        $sql .= " AND LOWER(m.nom) LIKE LOWER(?)";
                        $params[] = "%" . $matiere . "%";
                        $types .= "s";
                    }

                    if (!empty($niveau)) {
                        $sql .= " AND LOWER(a.niveau) LIKE LOWER(?)";
                        $params[] = "%" . $niveau . "%";
                        $types .= "s";
                    }


                    $stmt = $conn->prepare($sql);

                    if ($stmt === false) {
                        die('Erreur de préparation de la requête: ' . htmlspecialchars($conn->error));
                    }

                    if (!empty($params)) {
                        $stmt->bind_param($types, ...$params);
                        if ($stmt->errno) {
                            die('Erreur de liaison des paramètres: ' . htmlspecialchars($stmt->error));
                        }
                    }

                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="announcement-card">';
                            echo '<h2>' . htmlspecialchars($row['titre']) . '</h2>';
                            echo '<p><strong>Matière:</strong> ' . htmlspecialchars($row['matiere_nom']) . '</p>';
                            echo '<p><strong>Niveau:</strong> ' . htmlspecialchars($row['niveau']) . '</p>';
                            echo '<p><strong>Enseignant:</strong> ' . htmlspecialchars($row['teacher_prenom']) . ' ' . htmlspecialchars($row['teacher_nom']) . '</p>';
                            echo '<p>' . htmlspecialchars(substr($row['description'], 0, 150)) . '...</p>';
                            echo '<p class="price"><strong>Prix:</strong> ' . htmlspecialchars($row['prix_unitaire']) . 'dt/cours</p>';
                            echo '<button class="btn-details btn-inscrire" data-annonce-id="' . htmlspecialchars($row['annonce_id']) . '">S\'inscrire</button>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="no-results">Aucune annonce trouvée pour votre recherche.</p>';
                    }

                    $stmt->close();
                    $conn->close();
                    ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-column">
                <h3>A propos</h3>
                <ul>
                    <li><a href="#">Qui sommes-nous</a></li>
                    <li><a href="#">Mentions légales</a></li>
                    <li><a href="#">Confidentialité</a></li>
                    <li><a href="#">Pays</a></li>
                    <li><a href="#">Cours en Ligne</a></li>
                    <li><a href="#">Départements</a></li>
                    <li><a href="#">Jobs</a></li>
                    <li><a href="#">Carte cadeau</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Matières</h3>
                <ul>
                    <li><a href="#">Arts & loisirs</a></li>
                    <li><a href="#">Développement professionnel</a></li>
                    <li><a href="#">Informatique</a></li>
                    <li><a href="#">Langues</a></li>
                    <li><a href="#">Musique</a></li>
                    <li><a href="#">Santé & bien-être</a></li>
                    <li><a href="#">Scolaire</a></li>
                    <li><a href="#">Sports & danse</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Ressources</h3>
                <ul>
                    <li><a href="#">Tenjah Magazine</a></li>
                    <li><a href="#"> Ressources</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Assistance</h3>
                <ul>
                    <li><a href="#">Centre d'aide</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Suivez-nous</h3>
                <div class="social-icons">
                    <a href="#" class="social-icon facebook">f</a>
                    <a href="#" class="social-icon twitter">t</a>
                    <a href="#" class="social-icon instagram">i</a>
                    <a href="#" class="social-icon linkedin">in</a>
                    <a href="#" class="social-icon youtube">y</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <hr>
            <p>© 2025 Tenjaheh, la meilleure façon d'apprendre.</p>
        </div>
    </footer>

    <script src="js/auth.js"></script>
    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inscriptionButtons = document.querySelectorAll('.btn-inscrire');

            inscriptionButtons.forEach(button => {
                button.addEventListener('click', async (event) => {
                    const annonceId = event.target.dataset.annonceId;

                    if (!annonceId) {
                        alert('Erreur: ID de l\'annonce manquant.');
                        return;
                    }

                    // Vérifier si l'utilisateur est connecté et est un élève
                    const userInfoResponse = await fetch('../backend/get_user_info.php');
                    const userData = await userInfoResponse.json();

                    if (!userData.loggedIn || userData.role !== 'student') {
                        alert('Vous devez être connecté en tant qu\'élève pour vous inscrire à une annonce.');
                        window.location.href = 'login.html'; // Rediriger vers la page de connexion
                        return;
                    }

                    try {
                        const response = await fetch('../backend/inscrire_eleve.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `annonce_id=${annonceId}`
                        });

                        const result = await response.json();

                        if (result.success) {
                            alert(result.message);
                            // Optionnel: désactiver le bouton ou changer son texte après inscription réussie
                            event.target.disabled = true;
                            event.target.textContent = 'Inscrit';
                        } else {
                            alert('Échec de l\'inscription: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Erreur lors de l\'inscription:', error);
                        alert('Une erreur est survenue lors de l\'inscription.');
                    }
                });
            });
        });
    </script>
</body>
</html>
