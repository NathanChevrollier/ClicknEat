<?php

// Afficher les informations de configuration de la base de donnu00e9es
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

echo "<h2>Configuration de la base de donnu00e9es :</h2>";
echo "<ul>";
echo "<li>Connexion : " . env('DB_CONNECTION') . "</li>";
echo "<li>Hu00f4te : " . env('DB_HOST') . "</li>";
echo "<li>Port : " . env('DB_PORT') . "</li>";
echo "<li>Base de donnu00e9es : " . env('DB_DATABASE') . "</li>";
echo "<li>Utilisateur : " . env('DB_USERNAME') . "</li>";
echo "</ul>";

// Lister toutes les bases de donnu00e9es disponibles
try {
    $pdo = new PDO("mysql:host=" . env('DB_HOST') . ";port=" . env('DB_PORT'), env('DB_USERNAME'), env('DB_PASSWORD'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Bases de donnu00e9es disponibles :</h2>";
    echo "<ul>";
    foreach ($databases as $database) {
        echo "<li>$database</li>";
    }
    echo "</ul>";
    
    // Vu00e9rifier les tables dans la base de donnu00e9es configurée
    try {
        $pdo = new PDO("mysql:host=" . env('DB_HOST') . ";port=" . env('DB_PORT') . ";dbname=" . env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h2>Tables dans la base de donnu00e9es " . env('DB_DATABASE') . " :</h2>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    } catch (PDOException $e) {
        echo "<h2>Erreur lors de la vu00e9rification des tables :</h2>";
        echo "<p>{$e->getMessage()}</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>Erreur de connexion u00e0 MySQL :</h2>";
    echo "<p>{$e->getMessage()}</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Informations sur la base de donnu00e9es</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        ul { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Informations sur la base de donnu00e9es</h1>
</body>
</html>
