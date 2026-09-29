# AGENT.md — Shiksha Sahayata

Guidance for developers/agents working on this repository. **Read this before changing code.**

---

## Project Overview

**Shiksha Sahayata — Digital Scholarship Management and Student Assistance System.**

A Nepal-focused G2C e-governance **academic prototype** (BSc CSIT). It centralises scholarship discovery and drives a transparent, trackable workflow:

```text
Discovery → Application → Verification → Selection → Appeal → Award → Tracking → Accountability
```

Not an official government platform. No live government integration exists or may be claimed.

---

## Problem

Students (especially remote/disadvantaged) cannot easily find scholarships, understand eligibility/documents/deadlines, track application progress, or learn why a decision was made or how to appeal. Schools, local education units and committees coordinate on paper, which is slow and hard to audit.

---

## Goals

1. Centralised scholarship information with public discovery/search.
2. Student profile **without National ID** + generated Scholar Student ID.
3. Online applications with private document submission.
4. Two-stage verification (school → local education unit).
5. Configurable eligibility rules and configurable weighted selection scoring.
6. Committee decisions with reasons; status tracking; appeals.
7. Database notifications, QR-verifiable awards, disbursement status tracking, audit trail, role dashboards/reports, assisted applications.

---

## Scope

### Included
Public listing, profile, applications, document upload, school/local verification, weighted scoring, selection decisions, appeals, notifications, award + QR verification page, disbursement status, audit logs, dashboards, admin CRUD (users/schools/local units/scholarships/criteria/documents/committee), fictional seed data, feature tests.

### Out of Scope — DO NOT IMPLEMENT
National ID / IEMIS / civil registration integrations, biometric or facial recognition, AI/ML or recommendation engines, blockchain, real bank/eSewa/Khalti/government payment APIs, real SMS providers, GraphQL, microservices, React/Vue/Angular/Next.js/Node backends, MongoDB, TypeScript, WebSockets, native mobile apps, POS, generic complaint management, advanced BI, dark-only/flashy commercial UI.

---

## Technology

- PHP 8.2+, **Laravel 12** monolith, Eloquent.
- Blade + **Tailwind CSS 4** (Vite plugin) + **Alpine.js** for small UI interactions (no chart library).
- MySQL (dev: MariaDB 10.4 via XAMPP, DB `shiksha_sahayata`, user `root`, empty password).
- PHP **GD extension is required** (award letter QR codes are rendered as PNG).
- `barryvdh/laravel-dompdf` (award letter PDF), `endroid/qr-code` (QR codes).
- PHPUnit 11 feature/unit tests. Vite build (`npm run build`).

One app + one database. No new frameworks/packages without approval.

---

## Architecture

```text
Routes → Middleware (auth + role) → Controllers (thin)
      → Form Requests → Services (authorization + status transitions) → Eloquent → MySQL
```

Services (only where logic is real, avoid file bloat): `ScholarshipService`, `ApplicationService`, `VerificationService`, `SelectionService`, `AppealService`, `AwardService`, `NotificationService`, `AuditLogService`, `DashboardService`, `StudentService`.

There are **no Policies/Gate classes**: authorization lives in the `role` middleware plus the service methods (`canActFor`, `canReview`, `canView`, `inJurisdiction`, …). Enums in `app/Enums` define roles and statuses; `ApplicationStatus::transitions()` plus the service checks are the **single source of truth** for allowed status transitions. Never change an application status with a direct ad-hoc `update(['status' => ...])`; go through the owning service.

---

## Localization (en / np)

The UI is fully bilingual: `en` (English, default) and `np` (Nepali, Devanagari). `SetLocale` reads `session('locale')`; `POST /locale/{locale}` (the header toggle) switches it. **A screen is never mixed-language.**

Rules:

