# Deployment & Database Import Guide

For the IT/Admin team deploying the EUROPA FUSION Laravel site to a server.

## 1. Server requirements

| Component | Version / notes |
|---|---|
| PHP | 8.2 or newer (tested on 8.4) with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl` |
| Database | MySQL 8.0+ or MariaDB 10.6+ (utf8mb4) |
| Web server | Apache 2.4 (mod_rewrite) or Nginx + PHP-FPM |
| Composer | 2.x |
| Node.js | Only if you rebuild front-end assets (20+). Built assets ship in `public/build`. |
| Redis / queues | Not required (sessions, cache and queue use MySQL) |

## 2. Install the code

```bash
cd /var/www
unzip europafusion-website.zip              # creates ./europafusion
cd europafusion
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edit `.env` – at minimum:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.your-domain.com
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=europafusion
DB_USERNAME=europafusion
DB_PASSWORD=********
MAIL_MAILER=smtp            # plus MAIL_HOST / MAIL_PORT / MAIL_USERNAME / MAIL_PASSWORD / MAIL_SCHEME
MAIL_FROM_ADDRESS=no-reply@your-domain.com
ENQUIRY_NOTIFY_EMAIL=contact@europafusion.io
SESSION_SECURE_COOKIE=true  # once HTTPS is active
```

## 3. Create and import the database

```sql
-- as a MySQL administrator
CREATE DATABASE europafusion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'europafusion'@'localhost' IDENTIFIED BY 'choose-a-strong-password';
GRANT ALL PRIVILEGES ON europafusion.* TO 'europafusion'@'localhost';
FLUSH PRIVILEGES;
```

Import the export shipped in the ZIP:

```bash
mysql -u europafusion -p europafusion < database/sql/europafusion.sql
php artisan migrate --force       # safe: reports "Nothing to migrate" after the import
```

*Alternative (no SQL file):* `php artisan migrate --force --seed` builds the identical schema and content.

phpMyAdmin users: select the empty `europafusion` database → **Import** → choose
`database/sql/europafusion.sql` → Go.

## 4. Create the admin account

```bash
php artisan admin:create
# Name, email and password are prompted (min 10 chars, letters, numbers & symbols).
# Running it again with an existing email resets that admin's password.
```

The lead desk is at `https://your-domain/admin`.

## 5. Permissions & optimisation

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
php artisan optimize          # caches config, routes, views and events
```

After every later change to `.env` or code: `php artisan optimize:clear && php artisan optimize`.

## 6. Web server

Point the document root to **`public/`** (never the project root).

**Apache** – the included `public/.htaccess` handles rewrites. Example vhost:

```apache
<VirtualHost *:80>
    ServerName www.your-domain.com
    DocumentRoot /var/www/europafusion/public
    <Directory /var/www/europafusion/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx**:

```nginx
server {
    listen 80;
    server_name www.your-domain.com;
    root /var/www/europafusion/public;
    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }
    location ~* \.(?:css|js|woff2?|svg|png|jpg|ico)$ { expires 30d; access_log off; }
}
```

Enable HTTPS (e.g. `certbot --nginx` / `certbot --apache`) and then set `SESSION_SECURE_COOKIE=true`.

## 7. Email

Enquiry and B2B forms send two emails each: a notification to `ENQUIRY_NOTIFY_EMAIL` and an
acknowledgement to the applicant (`ENQUIRY_SEND_ACK=false` disables the latter). If SMTP fails,
the lead is still saved and visible in `/admin`; the failure is logged in `storage/logs`.

For slow SMTP servers set `QUEUE_CONNECTION=database` and keep a worker running
(`php artisan queue:work --tries=3`, e.g. via Supervisor or systemd).

## 8. Updating assets (only when CSS/JS changes)

```bash
npm ci
npm run build      # writes public/build
```

## 9. Smoke test after deployment

1. Open `/`, `/destinations`, `/study-in-finland`, `/germany/master`, `/services`, `/exam-prep`, `/contact`, `/b2b`.
2. Submit the contact form → success message appears → the lead shows in `/admin/enquiries` → emails arrive.
3. Submit the B2B form → appears in `/admin/partners`.
4. `/sitemap.xml` lists your production domain (comes from `APP_URL`).

## 10. Backups

```bash
mysqldump --single-transaction -u europafusion -p europafusion > europafusion-$(date +%F).sql
```

Back up the database (leads) and `.env`; everything else can be redeployed from the ZIP.
