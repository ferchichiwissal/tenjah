// JavaScript pour des fonctionnalités interactives futures
console.log("Script chargé !");

document.addEventListener('DOMContentLoaded', () => {
    const matiereInput = document.getElementById('search-matiere');
    const niveauInput = document.getElementById('search-niveau');
    const searchForm = document.querySelector('.search-bar-form'); // Utiliser le sélecteur de classe

    // Gérer la soumission du formulaire de recherche
    if (searchForm) {
        searchForm.addEventListener('submit', (event) => {
            event.preventDefault(); // Empêche la soumission par défaut du formulaire

            const matiere = matiereInput.value.trim();
            const niveau = niveauInput.value.trim();

            // Rediriger vers la page de résultats avec les paramètres de recherche
            const queryParams = new URLSearchParams();
            if (matiere) {
                queryParams.append('matiere', matiere);
            }
            if (niveau) {
                queryParams.append('niveau', niveau);
            }

            window.location.href = `search_results.php?${queryParams.toString()}`;
        });
    }
});
