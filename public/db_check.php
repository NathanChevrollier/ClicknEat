<?php

// Configuration de la base de donnu00e9es (u00e0 adapter selon votre configuration)
$host = 'localhost';
$dbname = 'clickneat';
$username = 'root';
$password = '';

try {
    // Connexion u00e0 la base de donnu00e9es avec PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Connexion u00e0 la base de donnu00e9es ru00e9ussie !</h2>";
    
    // Vu00e9rifier la table users
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Utilisateurs dans la base de donnu00e9es :</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nom</th><th>Email</th><th>Ru00f4le</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Vu00e9rifier la table restaurants
    $stmt = $pdo->query("SELECT * FROM restaurants");
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Restaurants dans la base de donnu00e9es :</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nom</th><th>Adresse</th><th>ID Propriu00e9taire</th></tr>";
    
    foreach ($restaurants as $restaurant) {
        echo "<tr>";
        echo "<td>{$restaurant['id']}</td>";
        echo "<td>{$restaurant['name']}</td>";
        echo "<td>{$restaurant['address']}</td>";
        echo "<td>{$restaurant['user_id']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<h2>Erreur de connexion u00e0 la base de donnu00e9es :</h2>";
    echo "<p>{$e->getMessage()}</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vu00e9rification de la base de donnu00e9es</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Vu00e9rification de la base de donnu00e9es</h1>
</body>
</html>
