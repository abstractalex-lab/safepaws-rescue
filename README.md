# Lab02_Group06 - SafePaws Rescue

FIT2104 S2 2026 Assessment 3 (Group).

A web database system for SafePaws Rescue, a not-for-profit animal rescue organization. Built with vanilla PHP (PDO_MYSQL) and MySQL.

## Authors

| Name | Student ID | Email |
|---|---|---|
| Alex Bui | 34662901 | abui0012@student.monash.edu |
| Bhudis Chitchonthan | 35752688 | bchi0038@student.monash.edu |

**Date of submission:** 11 September 2026

## Repository

https://git.infotech.monash.edu/fit2104/fit2104-2026-s2/fit2104-a3-group-repo/Lab02_Group06

## Login Credentials

The administration area is reachable from the "Login" link in the navigation bar,
or directly at `auth/login.php`.

| Username | Password |
|---|---|
| emma_admin | Passw0rd! |

All demo staff accounts in the sample data share the same password.

## Database Setup

| File | Contents |
|---|---|
| `schema_data.sql` | Table definitions with primary keys, foreign keys and constraints, plus all demo data |

1. Create the database by importing `schema_data.sql` in phpMyAdmin (import from
   the server-level Import tab - the file creates the schema itself).
2. Copy `config.local.example.php` to `config.local.php` and fill in your local
   MySQL credentials. The database name is `fit2104_a3`.

Uploaded animal images live in `animal_profiles/` and are committed with the
repository, so the sample data displays correctly on a fresh clone.

## Repository Structure

```
animal_profiles/              Uploaded animal profile images
animals/                      Admin CRUD for animal records, including image upload
applications/                 Admin review of adoption applications
auth/                         Login, logout, and the shared authentication guard
contact/                      Public enquiry form and admin enquiry management
foster_carers/                Admin CRUD for foster carers
includes/                     Shared partials: navbar, CSRF, helper data
js/                           Client-side scripts
partner_organisations/        Admin CRUD for partner organisations
public/                       Public animal listing, detail, and adoption form
users/                        Admin CRUD for staff logins
config.local.example.php      Template for local database credentials
connection.php                Single point of database connection
dashboard.php                 Admin landing page after login
index.php                     Public homepage
schema_data.sql               Database schema and demo data
```

Pages that remain publicly accessible without logging in: `index.php`,
`auth/login.php`, `public/animals.php`, `public/animal_detail.php`,
`public/apply.php`, and `contact/index.php`. Every other page includes
`auth/authentication.php`, which redirects unauthenticated visitors to the
login page.

## Work Breakdown Agreement

### Alex Bui

- Database schema design and the demo data set
- `connection.php` and the local configuration split
- Authentication: login, logout, and the pluggable session guard used by every protected page
- Animals module: list, detail, add, edit, delete, including profile image upload, replacement and deletion
- Public pages: animal listing, animal detail, and the adoption application form
- Adoption applications module: admin list, detail with status updates, delete
- Admin dashboard and the public homepage
- Bootstrap styling across the above pages

### Bhudis Chitchonthan

- Database schema design and the demo data set
- Partner organisations module: list, add, edit, delete
- Foster carers module: list, view, add, edit, delete, active/inactive toggle
- Users module: list, add, edit, delete, active/inactive toggle
- Contact Us: public enquiry form, admin enquiry list, replied status, delete
- Shared CSRF helper and foster carer validation helpers
- Bootstrap styling across the above pages

### Shared

- The navigation bar in `includes/navbar.php` was created by Bhudis and later
  reworked by Alex to build links from the document root and cover the
  remaining modules.

Commit history for both members is visible on the repository's Git graph and
aligns with the breakdown above.

## Notes

Do not share this repository outside the group and the teaching team.