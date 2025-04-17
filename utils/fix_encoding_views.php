<?php

/**
 * Script pour corriger les problèmes d'encodage dans les fichiers de vue
 */

// Fonction pour corriger l'encodage dans un fichier
function fix_file_encoding($file_path) {
    if (!file_exists($file_path)) {
        echo "Le fichier $file_path n'existe pas.\n";
        return false;
    }
    
    // Lire le contenu du fichier
    $content = file_get_contents($file_path);
    
    // Remplacer les séquences problématiques
    $patterns = [
        '/u00e0/' => 'à', '/u00e1/' => 'á', '/u00e2/' => 'â', '/u00e3/' => 'ã', '/u00e4/' => 'ä', '/u00e5/' => 'å',
        '/u00e6/' => 'æ', '/u00e7/' => 'ç', '/u00e8/' => 'è', '/u00e9/' => 'é', '/u00ea/' => 'ê', '/u00eb/' => 'ë',
        '/u00ec/' => 'ì', '/u00ed/' => 'í', '/u00ee/' => 'î', '/u00ef/' => 'ï', '/u00f0/' => 'ð', '/u00f1/' => 'ñ',
        '/u00f2/' => 'ò', '/u00f3/' => 'ó', '/u00f4/' => 'ô', '/u00f5/' => 'õ', '/u00f6/' => 'ö', '/u00f8/' => 'ø',
        '/u00f9/' => 'ù', '/u00fa/' => 'ú', '/u00fb/' => 'û', '/u00fc/' => 'ü', '/u00fd/' => 'ý', '/u00ff/' => 'ÿ',
        '/u00c0/' => 'À', '/u00c1/' => 'Á', '/u00c2/' => 'Â', '/u00c3/' => 'Ã', '/u00c4/' => 'Ä', '/u00c5/' => 'Å',
        '/u00c6/' => 'Æ', '/u00c7/' => 'Ç', '/u00c8/' => 'È', '/u00c9/' => 'É', '/u00ca/' => 'Ê', '/u00cb/' => 'Ë',
        '/u00cc/' => 'Ì', '/u00cd/' => 'Í', '/u00ce/' => 'Î', '/u00cf/' => 'Ï', '/u00d0/' => 'Ð', '/u00d1/' => 'Ñ',
        '/u00d2/' => 'Ò', '/u00d3/' => 'Ó', '/u00d4/' => 'Ô', '/u00d5/' => 'Õ', '/u00d6/' => 'Ö', '/u00d8/' => 'Ø',
        '/u00d9/' => 'Ù', '/u00da/' => 'Ú', '/u00db/' => 'Û', '/u00dc/' => 'Ü', '/u00dd/' => 'Ý'
    ];
    
    $fixed_content = $content;
    foreach ($patterns as $pattern => $replacement) {
        $fixed_content = preg_replace($pattern, $replacement, $fixed_content);
    }
    
    // Vérifier si des modifications ont été apportées
    if ($fixed_content !== $content) {
        // Sauvegarder le fichier corrigé
        file_put_contents($file_path, $fixed_content);
        echo "Fichier $file_path corrigé.\n";
        return true;
    } else {
        echo "Aucune correction nécessaire pour $file_path.\n";
        return false;
    }
}

// Fonction pour parcourir récursivement un répertoire
function fix_directory_encoding($directory) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    $blade_files = [];
    $fixed_count = 0;
    
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $path = $file->getPathname();
            if (strpos($path, '.blade.php') !== false) {
                $blade_files[] = $path;
                if (fix_file_encoding($path)) {
                    $fixed_count++;
                }
            }
        }
    }
    
    echo "\nTotal des fichiers Blade vérifiés : " . count($blade_files) . "\n";
    echo "Fichiers corrigés : $fixed_count\n";
}

// Répertoire des vues
$views_directory = __DIR__ . '/resources/views';

echo "Correction des problèmes d'encodage dans les fichiers de vue...\n";
fix_directory_encoding($views_directory);
echo "Terminé.\n";