1. **No hardcoded UI text** in Blade or PHP — always `__('group.key')` / `trans_choice()`; group the keys logically in the file's own `lang/en/<group>.php` + `lang/np/<group>.php`.
2. English values must stay **byte-identical** to what the tests assert (tests run in `en`). Nepali values are formal/administrative Nepali, no English words, no `नेपाली / English` pairs.
3. Display dates with `format_date($date, 'j M Y')` (`app/helpers.php`) so Nepali month names render; never change machine formats (`Y-m-d` inputs, `Y-m-d H:i` filenames/CSV).
4. Print model accessors and enum helpers as-is — `$scholarship->title|provider|description`, `$application->status->label()`, `$scholarship->gradeRangeLabel()`, `$rule->label()` already translate at read time.
5. DB content has Nepali columns: `scholarships.title_np|description_np|provider_np`, `scholarship_criteria.name_np|description_np`, `scholarship_eligibility_rules.description_np`, `scholarship_documents.description_np` (admin scholarship form). Use `getRawOriginal('title')` when you need the stored English value (form re-display, CSV).
6. Notifications store enum **values** and raw titles in `params`; `Notification::renderParams()` localises them for the reader.
7. Validation messages: use framework keys; custom messages go through `__()` (Form Request `messages()`), never English literals.
8. The language toggle always shows `__('common.switch_language')` — never a hardcoded "Nepali"/"English" label.
9. Deliberate English-only: PDF award letter (dompdf has no Devanagari font), CSV export headers/rows, audit-log `description` records.
10. Every `lang` key referenced from code must exist in **both** locales (`lang/np` mirrors `lang/en` key-for-key); `tests/Feature/LocaleTest.php` asserts `en` pages contain no Devanagari and `np` pages render Nepali.

---

## Folder Structure

```text
app/helpers.php                format_date(), pick_translation()
app/Enums/                     Role, ApplicationStatus, ScholarshipStatus, VerificationStage/Status, Decision,
                               AppealStatus, AwardStatus, DisbursementStatus, NotificationType, DocumentType...
                               (labels translate via lang/en|np/enum.php + status.php)
app/Http/Controllers/          Auth, Dashboard, Public Scholarship, Application(+Document), Verification,
                               Selection, Appeal, Award, AwardVerification, Notification, Profile, Locale
app/Http/Controllers/Admin/    Scholarship, School, LocalEducationUnit, Award, AuditLog, Report
app/Http/Middleware/           CheckRole (comma-separated roles) + SetLocale
app/Http/Requests/             Form Request validation per write action
app/Models/                    User, Guardian, Student, School, LocalEducationUnit, Scholarship,
                               ScholarshipCriterion, EligibilityRule, RequiredDocument, ScholarshipCommittee,
                               Application, ApplicationDocument, Verification, CriterionScore, SelectionDecision,
                               Appeal, Award, Notification, AuditLog
app/Services/                  Business logic (see Architecture above)
database/migrations|seeders    Schema + fictional demo data (seeded scholarship text includes Nepali columns)
lang/en|np                     All UI strings: common, nav, auth, home, layout, scholarship, verify,
                               application, profile, award, dashboard, activity, workflow, admin,
                               status, enum, notification, months, validation, pagination
resources/views/               layouts, components, public pages, role dashboards, partials/nav
routes/web.php                 Public + auth + role-grouped routes
tests/Feature                  Feature tests for every phase
```

---

## Roles & Permissions

Role values in `users.role` are **lowercase strings** (`App\Enums\Role` cases are uppercase):

| Role value | Jurisdiction | Notes |
|---|---|---|
| `admin` | system | Manages everything, reports, audit logs. |
| `student` | own profile/applications | The student applicant account. |
| `guardian` | linked students (`students.guardian_id`) | Guardian account; shares the student/guardian dashboard. |
| `school_officer` | own `users.school_id` | Verification for that school's applications only. |
| `local_officer` | own `users.local_education_unit_id` | Verification within that LEU only. |
| `committee` | scholarships they are assigned to (`scholarship_committee`) | Reviews/decides only after both verifications pass. |

