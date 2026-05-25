# 🎬 Videoteka - Web aplikacija za upravljanje filmovima

## O aplikaciji

Videoteka je web-aplikacija za upravljanje filmskom kolekcijom, ocjenjivanje filmova i upravljanje personalnom videotekom. Aplikacija je napravljena sa **PHP-om** i **MySQL** bazom podataka.

## Funkcionalnosti

✅ **Autentifikacija korisnika**
- Registracija novih korisnika
- Sigurna prijava sa heširanim lozinkama (bcrypt)
- Dva tipa korisnika: obični korisnici i administratori

✅ **Upravljanje filmovima**
- Prikaz svih dostupnih filmova
- Filtriranje po žanru, godini, zemlji
- Pretraživanje filmova

✅ **Ocjenjivanje filmova**
- Ocjenjivanje filmova ocjenom od 1 do 5 zvjezdica
- Prikaz prosječne ocjene svakog filma
- Pregled svih ocjena za film

✅ **Moja videoteka**
- Dodavanje/uklanjanje filmova iz personalne videoteke
- Pregled svih filmova u videoteci
- Statistika (broj filmova, ukupno sati gledanja)
- Upozorenja za filmove sa niskој ocjenom (< 5.0)

✅ **Admin panel**
- Dodavanje novih filmova
- Uređivanje filmova
- Brisanje filmova

## Instalacija

### Preduvjeti

- **PHP** 7.4 ili novije
- **MySQL** 5.7 ili novije (ili MariaDB)
- **Apache** sa mod_php ili **Nginx** sa PHP-FPM
- Ili **XAMPP** koji sadrži sve navedene komponente

### Koraci instalacije

1. **Preuzmite ili klonirajte repozitorij:**
   ```bash
   git clone https://github.com/Boki147/Web-lv2.git
   cd agents-web-app-movie-management-php-mysql
   ```

2. **Postavite bazu podataka:**
   - Ako koristite **XAMPP**:
     1. Pokrenite Apache i MySQL iz XAMPP Control Panel-a
     2. Otvorite `http://localhost/phpmyadmin`
     3. Prijavite se (obično: korisničko ime: `root`, lozinka: prazna)
     4. Pokrenut ćete SQL datoteku: Kliknite na "Import", odaberite `database.sql` i pokrente

   - Ako koristite **direktno PHP/MySQL**:
     ```bash
     mysql -u root -p < database.sql
     ```
     
   - **Alternativno** (najjednostavnije):
     1. Pokrenite PHP server: `php -S localhost:8000`
     2. Otvorite `http://localhost:8000/setup.php` u pregledniku
     3. Kliknite na dugme "Inicijaliziraj bazu podataka"

3. **Pokrenite aplikaciju:**
   ```bash
   php -S localhost:8000
   ```
   
   Ili koristite XAMPP i stavite datoteke u `htdocs` folder.

4. **Otvorite aplikaciju:**
   ```
   http://localhost:8000
   http://localhost/videoteka (ako koristite XAMPP)
   ```

## Korištenje

### Prijava kao običan korisnik
1. Kliknite na "Registracija"
2. Unesite podatke i kreirajte račun
3. Prijavite se sa novim računom

### Prijava kao administrator
- **Korisničko ime:** `admin`
- **Lozinka:** `admin123`

### Meni navigacije

- **Početna** - Prikaz statistike i izdvojenih filmova
- **Filmovi** - Prikaz svih dostupnih filmova sa mogućnošću filtriranja
- **Moja videoteka** - Prikaz i upravljanje vašom personalnom kolekcijom
- **Admin panel** (samo za admina) - Upravljanje svim filmovima

## Struktura datoteka

```
.
├── index.php              # Početna stranica
├── register.php           # Registracija korisnika
├── login.php              # Prijava korisnika
├── logout.php             # Odjava korisnika
├── films.php              # Prikaz i filtriranje filmova
├── film.php               # Detalji filma i ocjenjivanje
├── myvideoteka.php        # Moja videoteka
├── dashboard.php          # Admin panel
├── setup.php              # Inicijalizacija baze podataka
├── db.php                 # Konekcija na bazu
├── auth.php               # Autentifikacijske funkcije
├── functions.php          # Pomoćne funkcije
├── style.css              # Stilovi stranica
├── database.sql           # SQL skripta za bazu (za izvoz/backup)
├── package.json           # Metapodaci projekta
└── README.md              # Ovaj fajl
```

## Tehnologije

- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Backend:** PHP 7.4+
- **Baza podataka:** MySQL 5.7+
- **Sigurnost:**
  - Prepared statements za zaštitu od SQL injection-a
  - Password hashing sa bcrypt (PASSWORD_BCRYPT)
  - Sesije za upravljanje korisnicima
  - Sanitizacija unosa

## Sigurnosne mere

1. **Zaštita od SQL injection-a** - korišćeni prepared statements
2. **Hashiranje lozinki** - korishćen password_hash() sa BCRYPT algoritmom
3. **Sesije** - upravljanje korisničkim stanjem
4. **Sanitizacija** - svi korisnički unosi se sanitizuju sa htmlspecialchars()
5. **Validacija** - serverska validacija svih podataka

## Baza podataka

### Tablice

- **users** - Registrovani korisnici
- **films** - Dostupni filmovi
- **ratings** - Ocjene korisnika
- **myvideoteka** - Filmovi u videoteci korisnika
- **warnings** - Upozorenja za niske ocjene

## Deployment

Za deployment na produkciju (npr. Railway, Heroku, itd.):

1. **Postavite MySQL bazu na hosting servisu**
2. **Ažurirajte kredencijale u `db.php`:**
   ```php
   define('DB_HOST', 'your-host.example.com');
   define('DB_USER', 'your-username');
   define('DB_PASS', 'your-password');
   define('DB_NAME', 'your-database');
   ```
3. **Pokrente `setup.php` ili manual SQL skripta na produkciji**
4. **Uploadujte sve datoteke na hosting**

## Testiranje

### Test scenariji

1. **Registracija:**
   - Kreirajte novi račun sa različitim podacima
   - Provjerite validaciju (kraće od 3 karaktera, itd.)

2. **Prijava:**
   - Prijavite se sa novim računom
   - Pokušajte sa pogrešnom lozinkom

3. **Filmovi:**
   - Filtrirajte po različitim kriterijumima
   - Pretražite filmove
   - Otvorite detalje filma

4. **Ocjenjivanje:**
   - Ocijenite film
   - Ponovno ocijenite (trebao bi se ažurirati)

5. **Videoteka:**
   - Dodajte filmove
   - Uklonite filmove
   - Provjerite upozorenja za niske ocjene

6. **Admin panel:**
   - Prijavite se kao admin
   - Dodajte novi film
   - Ažurirajte film
   - Obrišite film

## Bugovi i problemi

Ako naiđete na problem:

1. Provjerite da je MySQL servis pokrenut
2. Provjerite kredencijale u `db.php`
3. Očistite cache preglednika (Ctrl+Shift+Del)
4. Provjerite PHP error log

## Licence

ISC License

## Autor

Kreirano kao dio laboratorijske vježbe 4.

---

**Napomena:** Za više informacija o laboratorijskoj vježbi, pogledajte opis u zadatku.