<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenjah - Trouvez le professeur parfait</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        const loggedInUser = <?php echo isset($_SESSION['user_id']) ? json_encode(['id' => $_SESSION['user_id'], 'email' => $_SESSION['email'], 'nom' => $_SESSION['nom'], 'prenom' => $_SESSION['prenom']]) : 'null'; ?>;
    </script>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Tenjah</div>
            <div id="guest-nav">
                <ul>
                    <li><a href="register_teacher.html">Donner des cours</a></li>
                    <li><a href="login.html">Connexion</a></li>
                </ul>
            </div>
            <div id="user-nav" style="display: none;">
                <div class="user-profile">
                    <img src="images/avatar_user.png" alt="User Avatar" class="profile-avatar">
                    <span id="user-full-name" style="display: block; text-align: center; margin-top: 5px;"></span>
                    <div class="dropdown-menu">
                        <a href="dashboard.html">Tableau de bord</a>
                        <a href="#">Compte</a>
                        <a href="#" id="logout-link">Se déconnecter</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>Trouvez le professeur parfait</h1>
            <div class="search-bar">
                <input type="text" placeholder="Essayez "Échecs"">
                <button>Rechercher</button>
            </div>
            <div class="categories">
                <div class="category-item"><img src="images/icon-maths.png" alt="Maths">Maths</div>
                <div class="category-item"><img src="images/icon-anglais.png" alt="Anglais">Anglais</div>
                <div class="category-item"><img src="images/icon-piano.png" alt="Piano">Piano</div>
                <div class="category-item"><img src="images/icon-francais.png" alt="Français">Français</div>
                <div class="category-item"><img src="images/icon-guitare.png" alt="Guitare">Guitare</div>
                <div class="category-item"><img src="images/icon-tennis.png" alt="Tennis">Tennis</div>
                <div class="category-item arrow"><img src="images/icon-arrow-right.png" alt="Plus"></div>
            </div>
        </section>

        <section class="courses-overview">
            <h2>Plus de 130.000 professeurs, plus de 700 activités.</h2>
            <div class="course-cards">
                <div class="card">
                    <img src="images/course-soutien-scolaire.jpg" alt="Soutien scolaire">
                    <h3>Le soutien scolaire</h3>
                    <p>73828 Professeurs</p>
                </div>
                <div class="card">
                    <img src="images/course-musique.webp" alt="Cours de musique">
                    <h3>Les cours de musique</h3>
                    <p>18260 Professeurs</p>
                </div>
                <div class="card">
                    <img src="images/course-langue.jpg" alt="Cours de langue">
                    <h3>Les cours de langue</h3>
                    <p>68946 Professeurs</p>
                </div>
                <div class="card">
                    <img src="images/course-en-ligne.jpg" alt="Cours en ligne">
                    <h3>Autres</h3>
                    <p>86413 Professeurs</p>
                </div>
            </div>
        </section>

        <section class="popular-courses">
            <h2>Nos cours particuliers les plus populaires</h2>
            <div class="course-list">
                <div>
                    <h4>Soutien scolaire</h4>
                    <ul>
                        <li>Maths</li>
                        <li>Physique</li>
                        <li>Chimie</li>
                        <li>Aide aux devoirs</li>
                        <li>Français</li>
                        <li>Anglais</li>
                    </ul>
                </div>
                <div>
                    <h4>Cours de langue</h4>
                    <ul>
                        <li>Russe</li>
                        <li>Espagnol</li>
                        <li>Chinois</li>
                        <li>Allemand</li>
                        <li>Arabe</li>
                        <li>Italien</li>
                    </ul>
                </div>
                <div>
                    <h4>Cours de musique</h4>
                    <ul>
                        <li>Musique pour enfants</li>
                        <li>Théorie de la musique</li>
                        <li>Chant</li>
                        <li>Guitare</li>
                        <li>Piano</li>
                        <li>Solfège</li>
                    </ul>
                </div>
                <div>
                    <h4>Informatique & électronique</h4>
                    <ul>
                        <li>Initiation à l'informatique (rudiments)</li>
                        <li>Adobe photoshop</li>
                        <li>Programmation informatique</li>
                        <li>Informatique</li>
                        <li>Microsoft excel</li>
                        <li>Développement de site web (initiation)</li>
                    </ul>
                </div>
                <div>
                    <h4>Sport, Danse, Nutrition & Bien-être</h4>
                    <ul>
                        <li>Natation</li>
                        <li>Yoga</li>
                        <li>Fitness</li>
                        <li>Coaching sportif</li>
                        <li>Entraîneur personnel (sport)</li>
                    </ul>
                </div>
                <div>
                    <h4>Autres cours</h4>
                    <ul>
                        <li>Parole en public (art oratoire)</li>
                        <li>Écriture</li>
                        <li>Peinture</li>
                        <li>Sciences sociales</li>
                        <li>Comptabilité d'entreprise</li>
                        <li>Dessin & croquis</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="testimonials">
            <h2>Ce que nos élèves disent de nos professeurs</h2>
            <div class="testimonial-cards">
                <div class="testimonial-card testimonial-card-green">
                    <p>"Je recommande vivement Walid comme professeur d'anglais. Ses cours sont captivants, bien structurés et adaptés à mes besoins. Grâce à ses conseils, j'ai amélioré ma confiance en moi, ma fluidité et mes compétences générales en communication anglaise."</p>
                    <div class="author">
                        <img src="images/avatar_ivan.jpg" alt="Ivan">
                        <span>Walid<br>Prof d'anglais</span>
                    </div>
                    <div class="rating">⭐⭐⭐⭐⭐</div>
                </div>
                <div class="testimonial-card testimonial-card-yellow">
                    <p>"J'ai pris 2 ans de cours avec Mariem et je suis très satisfaite ! Elle est très à l'écoute, repère rapidement ce qu'il faut corriger et m'a permis de beaucoup progresser alors que je n'avais jamais pratiqué auparavant"</p>
                    <div class="author">
                        <img src="images/avatar_julien.jpg" alt="Julien">
                        <span>Mariem<br>Prof de guitare</span>
                    </div>
                    <div class="rating">⭐⭐⭐⭐⭐</div>
                </div>
                <div class="testimonial-card testimonial-card-green">
                    <p>"Skander est un excellent prof d'informatique. Il est très pédagogue et a su m'expliquer tout les concepts de manière claire et concise. Je le recommande vivement."</p>
                    <div class="author">
                        <img src="images/etudiante.jpg" alt="Ivan">
                        <span>Skander<br>Prof d'informatique</span>
                    </div>
                    <div class="rating">⭐⭐⭐⭐⭐</div>
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


               
       

    <script src="js/script.js"></script>
    <script src="js/auth.js"></script>
</body>
</html>
