<?php
// includes/foster_carer_validation.php
// shared input handling for the foster carer add/edit forms.

function collect_foster_carer_input(array $post): array
{
    return [
        'first_name'            => trim($post['first_name'] ?? ''),
        'last_name'             => trim($post['last_name'] ?? ''),
        'email'                 => trim($post['email'] ?? ''),
        'phone'                 => trim($post['phone'] ?? ''),
        'suburb'                => trim($post['suburb'] ?? ''),
        'preferred_animal_type' => trim($post['preferred_animal_type'] ?? ''),
        'capacity'              => (int)($post['capacity'] ?? 1),
        'status'                => $post['status'] ?? 'active',
        'notes'                 => trim($post['notes'] ?? ''),
    ];
}

function validate_foster_carer_input(array $data): array
{
    $errors = [];

    if ($data['first_name'] === '') {
        $errors['first_name'] = 'First name is required';
    }
    if ($data['last_name'] === '') {
        $errors['last_name'] = 'Last name is required';
    }
    if ($data['email'] === '') {
        $errors['email'] = 'Email address is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    if ($data['capacity'] < 1 || $data['capacity'] > 10) {
        $errors['capacity'] = 'Capacity must be between 1 and 10';
    }

    if (!in_array($data['status'], ['active', 'inactive'], true)) {
        $errors['status'] = 'Invalid status';
    }

    if ($data['preferred_animal_type'] !== '' && !in_array($data['preferred_animal_type'], FOSTER_ANIMAL_TYPES, true)) {
        $errors['preferred_animal_type'] = 'Please select a valid animal type';
    }

    return $errors;
}
