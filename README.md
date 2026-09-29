# Shiksha Sahayata

**Digital Scholarship Management and Student Assistance System**

Shiksha Sahayata is a Nepal-focused, government-oriented **academic prototype** of a G2C (Government to Citizen) scholarship management system. It centralises scholarship discovery and makes the scholarship workflow transparent and trackable for students, guardians, schools, local education units and selection committees.

> **Disclaimer:** This is a BSc CSIT academic project. It is **not** an official government platform and makes **no** live integration with any government system (IEMIS, Civil Registration, National ID, banks, SMS gateways). All data in the demo/seed environment is fictional.

---

## 1. Problem Statement

Students — especially from remote or disadvantaged communities — often struggle to discover scholarship opportunities and to understand:

- which scholarships exist, their eligibility rules, required documents and deadlines;
- where their own application currently stands;
- why an application was returned, rejected or selected;
- how to appeal a decision.

Administratively, scholarship processing requires coordination between students, schools, local education authorities and selection committees, usually on paper. Records are hard to track and decisions are hard to audit.

---

## 2. Objectives

1. Centralise available scholarship information.
2. Let students/guardians discover scholarships.
3. Let students maintain a scholarship profile **without a National ID**.
4. Support online applications and document submission.
5. Let school officers verify student/school information.
6. Let local education officers perform local-level verification.
7. Support configurable eligibility criteria.
8. Support transparent, configurable selection scoring.
9. Let selection committee members review verified applications.
10. Provide application status tracking.
11. Provide decision reasons.
12. Provide an appeal mechanism.
13. Provide in-application notifications.
14. Generate digitally verifiable scholarship awards.
15. Provide QR-based award verification.
16. Maintain an audit trail of administrative actions.
17. Provide role-specific dashboards and reports.
18. Support assisted applications for students who cannot apply themselves.

---

## 3. Key Features

- Public scholarship listing with search and filters (level, grade, district, deadline).
- Rule-based **"Potentially Suitable"** eligibility matching (never a final eligibility claim).
- Full application lifecycle with controlled status transitions.
- Private document upload/download behind authorization checks.
- School verification and local education verification with remarks and returns.
- Configurable weighted selection scoring with committee decisions and reasons.
- Appeals with supporting documents and review decisions.
- Database notifications for all important workflow events.
- Digital award letter (PDF) with QR code and public verification page.
- Disbursement status tracking (administrative only — no real payments).
- Immutable audit log of important actions.
- Role-specific dashboards built from real database data (never fake statistics).
- **Fully bilingual (English ⇄ Nepali):** every screen, form, validation message, status badge, date and seeded scholarship description switches with the language toggle. Nepali is Devanagari script, no mixed-language screens.


---

## 4. User Roles

| Role | Capabilities |
|------|--------------|
| **Student / Guardian** | Manage profile, discover scholarships, apply, upload documents, track status, appeal, view awards. Student and guardian are separate account types sharing one applicant dashboard. |
| **School Officer** | Verify enrollment/grade/documents for their own school, return applications with remarks, assist applicants. |
| **Local Education Officer** | Verify local criteria for their own local education unit, approve/return, assist applicants. |
| **Selection Committee Member** | Review verified applications, view/calculcate scores, record SELECTED / WAITLISTED / REJECTED with reasons. |
| **Administrator** | Manage users, schools, local units, scholarships, criteria, documents, publishing, committee members, reports, audit logs, settings. |

All authorization is enforced server-side (role middleware + service-level jurisdiction checks). Client-side role claims are never trusted.

---

## 5. System Workflow

```text
Discovery → Application → Verification → Selection → Appeal → Award → Tracking → Accountability
```

Status flow:

```text
DRAFT → SUBMITTED → SCHOOL_VERIFICATION → LOCAL_VERIFICATION → UNDER_REVIEW
      → SELECTED / WAITLISTED / REJECTED → AWARDED → DISBURSEMENT_CONFIRMED

SCHOOL_VERIFICATION / LOCAL_VERIFICATION → RETURNED_FOR_CORRECTION → SUBMITTED
REJECTED → APPEALED → UNDER_REVIEW → (appeal APPROVED/REJECTED)
```

Invalid transitions (e.g. `DRAFT → AWARDED`) are rejected server-side.

---

## 6. Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2+, Laravel 12 (monolith), Eloquent ORM |
| Frontend | Laravel Blade, Tailwind CSS 4, Alpine.js |
| Database | MySQL 8+ (developed against MariaDB 10.4 via XAMPP) |
| Libraries | barryvdh/laravel-dompdf (award letter), endroid/qr-code (QR verification, needs the PHP **GD** extension) |
| Testing | PHPUnit 11 / Laravel Feature Tests |
| Build | Vite, npm |

