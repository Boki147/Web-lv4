#!/usr/bin/env python3

import subprocess
import os

os.chdir(r'C:\Users\Korisnik\Desktop\web-lv1.worktrees\agents-web-app-movie-management-php-mysql')

try:
    # Check git log
    log = subprocess.check_output(['git', 'log', '--oneline', '-10'], encoding='utf-8')
    print('Recent commits:')
    print(log)
    print()
    
    # Stage changes
    subprocess.run(['git', 'add', '-A'], check=True)
    
    # Check status
    status = subprocess.check_output(['git', 'status', '--short'], encoding='utf-8')
    print('Git status:')
    print(status)
    print()
    
    # Get diff
    diff = subprocess.check_output(['git', 'diff', '--cached', '--stat'], encoding='utf-8')
    print('Diff stat:')
    print(diff)
    print()
    
    # Commit message
    message = "feat: Implementacija PHP/MySQL aplikacije za upravljanje filmskom videotekom"
    body = """- Autentifikacija korisnika sa heširanim lozinkama (bcrypt)
- CRUD operacije za filmove (admin panel)
- Filtriranje filmova po žanru, godini, zemlji
- Ocjenjivanje filmova sa prosječnom ocjenom
- Upravljanje personalnom videotekom
- Upozorenja za niske ocjene (<5.0)
- Responzivni CSS dizajn
- Zaštita od SQL injection-a (prepared statements)
- Setup skripта za inicijalizaciju baze"""
    
    # Commit
    subprocess.run(['git', 'commit', '-m', message, '-m', body], check=True)
    
    print('\n✅ Commit uspješan!')
    
    # Show final commit
    final = subprocess.check_output(['git', 'log', '--oneline', '-1'], encoding='utf-8')
    print('Novi commit:')
    print(final)
    
except subprocess.CalledProcessError as e:
    print(f'Error: {e}')
except Exception as e:
    print(f'Error: {e}')
