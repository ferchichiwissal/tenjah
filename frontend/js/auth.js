document.addEventListener('DOMContentLoaded', () => {
    const guestNav = document.getElementById('guest-nav');
    const userNav = document.getElementById('user-nav');
    const logoutLink = document.getElementById('logout-link');
    const profileAvatar = document.querySelector('.user-profile .profile-avatar');
    const dropdownMenu = document.querySelector('.user-profile .dropdown-menu');

    async function fetchUserInfo() {
        try {
            const response = await fetch('../backend/get_user_info.php');
            const userData = await response.json();

            if (userData.loggedIn) {
                // Logic for logged-in user
                if (guestNav) guestNav.style.display = 'none';
                if (userNav) userNav.style.display = 'flex';

                const userFullNameElement = document.getElementById('user-full-name');
                if (userFullNameElement && userData.nom && userData.prenom) {
                    userFullNameElement.textContent = `${userData.nom} ${userData.prenom}`;
                }

                // Dashboard specific elements (only present on dashboard.html)
                const welcomeMessageElement = document.getElementById('welcome-message');
                const userInfoElement = document.getElementById('user-info');
                const roleInfoElement = document.getElementById('role-info');

                if (welcomeMessageElement) {
                    welcomeMessageElement.textContent = `Bienvenue, ${userData.prenom} ${userData.nom} !`;
                }

           /*     if (userInfoElement) {
                    userInfoElement.textContent = `Vous êtes connecté en tant que ${userData.prenom} ${userData.nom}.`;
                }*/

                if (roleInfoElement) {
                    if (userData.role === 'teacher') {
                       // roleInfoElement.textContent = "Tableau de bord enseignant";
                        const createAnnonceBtn = document.getElementById('create-annonce-btn');
                        const inscriptionsElevesSection = document.getElementById('inscriptions-eleves-section');
                        const eleveDetailsSection = document.getElementById('eleve-details-section');
                        const teacherSessionsSection = document.getElementById('teacher-sessions-section');
                        const studentMatieresSection = document.getElementById('student-matieres-section');
                        const studentSessionsDetailsSection = document.getElementById('student-sessions-details-section');

                        const teacherActionsDiv = document.querySelector('.teacher-actions');
                        if (teacherActionsDiv) teacherActionsDiv.style.display = 'flex'; // Afficher le conteneur des boutons

                        // Afficher les éléments spécifiques à l'enseignant
                        if (createAnnonceBtn) createAnnonceBtn.style.display = 'block';
                        if (inscriptionsElevesSection) inscriptionsElevesSection.style.display = 'block';
                        if (eleveDetailsSection) eleveDetailsSection.style.display = 'block'; // Afficher par défaut pour l'enseignant
                        if (teacherSessionsSection) teacherSessionsSection.style.display = 'block'; // Afficher par défaut pour l'enseignant

                        // Afficher le bouton "Statistiques" pour les enseignants
                        const statistiqueBtn = document.getElementById('statistique-btn');
                        if (statistiqueBtn) {
                            statistiqueBtn.style.display = 'block';
                            statistiqueBtn.addEventListener('click', () => {
                                window.location.href = 'statistique.html';
                            });
                        }

                        // Masquer les éléments spécifiques à l'élève
                        if (studentMatieresSection) studentMatieresSection.style.display = 'none';
                        if (studentSessionsDetailsSection) studentSessionsDetailsSection.style.display = 'none';

                        // Appeler les fonctions de chargement pour l'enseignant
                        if (typeof loadInscriptions === 'function') loadInscriptions();
                        if (typeof loadTeacherSessions === 'function') loadTeacherSessions();

                    } else if (userData.role === 'student') {
                      //  roleInfoElement.textContent = "Tableau de bord élève";
                        const createAnnonceBtn = document.getElementById('create-annonce-btn');
                        const inscriptionsElevesSection = document.getElementById('inscriptions-eleves-section');
                        const eleveDetailsSection = document.getElementById('eleve-details-section');
                        const teacherSessionsSection = document.getElementById('teacher-sessions-section');
                        const studentMatieresSection = document.getElementById('student-matieres-section');
                        const studentSessionsDetailsSection = document.getElementById('student-sessions-details-section');

                        // Afficher les éléments spécifiques à l'élève
                        if (studentMatieresSection) studentMatieresSection.style.display = 'block';
                        if (studentSessionsDetailsSection) studentSessionsDetailsSection.style.display = 'block'; // Afficher par défaut pour l'élève

                        // Masquer les éléments spécifiques à l'enseignant
                        if (createAnnonceBtn) createAnnonceBtn.style.display = 'none';
                        if (inscriptionsElevesSection) inscriptionsElevesSection.style.display = 'none';
                        if (eleveDetailsSection) eleveDetailsSection.style.display = 'none';
                        if (teacherSessionsSection) teacherSessionsSection.style.display = 'none';
                        
                        // Masquer le bouton "Statistiques" pour les élèves
                        const statistiqueBtn = document.getElementById('statistique-btn');
                        if (statistiqueBtn) statistiqueBtn.style.display = 'none';

                        // Appeler les fonctions de chargement pour l'élève
                        if (typeof loadStudentMatieres === 'function') loadStudentMatieres();

                    } else {
                        // Rôle inconnu ou non spécifié
                        roleInfoElement.textContent = `Votre rôle: ${userData.role}`;
                        // Masquer toutes les sections spécifiques aux rôles
                        const createAnnonceBtn = document.getElementById('create-annonce-btn');
                        const inscriptionsElevesSection = document.getElementById('inscriptions-eleves-section');
                        const eleveDetailsSection = document.getElementById('eleve-details-section');
                        const teacherSessionsSection = document.getElementById('teacher-sessions-section');
                        const studentMatieresSection = document.getElementById('student-matieres-section');
                        const studentSessionsDetailsSection = document.getElementById('student-sessions-details-section');
                        const statistiqueBtn = document.getElementById('statistique-btn'); // Ajouter pour masquer

                        if (createAnnonceBtn) createAnnonceBtn.style.display = 'none';
                        if (inscriptionsElevesSection) inscriptionsElevesSection.style.display = 'none';
                        if (eleveDetailsSection) eleveDetailsSection.style.display = 'none';
                        if (teacherSessionsSection) teacherSessionsSection.style.display = 'none';
                        if (studentMatieresSection) studentMatieresSection.style.display = 'none';
                        if (studentSessionsDetailsSection) studentSessionsDetailsSection.style.display = 'none';
                        if (statistiqueBtn) statistiqueBtn.style.display = 'none'; // Masquer pour les rôles inconnus
                    }
                }

                if (profileAvatar) {
                    profileAvatar.addEventListener('click', () => {
                        if (dropdownMenu) {
                            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
                        }
                    });
                }

                if (logoutLink) {
                    logoutLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        window.location.href = '../backend/logout.php';
                    });
                }
            } else {
                // Logic for not logged-in user
                if (guestNav) guestNav.style.display = 'flex';
                if (userNav) userNav.style.display = 'none';

                // If on dashboard.html and not logged in, redirect to login
                if (window.location.pathname.includes('dashboard.html')) {
                    window.location.href = 'login.html';
                }
            }
        } catch (error) {
            console.error('Error fetching user info:', error);
            // If on dashboard.html and error occurs, redirect to login
            if (window.location.pathname.includes('dashboard.html')) {
                window.location.href = 'login.html';
            }
        }
    }

    fetchUserInfo();

    window.addEventListener('click', (event) => {
        if (profileAvatar && dropdownMenu && !profileAvatar.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.style.display = 'none';
        }
    });
});
