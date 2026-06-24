# Blackstone Barber — Beállítási útmutató

## Rendszerkövetelmények
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache vagy Nginx (mod_rewrite ajánlott)

---

## 1. Adatbázis létrehozása

```bash
mysql -u root -p < schema.sql
```

Ez létrehozza az adatbázist, táblákat és az alapértelmezett admin fiókot.

**Alapértelmezett admin:**
- Email: `admin@blackstonebarber.hu`
- Jelszó: `Admin1234!`

⚠️ **Bejelentkezés után azonnal változtasd meg a jelszót!**

---

## 2. Adatbázis kapcsolat konfigurálása

Szerkeszd a `php/db.php` fájlt:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'blackstone_barber');
define('DB_USER', 'a_te_db_felhasználód');
define('DB_PASS', 'a_te_jelszavad');
```

---

## 3. Web szerver konfiguráció

### Apache (.htaccess a gyökérkönyvtárba)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.html [QSA,L]
```

### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.html;
}
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    include fastcgi_params;
}
```

---

## 4. Fájlszerkezet

```
barber/
├── index.html          ← Főoldal (publikus)
├── login.html          ← Bejelentkezés
├── register.html       ← Regisztráció (csak meghívóval)
├── admin.html          ← Admin dashboard
├── admin-users.html    ← Felhasználók kezelése
├── admin-invite.html   ← Meghívó küldése
├── schema.sql          ← Adatbázis séma
├── css/
│   ├── main.css        ← Alap stílusok
│   └── admin.css       ← Admin panel stílusok
├── js/
│   ├── main.js         ← Navigáció, smooth scroll
│   ├── auth.js         ← Bejelentkezés
│   ├── register.js     ← Regisztráció
│   ├── admin.js        ← Admin közös funkciók
│   ├── users.js        ← Felhasználók tábla
│   └── invite.js       ← Meghívó kezelés
└── php/
    ├── db.php           ← PDO kapcsolat
    ├── auth.php         ← Session / jogosultság ellenőrzés
    ├── login.php        ← POST /php/login.php
    ├── logout.php       ← GET /php/logout.php
    ├── register.php     ← POST /php/register.php
    ├── validate_token.php ← GET /php/validate_token.php?token=...
    ├── invite.php       ← POST /php/invite.php
    ├── invites.php      ← GET /php/invites.php
    ├── manage_invite.php ← POST /php/manage_invite.php
    ├── users.php        ← GET /php/users.php
    ├── update_user.php  ← POST /php/update_user.php
    └── stats.php        ← GET /php/stats.php
```

---

## 5. E-mail küldés

A meghívó rendszer PHP `mail()` funkciót használ. Éles szerveren ajánlott SMTP library:

```bash
composer require phpmailer/phpmailer
```

Majd a `php/invite.php`-ban cseréld a `mail()` hívást PHPMailer-re.

---

## 6. Jelszó visszaállítás / Új admin generálása

```bash
php -r "echo password_hash('UjJelszó123!', PASSWORD_BCRYPT, ['cost'=>12]);"
```

Majd futtasd MySQL-ben:
```sql
UPDATE users SET password_hash = 'GENERÁLT_HASH' WHERE email = 'admin@blackstonebarber.hu';
```

---

## Galéria képek

A `index.html`-ben a `.gallery-placeholder` elemeket cseréld le valódi `<img>` tagekre:

```html
<div class="gallery-item large">
  <img src="images/foto1.jpg" alt="Klasszikus oldalpompadour" loading="lazy">
</div>
```

Ajánlott képméret: **800×1000px** (álló formátum), WebP formátum.
