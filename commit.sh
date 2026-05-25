#!/bin/bash

cd "C:\Users\Korisnik\Desktop\web-lv1.worktrees\agents-web-app-movie-management-php-mysql" || exit 1

# Stage all changes
git add -A

# Commit with message
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

echo "✅ Commit complete"
git log --oneline -1
