# Backend du portfolio (PHP + PDO + MySQL)

1. Chez votre hébergeur PHP/MySQL (ex. InfinityFree), créez une base MySQL et notez : hôte, nom de la base, utilisateur, mot de passe.
2. Envoyez `index.html` et le dossier `backend/` dans le dossier public du site (htdocs / public_html).
3. Ouvrez `https://votre-site/backend/install.php`, remplissez le formulaire et validez. Il crée la table, écrit `config.php` et se supprime.
4. Vos messages : `https://votre-site/backend/admin.php`.

`index.html` envoie déjà le formulaire vers `backend/contact.php` (même site).
Sécurité : requêtes préparées PDO, validation serveur, honeypot, 3 messages / 10 min par IP (hachée), sortie échappée, session + CSRF. Utilisez HTTPS.
`schema.sql` et le `config.php` fourni servent à une installation manuelle (alternative à install.php).