- Officers may also create **ASSISTED** applications for students of their jurisdiction.
- Ordinary users can never act as officials. All checks are server-side.

---

## Database (important relationships)

```text
users 1—0..1 guardians ─ students ─ schools
students 1—* applications *— scholarships
scholarships 1—* scholarship_criteria | scholarship_eligibility_rules | scholarship_documents | scholarship_committee
applications 1—* application_documents
applications 1—* verifications (stage: SCHOOL | LOCAL)
applications 1—* criterion_scores *— scholarship_criteria
applications 1—* selection_decisions *— users (committee member)
applications 1—* appeals
applications 1—0..1 awards
users 1—* audit_logs (append-only, table `audit_logs`)
users 1—* user_notifications (custom `Notification` model: type, params, link, read_at)
```

- `applications` unique key `(student_id, scholarship_id)` — duplicate prevention.
- `students.scholar_student_id` unique (system ID, e.g. `SS-2026-0001`).
- Award `verification_code` unique; public page `/verify/award/{code}`.
- Documents live on the **private** disk; downloads go through an authorized route.

---

## Student Identity

**No National ID required.** Identity = birth registration number (optional) + name (+ `name_np`) + DOB + gender + guardian + school enrollment + grade + category. The system issues a **Scholar Student ID** (`scholar_student_id`) which is an internal application identifier only — never present it as a government identity document. `guardians.citizenship_number` is optional, stored privately, never exposed publicly.

---

## Application Workflow

```text
DRAFT → SUBMITTED → SCHOOL_VERIFICATION → LOCAL_VERIFICATION → UNDER_REVIEW
UNDER_REVIEW → SELECTED | WAITLISTED | REJECTED
SELECTED → AWARDED → DISBURSEMENT_CONFIRMED
SCHOOL_VERIFICATION|LOCAL_VERIFICATION → RETURNED_FOR_CORRECTION → SUBMITTED
REJECTED → APPEALED → UNDER_REVIEW (appeal APPROVED) | REJECTED (appeal REJECTED)
```

Rules:
- Only publishable scholarships inside their deadline accept new submissions.
- Required documents must be uploaded before submit.
- Transitions validated in `ApplicationStatus::canTransitionTo()` + service checks; invalid (e.g. `DRAFT → AWARDED`) must fail.

---

## Verification

Two stages, each with `PENDING | VERIFIED | RETURNED | REJECTED`, verifier, remarks, timestamp — stored in `verifications` with `stage` = `SCHOOL` or `LOCAL`.
- School officer: enrollment, grade, school documents (own school only).
- Local officer: local criteria within own LEU only.
- `VERIFIED` on school stage → application moves to `LOCAL_VERIFICATION`; local `VERIFIED` → `UNDER_REVIEW`.

---

## Selection

- `scholarship_criteria` per scholarship: name, `weight`, `maximum_score`, order. Example weights are **examples only**, not government rules.
- Committee enters a score per criterion; weighted total = `Σ (score / maximum_score × weight)`.
- Stored in `criterion_scores` (score, scorer, notes); the weighted total is computed on the fly by `Application::weightedTotal()` — there is no stored total column. Decisions are single rows in `selection_decisions` (`application_id` unique, decision, member, reason, decided_at).
- **The system calculates; humans decide.** Any override of the calculated score/recommendation requires a reason. Applicant sees decision + reason.

---

## Appeals

Only `REJECTED` applications show an appeal option. `appeals`: reason, optional supporting document, `SUBMITTED → UNDER_REVIEW → APPROVED | REJECTED`, reviewer, remarks, timestamp. Only the owning student/guardian may submit; only authorized officers may review. Approval returns the application to `UNDER_REVIEW`; rejection restores `REJECTED`.

---

## Awards

`AwardService` creates the award when an application is `SELECTED` and approved: `award_number`, unique `verification_code`, `issued_date`, status, `disbursement_status` (`NOT_STARTED → PROCESSING → RELEASED → RECEIVED → CONFIRMED`, administrative tracking only — no real payments). Award letter = PDF with QR pointing at `/verify/award/{verification_code}`. Public page reveals only award number, scholarship name, limited student identity, issue date, status — **never** birth registration, citizenship, phone or documents.

