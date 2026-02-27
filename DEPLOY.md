# CertNFT Backend — Production Deployment Guide

## Server Requirements

- **PHP** >= 8.2 with extensions: `openssl`, `pdo`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`, `gd`, `sqlite3`
- **Composer** (optional — `vendor/` is included in the zip)
- **MySQL** 8.0+ (recommended) or SQLite
- **Web Server**: Apache (with `mod_rewrite`) or Nginx

---

## Step 1: Upload & Extract

```bash
# Upload cert-backend-production.zip to your server, then:
unzip cert-backend-production.zip -d /var/www/certnft
cd /var/www/certnft
```

---

## Step 2: Configure Environment

```bash
cp .env.production.example .env
nano .env   # Fill in your production values
```

**Critical values to set:**
- `APP_KEY` — generate with: `php artisan key:generate`
- `APP_URL` — your production domain
- `DB_*` — your MySQL credentials
- `PINATA_API_KEY` / `PINATA_API_SECRET`
- `SEPOLIA_RPC_URL` / `DEPLOYER_PRIVATE_KEY`

---

## Step 3: Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Step 4: Generate App Key

```bash
php artisan key:generate
```

---

## Step 5: Database Setup

### Option A: MySQL (Recommended)

```bash
# Create the database first in MySQL:
mysql -u root -p -e "CREATE DATABASE certnft;"

# Run migrations:
php artisan migrate --force
```

### Option B: SQLite (Simpler — database included)

The SQLite database is already included at `database/database.sqlite` with your existing 10 certificates. Just make sure `.env` has:

```
DB_CONNECTION=sqlite
```

And remove/comment out all other `DB_*` lines.

---

## Step 6: Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Step 7: Web Server Configuration

### Apache (with .htaccess)

Point your virtual host's `DocumentRoot` to the `public/` directory:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/certnft/public

    <Directory /var/www/certnft/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/certnft/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Step 8: SSL (Recommended)

```bash
sudo apt install certbot python3-certbot-nginx   # or python3-certbot-apache
sudo certbot --nginx -d your-domain.com
```

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 500 Internal Server Error | Check `storage/logs/laravel.log`, ensure permissions are correct |
| Class not found | Run `composer dump-autoload` |
| Migration errors | Check PHP extensions (`pdo_mysql`, `sqlite3`) |
| Blank page | Set `APP_DEBUG=true` temporarily to see errors |

---

## Updating

To update the application later:
1. Upload new files (excluding `.env` and `database/`)
2. Run `php artisan migrate --force`
3. Run `php artisan config:cache && php artisan route:cache && php artisan view:cache`
