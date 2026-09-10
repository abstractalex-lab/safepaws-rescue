<?php
/**
 * Shared animal helpers.
 *
 * Included by any page that needs to display an animal's status.
 * Contains no database access, so it's cheap to include anywhere.
 */

// Maps the status ENUM's internal snake_case values to the display labels used in the UI
$statusLabels = [
    'in_care'   => 'In care',
    'available' => 'Available for adoption',
    'pending'   => 'Adoption pending',
    'adopted'   => 'Adopted',
];

// Rough age from a date of birth, or null when none is recorded
function animalAge(?string $dateOfBirth): ?string
{
    if (!$dateOfBirth) {
        return null;
    }

    // Use try-catch to handle any potential exceptions from invalid date formats
    try {
        $diff = (new DateTime($dateOfBirth))->diff(new DateTime());
    } catch (Exception $e) {
        // Returning null renders as "Unknown" rather than crashing the page, and log the error for debugging
        error_log('Age calculation failed: ' . $e->getMessage());
        return null;
    }

    // Return years if greater than 0, otherwise return months
    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y === 1 ? '' : 's');
    }
    return $diff->m . ' month' . ($diff->m === 1 ? '' : 's');
}