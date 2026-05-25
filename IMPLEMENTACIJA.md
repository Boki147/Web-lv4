# 🎬 Videoteka - IMPLEMENTACIJA ZAVRŠENA

## ✅ Što je napravljen

Kompletna PHP/MySQL web-aplikacija za upravljanje filmskom videotekom sa svim zahtijevanim funkcionalnostima.

### Kreirane datoteke:

**Glavne stranice:**
- `index.php` - Početna stranica sa statistikom i izdvojenim filmovima
- `register.php` - Registracija novih korisnika
- `login.php` - Prijava sa demo admin nalogom
- `logout.php` - Sigurna odjava
- `films.php` - Prikaz svih filmova sa filtriranjem i pretragom
- `film.php` - Detalji filma, ocjenjivanje i upravljanje videotekom
- `myvideoteka.php` - Moja videoteka sa upozorenjima za niske ocjene
- `dashboard.php` - Admin panel za upravljanje filmovima

**Backend datoteke:**
- `db.php` - Konekcija na MySQL sa UTF-8 podrškдом
- `auth.php` - Autentifikacijske funkcije
- `functions.php` - Validacija, sanitizacija, pomoćne funkcije
- `setup.php` - Inicijalizacija baze i kreiranja admin korisnika

**Stilovi i datoteke:**
- `style.css` - Responzivni CSS sa mobilnom optimizacijom
- `database.sql` - SQL skripta za bazu (za backup/export)
- `README.md` - Detaljne instrukcije
- `.gitignore` - Git ignore liste

### 🔒 Sigurnosne mjere:

✅ **SQL injection zaštita** - Prepared statements sa bind_param()
✅ **Hashiranje lozinki** - PASSWORD_BCRYPT sa password_hash()
✅ **Sesije** - Upravljanje korisničkim stanjem
✅ **Sanitizacija** - htmlspecialchars() za sve output
✅ **Validacija** - Serverska validacija svih unosa

### 📊 Funkcionalnosti:

✅ **Autentifikacija**
- Registracija sa validacijom
- Prijava sa bcrypt hashom
- Admin i regular user ulog
- Demo admin: admin / admin123

✅ **Upravljanje filmovima**
- Dodavanje, uređivanje, brisanje (admin)
- Prikaz sa slikom, žanrom, godinom, trajanjem
- Filtriranje po žanru, godini, zemlji
- Pretraživanje po naslovu i opisu

✅ **Ocjenjivanje**
- Ocjene od 1-5 zvjezdica
- Prosječna ocjena sa prikazom zvjezdica
- Pregled svih ocjena za film
- Ažuriranje postojeće ocjene

✅ **Moja videoteka**
- Dodavanje filmova u videoteku
- Uklanjanje filmova
- Statistika (broj filmova, sati gledanja)
- Upozorenja za niske ocjene

✅ **Responzivni dizajn**
- Desktop, tablet i mobile
- Fleksibilni grid layout
- Optimizovani CSS

## 🚀 Pokretanje

### Korak 1: Inicijalizacija baze

Otvorite `http://localhost:8000/setup.php` i kliknite na dugme.

Ili pokrenite direktno:
```bash
mysql -u root -p < database.sql
```

### Korak 2: Pokrenite PHP server

```bash
php -S localhost:8000
```

### Korak 3: Otvorite aplikaciju

```
http://localhost:8000
```

### Korak 4: Prijavite se

**Admin:**
- Korisničko ime: `admin`
- Lozinka: `admin123`

**Obični korisnik:**
- Registrujte se na `/register.php`

## 📦 Baza podataka

Tablice:
- `users` - Registrovani korisnici
- `films` - Dostupni filmovi (8 test filmova)
- `ratings` - Ocjene korisnika (1-5)
- `myvideoteka` - Filmovi u videoteci
- `warnings` - Upozorenja za niske ocjene

## 📝 Git Commit

Kreirajte commit sa:

```bash
git add -A
git commit -m "feat: Implementacija PHP/MySQL aplikacije za upravljanje filmskom videotekom" \
  -m "- Autentifikacija korisnika sa heširanim lozinkama (bcrypt)
- CRUD operacije za filmove (admin panel)
- Filtriranje filmova po žanru, godini, zemlji
- Ocjenjivanje filmova sa prosječnom ocjenom
- Upravljanje personalnom videotekom
- Upozorenja za niske ocjene (<5.0)
- Responzivni CSS dizajn
- Zaštita od SQL injection-a (prepared statements)
- Setup skripта za inicijalizaciju baze

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
```

## 🌐 Deployment na Railway

1. Push na GitHub
2. Povežite GitHub repozitorij sa Railway
3. Postavite environment varijable:
   ```
   DB_HOST=mysql.railway.internal
   DB_USER=root
   DB_PASS=your_password
   DB_NAME=movie_videoteka
   ```
4. Pokrente `setup.php` nakon deploymenта

## ✨ Gotovo!

Web-aplikacija je kompletan sa svim zahtijevanim funkcionalnostima:
- ✅ PHP/MySQL
- ✅ Autentifikacija
- ✅ CRUD filmove
- ✅ Filtriranje
- ✅ Ocjenjivanje
- ✅ Moja videoteka
- ✅ Upozorenja
- ✅ Responzivni dizajn
- ✅ Sigurnost (SQL injection zaštita, heširanje lozinki)
