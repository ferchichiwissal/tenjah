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

    // Logique spécifique au tableau de bord de l'enseignant
    const manageGroupsBtn = document.getElementById('manage-groups-btn');
    const groupManagementModal = document.getElementById('group-management-modal');
    const closeModalBtn = groupManagementModal ? groupManagementModal.querySelector('.close-button') : null;
    const modalMatiereName = document.getElementById('modal-matiere-name');
    const newGroupNameInput = document.getElementById('new-group-name');
    const createGroupBtn = document.getElementById('create-group-btn');
    const unassignedStudentsListForCreation = document.getElementById('unassigned-students-list-for-creation');
    const existingGroupsList = document.getElementById('existing-groups-list');
    const unassignedStudentsDisplay = document.getElementById('unassigned-students-display');
    const groupManagementMessage = document.getElementById('group-management-message');
    const matieresList = document.getElementById('matieres-list'); // Ajouté
    const selectedMatiereEleves = document.getElementById('selected-matiere-eleves'); // Ajouté
    const inscriptionsElevesSection = document.getElementById('inscriptions-eleves-section'); // Ajouté
    const eleveDetailsSection = document.getElementById('eleve-details-section'); // Ajouté

    let currentMatiereId = null;
    let currentMatiereName = '';

    // Fonction pour ouvrir la modale de gestion des groupes
    if (manageGroupsBtn) {
        manageGroupsBtn.addEventListener('click', () => {
            const activeMatiereItem = document.querySelector('.matiere-item.active');
            if (!activeMatiereItem) {
                alert('Veuillez sélectionner une matière d\'abord.');
                return;
            }

            currentMatiereId = activeMatiereItem.dataset.matiereId;
            currentMatiereName = activeMatiereItem.dataset.matiereName;

            if (modalMatiereName) {
                modalMatiereName.textContent = currentMatiereName;
            }
            
            groupManagementModal.style.display = 'block';
            loadGroupsAndUnassignedStudents(currentMatiereId);
        });
    }

    // Fonction pour fermer la modale
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            groupManagementModal.style.display = 'none';
            groupManagementMessage.textContent = ''; // Clear messages
            newGroupNameInput.value = ''; // Clear input
            // Uncheck all students when closing the modal
            unassignedStudentsListForCreation.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    }

    // Fermer la modale si l'utilisateur clique en dehors
    if (groupManagementModal) {
        window.addEventListener('click', (event) => {
            if (event.target === groupManagementModal) {
                groupManagementModal.style.display = 'none';
                groupManagementMessage.textContent = ''; // Clear messages
                newGroupNameInput.value = ''; // Clear input
                // Uncheck all students when closing the modal
                unassignedStudentsListForCreation.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
            }
        });
    }

    // Fonction pour charger les groupes et les élèves non affectés
    async function loadGroupsAndUnassignedStudents(matiereId) {
        if (!matiereId) return;

        groupManagementMessage.textContent = 'Chargement des groupes et des élèves...';
        groupManagementMessage.style.color = 'blue';

        try {
            const response = await fetch(`../backend/get_groups_and_unassigned_students.php?matiere_id=${matiereId}`);
            const result = await response.json();

            if (result.success) {
            groupManagementMessage.textContent = ''; // Clear message on success
            displayGroups(result.groups);
            displayUnassignedStudents(result.unassigned_students); // This displays unassigned students in the main modal view
            populateUnassignedStudentsForCreation(result.unassigned_students); // This populates the list for new group creation
            } else {
                let errorMessage = 'Erreur lors du chargement: ' + result.message;
                if (result.error_details) {
                    errorMessage += '<br>Détails: ' + result.error_details;
                }
                if (result.trace) {
                    errorMessage += '<br><pre>Trace: ' + result.trace + '</pre>';
                }
                groupManagementMessage.innerHTML = errorMessage;
                groupManagementMessage.style.color = 'red';
            }
        } catch (error) {
            console.error('Erreur réseau lors du chargement des groupes et élèves:', error);
            groupManagementMessage.textContent = 'Erreur réseau lors du chargement des groupes et élèves.';
            groupManagementMessage.style.color = 'red';
        }
    }

    // Fonction pour afficher les groupes existants
    function displayGroups(groups) {
        if (existingGroupsList) {
            existingGroupsList.innerHTML = '';
            if (groups.length > 0) {
                groups.forEach(group => {
                    const groupDiv = document.createElement('div');
                    groupDiv.classList.add('group-card');
                    groupDiv.innerHTML = `
                        <h4>${group.nom}</h4>
                        <ul class="student-list">
                            ${group.students.map(student => `
                                <li>
                                    ${student.prenom} ${student.nom}
                                </li>
                            `).join('')}
                        </ul>
                    `;
                    existingGroupsList.appendChild(groupDiv);
                });
            } else {
                existingGroupsList.innerHTML = '<p>Aucun groupe créé pour cette matière.</p>';
            }
        }
    }

    // Fonction pour afficher les élèves non affectés
    function displayUnassignedStudents(students) {
        if (unassignedStudentsDisplay) {
            unassignedStudentsDisplay.innerHTML = '';
            if (students.length > 0) {
                const unassignedDiv = document.createElement('div');
                unassignedDiv.classList.add('group-card');
                unassignedDiv.innerHTML = `
                    <h4>Non affectés</h4>
                    <ul class="student-list">
                        ${students.map(student => `
                            <li>
                                ${student.prenom} ${student.nom}
                            </li>
                        `).join('')}
                    </ul>
                `;
                unassignedStudentsDisplay.appendChild(unassignedDiv);
            } else {
                unassignedStudentsDisplay.innerHTML = '<p>Tous les élèves sont affectés à un groupe.</p>';
            }
        }
    }

    // Fonction pour peupler la liste des élèves non affectés pour la création de groupe
    function populateUnassignedStudentsForCreation(students) {
        if (unassignedStudentsListForCreation) {
            unassignedStudentsListForCreation.innerHTML = '';
            if (students.length > 0) {
                students.forEach(student => {
                    const studentDiv = document.createElement('div');
                    studentDiv.classList.add('student-checkbox-item');
                    studentDiv.innerHTML = `
                        <input type="checkbox" id="create-group-student-${student.eleve_id}" value="${student.eleve_id}" data-inscription-id="${student.inscription_id}">
                        <label for="create-group-student-${student.eleve_id}">${student.eleve_prenom} ${student.eleve_nom} (Niveau: ${student.annonce_niveau} - Annonce: ${student.annonce_titre})</label>
                    `;
                    unassignedStudentsListForCreation.appendChild(studentDiv);
                });
            } else {
                unassignedStudentsListForCreation.innerHTML = '<p>Aucun élève non affecté disponible.</p>';
            }
        }
    }

    // Gérer la création d'un nouveau groupe et l'affectation des élèves
    if (createGroupBtn) {
        createGroupBtn.addEventListener('click', async () => {
            const groupName = newGroupNameInput.value.trim();
            if (!groupName) {
                groupManagementMessage.textContent = 'Veuillez donner un nom au groupe.';
                groupManagementMessage.style.color = 'red';
                return;
            }
            if (!currentMatiereId) {
                groupManagementMessage.textContent = 'Erreur: Aucune matière sélectionnée.';
                groupManagementMessage.style.color = 'red';
                return;
            }

            const selectedStudents = [];
            unassignedStudentsListForCreation.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
                selectedStudents.push({
                    eleve_id: checkbox.value,
                    inscription_id: checkbox.dataset.inscriptionId
                });
            });

            // Students can be optionally assigned later, so no need to check if selectedStudents.length === 0 here.

            groupManagementMessage.textContent = 'Création du groupe et affectation des élèves...';
            groupManagementMessage.style.color = 'blue';

            try {
                const response = await fetch('../backend/create_group.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        nom: groupName,
                        matiere_id: currentMatiereId,
                        students: selectedStudents // Pass selected students to the backend
                    })
                });
                const result = await response.json();

                if (result.success) {
                    groupManagementMessage.textContent = 'Groupe créé et élèves affectés avec succès !';
                    groupManagementMessage.style.color = 'green';
                    newGroupNameInput.value = ''; // Clear input
                    // Uncheck all students after successful creation
                    unassignedStudentsListForCreation.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    loadGroupsAndUnassignedStudents(currentMatiereId); // Recharger les données
                    window.loadInscriptions(); // Refresh the main dashboard view
                } else {
                    let errorMessage = 'Erreur lors de la création du groupe: ' + result.message;
                    if (result.error_details) {
                        errorMessage += '<br>Détails: ' + result.error_details;
                    }
                    if (result.trace) {
                        errorMessage += '<br><pre>Trace: ' + result.trace + '</pre>';
                    }
                    groupManagementMessage.innerHTML = errorMessage;
                    groupManagementMessage.style.color = 'red';
                }
            } catch (error) {
                console.error('Erreur réseau lors de la création du groupe:', error);
                groupManagementMessage.textContent = 'Erreur réseau lors de la création du groupe.';
                groupManagementMessage.style.color = 'red';
            }
        });
    }

    // Modification de la fonction loadInscriptions pour gérer les clics sur les matières
    window.loadInscriptions = async function() {
        try {
            const response = await fetch('../backend/get_inscriptions_enseignant.php');
            const result = await response.json();

            if (result.success) {
                matieresList.innerHTML = ''; // Clear previous content
                selectedMatiereEleves.innerHTML = '<p>Sélectionnez une matière pour voir les élèves inscrits.</p>'; // Reset right panel

                if (result.matieres && result.matieres.length > 0) {
                    result.matieres.forEach(matiereData => {
                        const matiereItem = document.createElement('div');
                        matiereItem.classList.add('matiere-item');
                        matiereItem.dataset.matiereId = matiereData.matiere_id;
                        matiereItem.dataset.matiereName = matiereData.matiere_nom;
                        matiereItem.innerHTML = `
                            <div class="matiere-name">${matiereData.matiere_nom}</div>
                            <ul class="group-names-list">
                                ${matiereData.groups.map(group => `<li>${group.nom}</li>`).join('')}
                                ${matiereData.unassigned_students.length > 0 ? '<li>Non affectés</li>' : ''}
                            </ul>
                        `;
                        matieresList.appendChild(matiereItem);

                        // Add click event to show students for this matiere
                        matiereItem.addEventListener('click', () => {
                            document.querySelectorAll('.matiere-item').forEach(item => item.classList.remove('active'));
                            matiereItem.classList.add('active');

                            currentMatiereId = matiereData.matiere_id;
                            currentMatiereName = matiereData.matiere_nom;

                            selectedMatiereEleves.innerHTML = `<h3>Élèves inscrits à ${matiereData.matiere_nom}</h3>`;
                            
                            // Display groups and their students
                            if (matiereData.groups.length > 0) {
                                matiereData.groups.forEach(group => {
                                    const groupDiv = document.createElement('div');
                                    groupDiv.classList.add('group-card');
                                    groupDiv.innerHTML = `
                                        <h4>${group.nom}</h4>
                                        <ul class="student-list">
                                            ${group.students.map(student => `
                                                <li>
                                                    ${student.eleve_prenom} ${student.eleve_nom}
                                                </li>
                                            `).join('')}
                                        </ul>
                                    `;
                                    selectedMatiereEleves.appendChild(groupDiv);
                                });
                            }

                            // Display unassigned students
                            if (matiereData.unassigned_students.length > 0) {
                                const unassignedDiv = document.createElement('div');
                                unassignedDiv.classList.add('group-card');
                                unassignedDiv.innerHTML = `
                                    <h4>Non affectés</h4>
                                    <ul class="student-list">
                                        ${matiereData.unassigned_students.map(student => `
                                            <li>
                                                ${student.eleve_prenom} ${student.eleve_nom}
                                            </li>
                                        `).join('')}
                                    </ul>
                                `;
                                selectedMatiereEleves.appendChild(unassignedDiv);
                            } else if (matiereData.groups.length === 0) {
                                selectedMatiereEleves.innerHTML += '<p>Aucun élève inscrit ou tous les élèves sont affectés à un groupe.</p>';
                            }
                            eleveDetailsSection.style.display = 'block'; // Show right panel
                        });
                    });
                } else {
                    matieresList.innerHTML = '<p>Aucune matière avec des élèves inscrits pour le moment.</p>';
                }
                inscriptionsElevesSection.style.display = 'block';
            } else {
                let errorMessage = '<p>Erreur lors du chargement des inscriptions: ' + result.message + '</p>';
                if (result.error_details) {
                    errorMessage += '<p>Détails: ' + result.error_details + '</p>';
                }
                if (result.trace) {
                    errorMessage += '<pre>Trace: ' + result.trace + '</pre>';
                }
                matieresList.innerHTML = errorMessage;
                inscriptionsElevesSection.style.display = 'block';
            }
        } catch (error) {
            console.error('Erreur réseau lors du chargement des inscriptions:', error);
            matieresList.innerHTML = '<p>Erreur réseau lors du chargement des inscriptions.</p>';
            inscriptionsElevesSection.style.display = 'block';
        }
    }
});
