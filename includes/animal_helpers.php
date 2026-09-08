<?php
/**
 * Shared animal helpers.
 *
 * Included by any page that needs to display an animal's status.
 * Contains no database access, so it's cheap to include anywhere.
 */

// Maps the status ENUM's internal snake_case values to the display
// wording used in the assignment brief.
$statusLabels = [
    'in_care'   => 'In care',
    'available' => 'Available for adoption',
    'pending'   => 'Adoption pending',
    'adopted'   => 'Adopted',
];