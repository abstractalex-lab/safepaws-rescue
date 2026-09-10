/**
 * Cascading species -> breed dropdown.
 *
 * Expects three globals to already be defined by the including page:
 *   - breedsBySpecies: object keyed by species_id, each value an array of { id, name } breed objects for that species
 *   - oldBreedId: previously selected breed_id (or null), used to restore selection after a failed form validation
 *   - oldSpeciesId: the species_id matching oldBreedId (or null)
 */

// Update the breed dropdown when the species selection changes
function updateBreeds() {
    const speciesSelect = document.getElementById('species');
    const breedSelect = document.getElementById('breed_id');
    const speciesId = speciesSelect.value;

    breedSelect.innerHTML = '';
    (breedsBySpecies[speciesId] || []).forEach(function (breed) {
        const option = document.createElement('option');
        option.value = breed.id;
        option.textContent = breed.name;
        if (oldBreedId && String(breed.id) === String(oldBreedId)) {
            option.selected = true;
        }
        breedSelect.appendChild(option);
    });
}

// On initial load, restore the previously selected species (if any)
// before populating breeds, so a failed submission re-shows correctly
if (typeof oldSpeciesId !== 'undefined' && oldSpeciesId) {
    document.getElementById('species').value = oldSpeciesId;
}
updateBreeds();