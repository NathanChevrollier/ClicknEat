<?php


require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Get the database connection
$db = $app->make('db');

// Create the migrations table if it doesn't exist
$db->statement("CREATE TABLE IF NOT EXISTS migrations (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    migration VARCHAR(255) NOT NULL,
    batch INT(11) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Get all migration files
$migrationFiles = glob(__DIR__.'/database/migrations/*.php');

// Get already executed migrations
$executedMigrations = $db->table('migrations')->pluck('migration')->toArray();

// Sort migration files by name
sort($migrationFiles);

$batch = $db->table('migrations')->max('batch') + 1;
if ($batch === null) {
    $batch = 1;
}

// Execute each migration file that hasn't been executed yet
foreach ($migrationFiles as $file) {
    $migrationName = basename($file, '.php');
    
    if (!in_array($migrationName, $executedMigrations)) {
        echo "Running migration: {$migrationName}\n";
        
        try {
            // Start a transaction
            $db->beginTransaction();
            
            // Include the migration file
            $migration = require $file;
            
            // Run the up method
            $migration->up();
            
            // Add the migration to the migrations table
            $db->table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => $batch
            ]);
            
            // Commit the transaction
            $db->commit();
            
            echo "Migration {$migrationName} executed successfully.\n";
        } catch (\Exception $e) {
            // Rollback the transaction
            $db->rollBack();
            
            // Handle table already exists errors more gracefully
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "Warning: Table already exists in migration {$migrationName}. Marking as executed.\n";
                
                // Mark the migration as executed even though it failed
                $db->table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $batch
                ]);
            } else {
                echo "Error executing migration {$migrationName}: " . $e->getMessage() . "\n";
                echo "Stopping migration process.\n";
                exit(1);
            }
        }
    }
}

echo "All migrations have been executed.\n";
