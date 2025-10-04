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

                if (userInfoElement) {
                    userInfoElement.textContent = `Vous êtes connecté en tant que ${userData.prenom} ${userData.nom}.`;
                }

                if (roleInfoElement) {
                    if (userData.role === 'teacher') {
                        roleInfoElement.textContent = "Tableau de bord enseignant";
                        // Afficher le bouton "Créer une Annonce" si l'utilisateur est un enseignant
                const createAnnonceBtn = document.getElementById('create-annonce-btn');
                const inscriptionsElevesSection = document.getElementById('inscriptions-eleves-section');

                if (createAnnonceBtn) {
                    createAnnonceBtn.style.display = 'block';
                }
                if (inscriptionsElevesSection) {
                    inscriptionsElevesSection.style.display = 'block';
                    // Appeler loadInscriptions() si l'utilisateur est un enseignant et sur le tableau de bord
                    if (typeof loadInscriptions === 'function') {
                        loadInscriptions();
                    }
                }
                    } else if (userData.role === 'student') {
                        roleInfoElement.textContent = "Tableau de bord élèves";
                    } else {
                        roleInfoElement.textContent = `Votre rôle: ${userData.role}`;
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
