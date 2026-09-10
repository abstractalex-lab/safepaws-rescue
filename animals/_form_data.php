<?php
/**
 * Shared dropdown data for the animal add/edit forms.
 *
 * Requires $pdo to already be available, and optionally $currentFosterCarerId.
 * Set $currentFosterCarerId to the ID of the current foster carer before including this file to keep an
 * already-assigned but now-inactive carer in the list (edit.php does this).
 *
 * Defines: $species, $breeds, $fosterCarers
 *
 * @var PDO $pdo
 */

// All species + all breeds (grouped by species in JS) for the cascading dropdown. Unknown/Mixed are always listed last
$species = $pdo->query(
    "SELECT species_id, species_name
     FROM species
     ORDER BY (species_name = 'Unknown'), species_name"
)->fetchAll();

$breeds = $pdo->query(
    "SELECT breed_id, species_id, breed_name
     FROM breeds
     ORDER BY (breed_name LIKE 'Mixed%' OR breed_name = 'Unknown'), breed_name"
)->fetchAll();

// Only active carers can take a new assignment
// On the edit form, the animal's existing carer is included even if inactive
$currentFosterCarerId = $currentFosterCarerId ?? null;

// Query for active carers, plus the current one if set (even if inactive)
// Ordered by first name for easier scanning
$carerStmt = $pdo->prepare(
    "SELECT foster_carer_id, first_name, last_name, status
     FROM foster_carers
     WHERE status = 'active' OR foster_carer_id = :current
     ORDER BY first_name"
);
$carerStmt->execute(['current' => $currentFosterCarerId]);
$fosterCarers = $carerStmt->fetchAll();