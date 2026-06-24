# Bukta Zoltán EV — Beállítási útmutató

## Rendszerkövetelmények
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache (mod_rewrite) vagy Nginx

---

## Architektúra

```
OOP MVC alapú, egyetlen belépési ponttal (public/index.php).
Tiszta URL-ek, PSR-4 autoload, session-alapú auth.
```

### Mappa struktúra

```
bukta-zoltan/
├── public/                 ← DocumentRoot (csak ez legyen publikus)
│   ├── index.php           ← Egyetlen belépési pont
│   ├── .htaccess           ← Clean URL + biztonsági fejlécek
│   ├── css/
│   │   ├── main.css
│   │   └── admin.css
│   └── js/
│       ├── main.js
│       ├── auth.js
│       ├── register.js
│       ├── admin.js
│       ├── users.js
│       └── invite.js
├── app/
│   ├── bootstrap.php       ← Autoloader, konstansok
│   ├── routes.php          ← Összes útvonal
│   ├── Core/
│   │   ├── Router.php      ← HTTP router
│   │   ├── Database.php    ← PDO singleton
│   │   ├── Auth.php        ← Session + jogosultság
│   │   └── Response.php    ← JSON / redirect / view helper
│   ├── Models/
│   │   ├── Model.php       ← Alap model
│   │   ├── User.php
│   │   └── Invite.php
│   ├── Controllers/
│   │   ├── PublicController.php
│   │   ├── AuthController.php
│   │   └── AdminController.php
│   └── Views/
│       ├── partials/       ← head.php, nav.php, sidebar.php
│       ├── public/         ← home.php, dashboard.php
│       ├── auth/           ← login.php, register.php
│       ├── admin/          ← dashboard.php, users.php, invite.php
│       └── errors/         ← 404.php
├── config/
│   ├── app.php
│   └── database.php        ← DB hitelesítő adatok
└── schema.sql
```

---

## 1. Telepítés

### DocumentRoot beállítása
**Fontos:** A web szerver DocumentRoot-ját a `public/` mappára kell állítani.

Apache virtualhost:
```apache
<VirtualHost *:80>
    ServerName buktazoltan.hu
    DocumentRoot /var/www/bukta-zoltan/public

    <Directory /var/www/bukta-zoltan/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Nginx:
```nginx
server {
    listen 80;
    server_name buktazoltan.hu;
    root /var/www/bukta-zoltan/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Tiltsd le a publikus mappa feletti elérést
    location ~ /\. { deny all; }
}
```

---

## 2. Adatbázis létrehozása

```bash
mysql -u root -p < schema.sql
```

**Alapértelmezett admin:**
- Email: `admin@buktazoltan.hu`
- Jelszó: `Admin1234!`

⚠️ **Bejelentkezés után azonnal változtasd meg!**

---

## 3. Konfiguráció

### `config/database.php`
```php
return [
    'host'    => 'localhost',
    'name'    => 'bukta_zoltan_ev',
    'user'    => 'db_user',
    'pass'    => 'db_pass',
    'charset' => 'utf8mb4',
];
```

### `config/app.php`
```php
return [
    'name'  => 'Bukta Zoltán EV',
    'env'   => 'production',  // 'development' a debug módhoz
    'debug' => false,
];
```

---

## 4. Útvonalak (clean URL)

| Módszer | URL                        | Leírás                    |
|---------|----------------------------|---------------------------|
| GET     | `/`                        | Főoldal                   |
| GET     | `/login`                   | Bejelentkezési oldal      |
| POST    | `/login`                   | Login feldolgozás (JSON)  |
| GET     | `/kijelentkezes`           | Kijelentkezés             |
| GET     | `/regisztracio?token=...`  | Regisztrációs oldal       |
| POST    | `/regisztracio`            | Regisztráció (JSON)       |
| GET     | `/token-ellenorzes?token=` | Token validálás (JSON)    |
| GET     | `/admin`                   | Admin dashboard           |
| GET     | `/admin/felhasznalok`      | Felhasználók              |
| GET     | `/admin/meghivo`           | Meghívó oldal             |
| GET     | `/api/admin/stats`         | Statisztikák (JSON)       |
| GET     | `/api/admin/felhasznalok`  | Felhasználók (JSON)       |
| POST    | `/api/admin/felhasznalo`   | Felhasználó módosítás     |
| POST    | `/api/admin/meghivo`       | Meghívó küldés            |
| GET     | `/api/admin/meghivok`      | Függő meghívók (JSON)     |
| POST    | `/api/admin/meghivo/kezeles` | Meghívó visszavon/újraküld |

---

## 5. E-mail (SMTP)

A `php/invite.php` helyett az `AdminController::sendInvite()` módszer tartalmazza az e-mail küldést.
PHPMailer integráció:

```bash
composer require phpmailer/phpmailer
```

Majd a `AdminController.php`-ban cseréld a `@mail()` hívást:

```php
use PHPMailer\PHPMailer\PHPMailer;
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.example.com';
// ...
```

---

## 6. Új jelszó generálása

```bash
php -r "echo password_hash('UjJelszó123!', PASSWORD_BCRYPT, ['cost'=>12]);"
```

```sql
UPDATE users SET password_hash = 'HASH' WHERE email = 'admin@buktazoltan.hu';
```
