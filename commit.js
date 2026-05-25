const { execSync } = require('child_process');
const path = require('path');
const fs = require('fs');

const projectDir = path.resolve(
  'C:/Users/Korisnik/Desktop/web-lv1.worktrees/agents-web-app-movie-management-php-mysql'
);

console.log('📁 Direktorijum:', projectDir);

try {
  process.chdir(projectDir);
  console.log('✓ Promenjen direktorijum');

  // Add all files
  console.log('\n🔄 Staging filova...');
  execSync('git add -A');
  console.log('✓ Filovi staged');

  // Check status
  const status = execSync('git status --short', { encoding: 'utf-8' });
  console.log('\nStatus:');
  console.log(status);

  // Create commit
  const message =
    'feat: Implementacija PHP/MySQL aplikacije za upravljanje filmskom videotekom';
  const body = `- Autentifikacija korisnika sa heširanim lozinkama (bcrypt)
- CRUD operacije za filmove (admin panel)
- Filtriranje filmova po žanru, godini, zemlji
- Ocjenjivanje filmova sa prosječnom ocjenom
- Upravljanje personalnom videotekom
- Upozorenja za niske ocjene (<5.0)
- Responzivni CSS dizajn
- Zaštita od SQL injection-a (prepared statements)
- Setup skripта za inicijalizaciju baze

Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>`;

  console.log('\n📝 Pravljenje commit-a...');
  execSync(`git commit -m "${message}" -m "${body}"`, { stdio: 'inherit' });
  console.log('✓ Commit kreiran');

  // Show last commit
  console.log('\n✅ Poslednja commit:');
  const lastCommit = execSync('git log --oneline -1', { encoding: 'utf-8' });
  console.log(lastCommit);

} catch (error) {
  console.error('\n❌ Greška:', error.message);
  process.exit(1);
}