---

## Notifications

In-app notifications only (no SMS/mail providers). Rows live in `user_notifications` with a `type` (`App\Enums\NotificationType`), a `params` JSON payload, an optional `link` and `read_at`. Titles/bodies are rendered **at view time** from `lang/{en,np}/notification.php`, so the language follows the reader, not the writer.

Wired events: application submitted (→ school officers, admins), returned for correction (→ submitter), selection decision (→ submitter), appeal submitted (→ committee + admins), appeal decided (→ appellant), award issued / revoked / disbursement confirmed (→ submitter). `NotificationController` lists them and supports mark-one/mark-all as read; the student dashboard shows the latest unread ones.

---

## Security & Privacy

- bcrypt passwords; CSRF; Form Requests; rate-limited login; HTTPS-ready config.
- Middleware (`role`, `CheckRole`) + service-level checks for every decision; never trust client-side roles.
- Private document storage + authorized download route (validate mime/size/ownership).
- Students see only their own data; school officers only their school; local officers only their LEU; committee only assigned scholarships & selection-relevant fields.
- Sensitive fields never in public routes or logs. No secrets in git. Audit logs are append-only (no user edit/delete).

---

## Testing (required)

- Auth: valid/invalid login, logout, protected routes.
- Authorization: student→other student's data blocked; school officer→other school blocked; committee→admin blocked; unauthorized document download blocked.
- Applications: duplicate blocked; expired scholarship blocked; required docs enforced; valid/invalid submission.
- Workflow: `DRAFT → SUBMITTED` ok; `DRAFT → AWARDED` fails.
- Selection: weighted scoring math; committee authorization; decision recording.
- Appeals: eligibility, ownership, status workflow.
- Awards: generation, unique code, QR route, public page leaks no sensitive data.
- Notifications/audit: correct recipients, mark-as-read, append-only audit rows with actor + subject.
- Assisted applications: officer jurisdiction enforced; applicants cannot see other students' rows.
- Dashboards/reports: live aggregates, admin-only reports + CSV export.
- Localization: `tests/Feature/LocaleTest.php` — `en` pages contain no Devanagari, `np` pages render Nepali and no English labels.

Run: `php artisan test` (feature tests, in-memory SQLite), then `vendor/bin/pint` and `php artisan migrate:fresh --seed` against MySQL before committing.

---

## Development Rules

1. Read the existing code before modifying it.
2. Do not rewrite working features unnecessarily.
3. Do not expand scope without approval.
4. Do not invent external government integrations.
5. Do not expose sensitive student information.
6. Do not bypass authorization.
7. Validate all user input.
8. Add tests for new important functionality.
9. Update `README.md`/`AGENT.md` when architecture or features change.
10. Keep the UI simple, readable, mobile responsive, public-service style.
11. Use real database data for dashboards — **never fake statistics**.
12. Never commit secrets (`.env`, keys, credentials).
13. Follow Laravel conventions (PSR-12, Eloquent relationships, route/model binding).
14. Keep business logic in services; keep controllers thin.
15. Never hardcode UI text — add `lang/{en,np}` keys instead (see Localization), and keep English values byte-identical.

## Git Workflow

Before starting a task:

```bash
git status && git branch --show-current && git remote -v
```

After each completed task: run tests → review `git diff` → check for secrets → update docs if needed → commit with a conventional message (`feat:`, `fix:`, `test:`, `docs:`) → push. Never force-push. If a push fails, keep the local commit and report the exact error.

## UI Conventions

Clean government-service style: readable forms, obvious actions, clear status badges, clear validation messages, consistent spacing, meaningful empty states, responsive tables, low-bandwidth friendly. No huge heroes, gradients, glassmorphism, fake stats, decorative animations or giant typography.
