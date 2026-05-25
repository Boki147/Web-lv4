#!/usr/bin/env php
<?php
/**
 * PHP Built-in Server Launcher
 * Zamjena za Express server - koristi PHP built-in server
 */

$host = 'localhost';
$port = 8000;

// Pokreni PHP server
echo "🚀 Pokretanje PHP servera na http://$host:$port\n";
echo "Pritisnite Ctrl+C da stoprirate server.\n\n";

passthru("php -S $host:$port");
?>

app.get('/slike', (req, res) => {
  const dataPath = path.join(__dirname, 'images.json');
  const images = JSON.parse(fs.readFileSync(dataPath, 'utf-8'));

  res.render('slike', { images });
});


app.listen(PORT, () => {
  console.log(`Server pokrenut na portu ${PORT}`);
});