const classeSelect = document.getElementById('classe-select');
const matiereSelect = document.getElementById('matiere-select');
const professeurSelect = document.getElementById('professeur-select');
const salleSelect = document.getElementById('salle-select');

// Hidden input fields for storing names
const professeurValue = document.getElementById('professeur-value');
const matiereValue = document.getElementById('matiere-value');
const classeValue = document.getElementById('classe-value');
const salleValue = document.getElementById('salle-value');


function resetSelect(select, defaultText = "", disabled = true) {
    select.innerHTML = `<option value="">${defaultText}</option>`;
    select.disabled = disabled;
}

// Handle classe selection
classeSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption && selectedOption.textContent) {
        classeValue.value = selectedOption.textContent; // Store the class name
    }
    
    // Reset subsequent selects
    resetSelect(matiereSelect, "Sélectionnez une matière");
    resetSelect(professeurSelect, "Choisissez un enseignant");

    if (this.value) {
        fetch(`?action=get_matieres&classe_id=${this.value}`)
            .then(response => response.json())
            .then(data => {
                matiereSelect.disabled = false;
                data.forEach(matiere => {
                    const option = document.createElement('option');
                    option.value = matiere.id;
                    option.textContent = matiere.nom;
                    matiereSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Erreur:', error));
    }
});

// Handle matière selection
matiereSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption && selectedOption.textContent) {
        matiereValue.value = selectedOption.textContent; // Store the matiere name
    }
    
    resetSelect(professeurSelect, "Choisissez un enseignant");

    if (this.value) {
        fetch(`?action=get_professeurs&matiere_id=${this.value}`)
            .then(response => response.json())
            .then(data => {
                professeurSelect.disabled = false;
                data.forEach(prof => {
                    const option = document.createElement('option');
                    option.value = prof.id;
                    option.textContent = prof.nom;
                    professeurSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Erreur:', error));
    }
});

// Update hidden values when selections change
professeurSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption && selectedOption.textContent) {
        professeurValue.value = selectedOption.textContent; // Store the professor name
    }
});

salleSelect.addEventListener('change', function() {
  const selectedOption = this.options[this.selectedIndex];
  if (selectedOption && selectedOption.textContent) {
      salleValue.value = selectedOption.textContent;
  }
});

// Form validation before submit
document.getElementById('schedule-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default submission

    // Get all the values
    const classeNom = classeValue.value;
    const matiereNom = matiereValue.value;
    const professeurNom = professeurValue.value;
    // const salleNom = salleSelect.options[salleSelect.selectedIndex].textContent;
    const salleNom = salleValue.value;  // Utiliser la valeur du champ caché

    
    // Validate that all required fields have values
    if (!classeNom || !matiereNom || !professeurNom || !salleNom) {
        alert('Tous les champs sont obligatoires');
        return;
    }

    // Validate datetime
    const start = new Date(document.getElementById('start_datetime').value);
    const end = new Date(document.getElementById('end_datetime').value);

    if (start >= end) {
        alert('La date de fin doit être postérieure à la date de début');
        return;
    }

    // Update hidden fields with actual names
    classeValue.value = classeNom;
    matiereValue.value = matiereNom;
    professeurValue.value = professeurNom;
    salleSelect.value = salleNom;

    // If all validations pass, submit the form
    this.submit();
});