**Not used (by design):** React, Vue, Angular, Next.js, Node backends, MongoDB, TypeScript, microservices, GraphQL, blockchain, AI/ML, biometrics, WebSockets, third-party payment/SMS services.

---

## 7. Architecture

```text
Browser
  ↓
Laravel Routes (web.php)
  ↓
Auth / Middleware (auth, role, locale) / service-level authorization
  ↓
Controllers (thin)
  ↓
Form Requests (validation)
  ↓
Services (Scholarship, Application, Verification, Selection, Appeal,
          Award, Notification, AuditLog, Dashboard)
  ↓
Eloquent Models
  ↓
MySQL
```

---

## 8. Database Overview

Core tables: `users`, `guardians`, `students`, `schools`, `local_education_units`,
`scholarships`, `scholarship_criteria`, `scholarship_eligibility_rules`,
`scholarship_documents`, `scholarship_committee`, `applications`,
`application_documents`, `verifications`, `criterion_scores`,
`selection_decisions`, `appeals`, `awards`, `user_notifications`,
`audit_logs`, plus framework tables (`sessions`, `cache`, `jobs`).

See `AGENT.md` for entity relationships.

---

## 9. Installation

Requirements: PHP 8.2+ (with `pdo_mysql`, `mbstring`, `openssl`, `gd`/`imagick` optional), Composer, Node 18+, MySQL 8+ (or MariaDB 10.4+).

```bash
git clone https://github.com/Reetu-Dhakal/Shiksha-Sahayata.git
cd Shiksha-Sahayata
composer install
cp .env.example .env
php artisan key:generate
npm install
```

Create the database, then migrate and seed:

```bash
mysql -u root -e "CREATE DATABASE shiksha_sahayata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
npm run build
```

---

## 10. Environment Configuration

Key `.env` values:

```dotenv
APP_NAME="Shiksha Sahayata"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shiksha_sahayata
DB_USERNAME=root
DB_PASSWORD=
```

- Uploaded documents are stored on the **private** `local` disk (`storage/app/private`), never in `public/`.
- Never commit `.env` or real credentials.

---

## 11. Language (English / Nepali)

The whole interface is bilingual. Switch with the **language button** in the header (or `POST /locale/{locale}`); the choice is stored in the session and applied by the `SetLocale` middleware (`en` default, `np` = Nepali).

```text
lang/en/*.php   # English (source of truth for feature-test strings)
lang/np/*.php   # Nepali (Devanagari)
lang/np.json    # JSON translations
```

- All UI text lives in `lang/{en,np}/*.php` — no hardcoded strings in Blade or PHP.
- Scholarship and its criteria/rules/documents carry optional Nepali columns (`title_np`, `description_np`, `provider_np`, `name_np`), edited in the admin scholarship form and rendered automatically from the accessors when the locale is `np`.
- Display dates use the `format_date()` helper so Nepali month names render correctly; machine formats (`Y-m-d`) are unchanged.
- Notifications, enum labels and statuses are translated at read time, so the reader's language wins.
- Deliberate English-only leftovers: the PDF award letter (dompdf has no Devanagari font), CSV export headers/rows, and stored audit-log descriptions.

---

## 12. Running the Application


```bash
php artisan serve          # http://localhost:8000
npm run dev                # Vite dev server (hot reload) during development
```

For a production-style build: `npm run build && php artisan optimize`.

---

## 13. Testing

```bash
php artisan test
# or
vendor/bin/phpunit
```

Tests cover authentication, role/jurisdiction authorization, application workflow
transitions, duplicate application prevention, deadline enforcement, selection
scoring, appeals, award generation and public verification privacy.

---

## 14. Demo Accounts

All seeded accounts use the password `password`. **All seed data is fictional.**

| Role | Login |
|------|-------|
| Administrator | `admin@shikshasahayata.np` |
| School Officer (Sagarmatha Secondary School) | `school.officer@sagarmatha.edu.np` |
| Local Education Officer (Kavrepalanchok LEU) | `local.officer@kavre.gov.np` |
| Selection Committee Member | `committee@shikshasahayata.np` |
| Student | `aasha.student@mail.com` |
| Guardian | `guardian.aasha@mail.com` |

---

## 15. Project Structure

