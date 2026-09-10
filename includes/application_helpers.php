<?php
/**
 * Shared adoption application helpers.
 *
 * Included by any page that displays an application status.
 * Contains no database access, so it's cheap to include anywhere.
 *
 */

// Maps the status ENUM's internal snake_case values to the display
// wording used in the assignment brief.
$applicationStatusLabels = [
    'new'          => 'New',
    'under_review' => 'Under review',
    'approved'     => 'Approved',
    'rejected'     => 'Rejected',
];

// Bootstrap badge color per status
$applicationStatusBadges = [
    'new'          => 'bg-info',
    'under_review' => 'bg-warning text-dark',
    'approved'     => 'bg-success',
    'rejected'     => 'bg-secondary',
];

// How the applicant answered the own/rent question
$ownershipLabels = [
    'own'   => 'Owns their home',
    'rent'  => 'Rents their home',
    'other' => 'Other arrangement',
];
