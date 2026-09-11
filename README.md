# SafePaws Rescue

> *A web database system for a not-for-profit animal rescue centre – animals, foster placements, and adoptions in one place.*

![PHP](https://img.shields.io/badge/PHP_8-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap_5-7952B3?style=flat&logo=bootstrap&logoColor=white)
![No Framework](https://img.shields.io/badge/framework-none-lightgrey?style=flat)


## ℹ️ Overview

SafePaws Rescue manages the operational side of an animal rescue: intake records, foster placements, veterinary partners, and adoption applications. Plus a public site where visitors browse animals and apply to adopt.

Built with **vanilla PHP and MySQL over PDO**. Using no framework, no ORM, no Composerevery - query, session check and file upload is written out, which was the goal of the exercise.

Completed as **Assessment 3 (20%)** for **FIT2104 – Web Database Systems** at Monash University, Semester 2, 2026, as a two-person group project.

### 🌟 Highlights

- **Pluggable session guard** re-validating the user on every request, not just at login
- **Prepared statements throughout** ensure nothing interpolated into SQL anywhere
- **Safe file uploads** with server-generated filenames and transactional cleanup
- **Transactional status updates** so an approved application can't leave an animal in a broken state
- **Fallback lookup rows** that keep `breed_id` NOT NULL across the whole schema

### ✍️ Authors

**Alex Bui** - schema, authentication, animals, adoption applications, public pages, dashboard\
**Bhudis Chitchonthan** - foster carers, partner organisations, users, contact enquiries, CSRF helper


## ⬇️ Getting Started

**Requirements:** PHP 8+, MySQL or MariaDB, a web server. XAMPP covers all three.

```bash
git clone https://github.com/abstractalex-lab/safepaws-rescue.git
```

1. Clone into your web root (`htdocs` on XAMPP).
2. Import `schema_data.sql` from phpMyAdmin's **server-level** Import tab. This creates the database and loads the demo data.
3. Copy `config.local.example.php` to `config.local.php` and fill in your local MySQL credentials.
4. Open the project folder in a browser.

Demo animal photos are committed under `animal_profiles/`, so sample data displays correctly on a fresh clone.

> **Demo login:** `emma_admin` / `Passw0rd!` - all sample staff accounts share this password. Throwaway credentials for throwaway data.


## 🚀 Features

### 🌐 Public Site
- Browse animals currently available for adoption
- Animal profiles with photo, breed, age and description
- Submit an adoption application online, one per email per animal
- General enquiry form

### 🐾 Animal Management
- Full CRUD with search, sorting, and status filtering
- Cascading species → breed dropdown driven by the lookup tables
- Profile photo upload, replacement and removal with filesystem cleanup
- Foster carer assignment and status tracking across four states

### 📋 Adoption Applications
- Admin review with status updates across New / Under review / Approved / Rejected
- Approving moves the animal to "Adoption pending" in the same transaction

### 🏠 Foster Carers & Partners
- Foster carer records with fostering capacity and remaining-capacity display
- Active/inactive toggling that keeps existing placements intact
- Partner organisation directory for vets, suppliers and community partners

### 🔐 Admin & Access Control
- Staff login with `password_hash()` and session fixation protection
- Six pages deliberately public; everything else behind the guard
- Contact enquiry tracking with replied status
- Dashboard with summary counts across the system


## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8 (no framework) |
| Database | MySQL / MariaDB via PDO |
| UI | Bootstrap 5, Bootstrap Icons |
| Tables | DataTables (client-side search and sorting) |
| Auth | Native sessions, `password_hash()` / `password_verify()` |


## 📁 Project Structure

```
animal_profiles/          Uploaded animal profile images
animals/                  Admin CRUD for animal records
applications/             Admin review of adoption applications
auth/                     Login, logout, shared authentication guard
contact/                  Public enquiry form and admin management
foster_carers/            Admin CRUD for foster carers
includes/                 Shared partials: navbar, CSRF, helper data
js/                       Client-side scripts
partner_organisations/    Admin CRUD for partner organisations
public/                   Public listing, detail, and adoption form
users/                    Admin CRUD for staff logins
connection.php            Single point of database connection
dashboard.php             Admin landing page after login
index.php                 Public homepage
schema_data.sql           Database schema and demo data
```


## 🏗️ Implementation Notes

**Access control** is one includable guard. `auth/authentication.php` sits at the top of every protected page and re-checks that the session user still exists and is still active on every request, so deactivating an account takes effect immediately, not at next login.

**File uploads** are validated server-side for type and size, then saved under a generated filename, so the uploaded name never reaches the filesystem. The old file is unlinked only after the database write commits, and a new upload is removed if that write rolls back. `animal_profiles/` never collects orphans and never loses a referenced file.

**Destructive actions** go through a confirmation page and only run on POST, so a prefetch or pasted URL can't delete a record. Animals with applications on record can't be deleted at all as the foreign key is `RESTRICT`, and the violation is caught and explained rather than surfacing as a SQL error.

**The breed lookup never allows a null.** Every species carries a "Mixed / Unknown" breed, and there's an "Unknown" species for undocumented intakes. So `animals.breed_id` is `NOT NULL` throughout and no page needs a null-breed branch.


## 💭 Academic Context & Disclaimer

Submitted for academic assessment at Monash University. Shared here for portfolio and reference purposes only.

> Please respect Monash University's academic integrity policy — **do not submit this work or any derivative as your own.**
