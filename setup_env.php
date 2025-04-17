<?php

// Lire le contenu du fichier .env.example
$envExample = file_get_contents('.env.example');

// Remplacer les valeurs par celles de XAMPP
$envContent = str_replace(
    [
        'APP_NAME=Laravel',
        'APP_ENV=local',
        'APP_KEY=',
        'DB_CONNECTION=sqlite',
        '# DB_HOST=127.0.0.1',
        '# DB_PORT=3306',
        '# DB_DATABASE=laravel',
        '# DB_USERNAME=root',
        '# DB_PASSWORD='
    ],
    [
        'APP_NAME="Click\'n Eat"',
        'APP_ENV=local',
        'APP_KEY=base64:'.base64_encode(random_bytes(32)),
        'DB_CONNECTION=mysql',
        'DB_HOST=127.0.0.1',
        'DB_PORT=3306',
        'DB_DATABASE=clickneat',
        'DB_USERNAME=root',
        'DB_PASSWORD='
    ],
    $envExample
);

// u00c9crire le contenu dans le fichier .env
file_put_contents('.env', $envContent);

echo "Fichier .env cru00e9u00e9 avec succu00e8s !\n";
echo "Configuration de la base de donnu00e9es :\n";
echo "- Connexion : mysql\n";
echo "- Hu00f4te : 127.0.0.1\n";
echo "- Port : 3306\n";
echo "- Base de donnu00e9es : clickneat\n";
echo "- Utilisateur : root\n";
echo "- Mot de passe : (vide)\n";

echo "\nVeuillez cru00e9er la base de donnu00e9es 'clickneat' dans phpMyAdmin si elle n'existe pas du00e9ju00e0.\n";
