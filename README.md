# EUROPA FUSION – Study Abroad Website (Laravel)

A full rebuild and redesign of [europafusion.io](https://www.europafusion.io/) as a Laravel 13
application: a Germany & Europe study-abroad advisory site with 23 public pages, working enquiry
and partner forms backed by MySQL, email notifications and a password-protected lead desk.

- **Deployment guide (servers, Apache/Nginx, SSL, email):** [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md)
- **Content model for country / pathway pages:** [`docs/CONTENT_SCHEMA.md`](docs/CONTENT_SCHEMA.md)
- **Database export:** [`database/sql/europafusion.sql`](database/sql/europafusion.sql)

---

## 1. What is included

| Module | URL(s) | Notes |
|---|---|---|
| Home / About | `/` (`/about` redirects) | Hero, university marquee, about, mission & vision, what we do, 5-stage approach, destinations |
| Destinations | `/destinations` | 9 country cards, "View details" modal per country, profile-evaluation call to action |
| Germany hub | `/study-in-germany` | New landing page linking the four Germany pathways |
| Country pages (8) | `/study-in-{austria,finland,italy,latvia,lithuania,netherlands,poland,spain}` | Rendered from JSON content blocks stored in `pages.sections` |
| Germany pathways (4) | `/germany/{studienkolleg,bachelor,master,medicine}` | Same block renderer |
| Services | `/services` (+ `#counselling`, `#visa-guidance`, …) | 11 services in two groups, sticky section navigation |
| Exam Prep | `/exam-prep` (`/exams` redirects) | Benefits, 6 language tests, 5 standardized tests, FAQ |
| Contact | `/contact` | Enquiry form (general / counselling / evaluation) |
| B2B Partner | `/b2b` | Partner registration form |
| Legal | `/privacy-policy`, `/terms-conditions`, `/disclaimer`, `/refund-policy` | Old short URLs redirect |
| Enquiry modal | every page | "Book Free Counselling" / "Free Profile Evaluation" buttons |
| Admin lead desk | `/admin` | Login, dashboard, enquiries & partner requests (filter, search, status, notes, CSV export, delete) |
| SEO | `/sitemap.xml`, `/robots.txt` | Canonical URLs, Open Graph image, JSON-LD organisation data |

Every form validates on the server, stores its submission in MySQL, emails the team
(`ENQUIRY_NOTIFY_EMAIL`), sends an acknowledgement to the applicant, and is protected by
CSRF, a honeypot field and rate limiting (5 per minute / 40 per day per IP). The forms also
work without JavaScript.

## 2. Technology

- PHP 8.2+ (developed on 8.4), Laravel 13, MySQL 8 (MariaDB 10.6+ also works)
- Blade templates, Bootstrap 5.3 (Sass, customised), Bootstrap Icons, vanilla JavaScript (ES modules)
- Vite 8 for asset bundling; self-hosted Fraunces + Manrope fonts (no Google Fonts requests)
- Hand-made SVG illustrations, flags and landmark skylines in `public/images`

## 3. Quick start (local machine)

Requirements: PHP 8.2+ with `pdo_mysql`, `mbstring`, `xml`, `curl`, `fileinfo`; Composer 2; MySQL 8;
Node.js 20+ (only needed if you change CSS/JS – compiled assets are already in `public/build`).

```bash
# 1. Dependencies
composer install
npm install && npm run build        # optional – public/build is already included

# 2. Environment
cp .env.example .env
php artisan key:generate
#   edit .env: APP_URL, DB_* credentials, APP_ENV=local, APP_DEBUG=true for local work

# 3. Database – EITHER import the SQL export …
mysql -u root -p -e "CREATE DATABASE europafusion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p europafusion < database/sql/europafusion.sql
#    … OR build it from migrations + seeders (same result)
php artisan migrate --seed

# 4. Create the first admin account (prompts for name, email, password)
php artisan admin:create

# 5. Run
php artisan serve            # http://127.0.0.1:8000
```

## 4. Database

| Table | Purpose |
|---|---|
| `destinations` | 9 study destinations: card text, tuition, living cost, work rights, highlights, top universities |
| `pages` | Country & pathway pages (`sections` JSON) and the four legal pages (`body` HTML) |
| `services` | The 11 services (two groups) |
| `exams`, `faqs` | Exam-prep content and its FAQ |
| `universities` | Home page university strip |
| `enquiries` | Counselling, profile-evaluation and contact submissions (+ status & internal notes) |
| `partner_applications` | B2B partner registrations (+ status & internal notes) |
| `users` | Admin accounts (`is_admin = 1`) |
| `sessions`, `cache`, `jobs`, … | Laravel framework tables |

`database/sql/europafusion.sql` contains the full schema, all website content and the migration
log. It intentionally contains **no admin accounts and no form submissions**; create the admin with
`php artisan admin:create` after importing.

Re-running `php artisan db:seed` is safe: content is upserted from `database/seeders/content/`
(JSON + HTML files) and leads are never touched.

## 5. Editing content

- **Company details** (phone, WhatsApp, email, office address, social links, notification inbox):
  `.env` (`SITE_*`, `SOCIAL_*`, `ENQUIRY_*`) and `config/site.php`. Run `php artisan config:clear` after changes.
- **Country & pathway pages:** edit `database/seeders/content/pages/*.json` (schema in
  `docs/CONTENT_SCHEMA.md`), then `php artisan db:seed --force`.
- **Destinations, services, exams, universities:** the JSON files in `database/seeders/content/`.
- **Legal pages:** `database/seeders/content/legal/*.html`.
- **Design:** Sass in `resources/scss` (design tokens in `_tokens.scss`), JavaScript in
  `resources/js/modules`, then `npm run build`.

## 6. Admin lead desk

`/admin` → sign in with an account created by `php artisan admin:create` (run it again with the
same email to reset a password). Features: dashboard KPIs, enquiries by type/country, filter and
search, status workflow (New → Contacted → In progress → Closed), internal notes, CSV export
(Excel-safe), delete.

## 7. Tests

```bash
# uses a separate database named in phpunit.xml (europafusion_testing) – create it first
mysql -u root -p -e "CREATE DATABASE europafusion_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan test
```

42 feature tests cover every public page, redirects, 404s, sitemap/robots, enquiry & partner
validation/storage/emails, honeypot, rate limiting, admin authentication and lead management.

## 8. Project structure (key files)

```
app/Http/Controllers/          Public page + form controllers
app/Http/Controllers/Admin/    Lead desk (auth, dashboard, enquiries, partners)
app/Http/Requests/             Form validation rules
app/Mail/ + resources/views/mail/   Notification & acknowledgement emails
app/Models/                    Destination, Page, Service, Exam, Faq, University, Enquiry, PartnerApplication
app/Console/Commands/CreateAdmin.php
config/site.php                Company / contact settings
database/migrations/           Schema
database/seeders/ContentSeeder.php + content/   All website content
database/sql/europafusion.sql  MySQL export
resources/views/pages/         Page templates
resources/views/blocks/        Content block partials for country/pathway pages
resources/views/partials/      Header (mega menus), footer, enquiry modal
resources/scss/, resources/js/ Front-end source
public/images/                 SVG illustrations, flags, skylines, icons
```

## 9. Things the business still needs to provide

1. **SMTP credentials** for real email delivery (`MAIL_*` in `.env`). Until then emails are written
   to `storage/logs/laravel.log` and leads are still saved in the database and visible in `/admin`.
2. **YouTube channel URL** (`SOCIAL_YOUTUBE`) – the original site linked to `#`; the icon is hidden until set.
3. **Verification of marketing figures** carried over from the original site (e.g. "98.4% visa rate",
   "1,200+ partners", B2B "100+ profiles guided / 3 office locations") and of fees/visa figures on country pages.
4. **University logos**, if official logos should replace the typographic university tiles
   (logos are trademarks of each university and were deliberately not copied).
