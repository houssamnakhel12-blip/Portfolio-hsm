<?php
// À MODIFIER avec les informations de votre hébergeur
const DB_HOST = 'localhost';
const DB_NAME = 'portfolio';
const DB_USER = 'votre_utilisateur';
const DB_PASS = 'votre_mot_de_passe';
// Adresse EXACTE de votre portfolio (sans / final), ex: https://houssam.netlify.app
const ALLOWED_ORIGIN = 'https://VOTRE-PORTFOLIO.example';
// Texte aléatoire quelconque pour anonymiser les IP
const IP_SALT = 'changez-moi-par-un-texte-aleatoire';
// Mot de passe admin : générez le hash avec  php -r "echo password_hash('MonMotDePasse', PASSWORD_DEFAULT);"
const ADMIN_HASH = 'COLLEZ_ICI_LE_HASH';

function pdo(): PDO {
    return new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