```text
app/
 ├── helpers.php               # format_date(), pick_translation()
 ├── Http/Controllers/      # Thin HTTP layer (public + Admin/)
 ├── Http/Middleware/        # Role + locale middleware
 ├── Http/Requests/         # Form Request validation
 ├── Models/                # Eloquent models
 ├── Services/              # Scholarship, Application, Verification,
 │                          # Selection, Appeal, Award, Notification,
 │                          # AuditLog, Dashboard, Student
 ├── Enums/                 # Roles, statuses, transitions, notification types
database/
 ├── migrations/            # Schema with FKs, indexes, constraints
 ├── seeders/               # Fictional demo data
 └── factories/
lang/
 ├── en/                    # English strings (UI source of truth)
 └── np/                    # Nepali strings (Devanagari)
resources/views/            # Blade components, layouts, pages per role
routes/web.php              # Public, auth and role-grouped routes
tests/Feature, tests/Unit   # PHPUnit tests
```

---

## 16. Security & Privacy

- Hashed passwords (bcrypt), CSRF protection, session security, rate-limited login.
- Form Request validation for every write endpoint.
- Middleware (`role`) plus service-level checks for every authorization decision (role **and** jurisdiction).
- Documents stored privately; downloads go through an authorized route.
- Sensitive fields (birth registration, citizenship, phone, documents) are never shown on public pages.
- Public award verification exposes only: award number, scholarship name, limited student identity, issue date, status.
- Mass-assignment protection, FK constraints, and an append-only audit log.
- Data minimisation: only information required by the scholarship workflow is collected.

---

## 17. Scope

**In scope:** discovery, applications, verification, selection scoring, appeals, notifications, awards with QR verification, disbursement status tracking, audit trail, role dashboards/reports, assisted applications.

**Out of scope (not implemented):** National ID / IEMIS / civil registration integration, biometrics, AI/ML, blockchain, real bank or wallet payment APIs, real SMS providers, native mobile apps, advanced BI, microservices.

---

## 18. Future Enhancements

Clearly labelled as **future work only** — none of these currently exist in the project:

- IEMIS integration for student/school data.
- Civil Registration integration for birth records.
- National ID integration where applicable.
- SMS gateway notifications.
- Bank / payment gateway integration for disbursement.

---

## 19. Academic Context

Shiksha Sahayata is developed as a BSc CSIT academic project. It demonstrates a complete, coherent e-governance workflow using a single Laravel application and a single MySQL database, with an emphasis on maintainability, security, privacy and honest documentation.

---

## 20. Development Progress

Incremental build log (each phase committed and pushed with passing tests):

| Phase | Delivered | Status |
|-------|-----------|--------|
| 1 | Laravel 12 skeleton, Tailwind 4 + Vite, MySQL config, README/AGENT docs, dependencies (dompdf, QR) | Done |
| 2 | Roles enum, auth (email/phone, rate-limited), role middleware, per-role dashboards, layouts/nav, locale switcher (en/np) | Done |
| 3 | Schools, local education units, guardians, students, Scholar Student ID generation, profile management, admin school/LEU CRUD | Done |
| 4 | Scholarship management: drafts, eligibility rules, weighted criteria, required documents, committee assignment, publishing preconditions | Done |
| 5 | Public scholarship discovery: listing with keyword/level/open filters, detail page with criteria, rules, documents and timeline | Done |
| 6 | Applications: draft/edit/submit with deadline enforcement, duplicate prevention, guardian applications, status timeline and tracking | Done |
| 7 | Private document upload/download with type/size validation, replace and delete, required documents enforced before submission | Done |
| 8 | Two-stage verification: school and local education officer queues, jurisdiction scoping, approve/forward and return-for-correction with remarks | Done |
| 9 | Selection: committee-scoped queues, weighted criterion scoring with live totals, reasoned SELECTED/WAITLISTED/REJECTED decisions | Done |
| 10 | Appeals: one appeal per rejection, reopen back into selection review, reasoned approve/reject outcomes visible to the applicant | Done |
| 11 | Awards: issue numbered awards with QR verification codes, step-by-step disbursement tracking, revocation, PDF award letter, public verification page | Done |
| 12 | Notifications and audit trail: workflow events notify the right actors, every state change is recorded with actor, subject and IP | Done |
| 13 | Assisted applications: school/local officers and admins fill applications on behalf of students inside their jurisdiction, flagged as assisted | Done |
| 14 | Dashboards and reports: role-specific live statistics, workflow queues on every dashboard, admin report aggregates and applications CSV export | Done |
| 15 | Final polish: documentation aligned with the delivered system (services, routes, notifications, audit, reports), full test suite + seed verification | Done |
| 16 | Full localisation: every screen, form, validation message, status/enum label, date and seeded scholarship text extracted to `lang/{en,np}`; Nepali DB content columns for scholarships; language switch on public, government and dashboard layouts; `LocaleTest` guards against mixed-language rendering | Done |

Seeded demo: 2 published scholarships (Merit-cum-Means, Remote Area Girls), 1 draft, criteria totalling 100%, required documents and an assigned selection committee.
