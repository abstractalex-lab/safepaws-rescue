<?php
/**
 * Cascading dropdown data block for the animal add/edit forms.
 *
 * Rendered inline (rather than living in animal-form.js) because the values come from the database
 * and the current request. The logic that consumes them is in js/animal-form.js.
 *
 * @var array $species
 * @var array $breeds
 * @var array $old
 */
?>
<script>
    // Breed data grouped by species, embedded directly since the dataset is small and static
    const breedsBySpecies = {};
    <?php foreach ($species as $s): ?>
    breedsBySpecies[<?= $s['species_id'] ?>] = [
        <?php foreach ($breeds as $b): ?>
        <?php if ($b['species_id'] == $s['species_id']): ?>
        { id: <?= $b['breed_id'] ?>, name: <?= json_encode($b['breed_name']) ?> },
        <?php endif; ?>
        <?php endforeach; ?>
    ];
    <?php endforeach; ?>

    const oldBreedId = <?= json_encode($old['breed_id'] ?? null) ?>;
    const oldSpeciesId = <?php
        // Pre-select the species matching the current breed, so the breed
        // dropdown lands on the right option after a load or failed submit.
        $oldSpeciesId = null;
        if (!empty($old['breed_id'])) {
            foreach ($breeds as $b) {
                if ($b['breed_id'] == $old['breed_id']) {
                    $oldSpeciesId = $b['species_id'];
                    break;
                }
            }
        }
        echo json_encode($oldSpeciesId);
        ?>;
</script>
<script src="../js/animal-form.js"></script>