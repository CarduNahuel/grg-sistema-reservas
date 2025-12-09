<?php
/**
 * Load seeder data into database
 */

$seederFile = __DIR__ . '/database/seeders/001_seed_initial_data.sql';
$content = file_get_contents($seederFile);

$pdo = new PDO('mysql:host=localhost;port=3307', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec('USE grg_db');

// Split SQL statements (handle multi-line queries)
$statements = preg_split('/;[\s\n]+(?=(?:[^\']*[\']{2})*[^\']*$)/', $content);

$executed = 0;
$errors = 0;

foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    
    // Skip empty lines and comments
    if (empty($stmt) || strpos($stmt, '--') === 0) {
        continue;
    }
    
    // Skip comment lines
    if (strpos($stmt, '/*') === 0) {
        continue;
    }
    
    try {
        $pdo->exec($stmt);
        $executed++;
    } catch (Exception $e) {
        $errors++;
        echo "⚠️ Error executing statement:\n";
        echo "   " . substr($stmt, 0, 80) . "...\n";
        echo "   Error: " . $e->getMessage() . "\n\n";
    }
}

echo "✅ Database seeding complete!\n";
echo "   Statements executed: $executed\n";
if ($errors > 0) {
    echo "   Errors: $errors (but data may still be loaded)\n";
}

// Verify data was loaded
echo "\n📊 Verification:\n";
$counts = [
    'roles' => $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn(),
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'restaurants' => $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn(),
    'reservations' => $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn(),
];

foreach ($counts as $table => $count) {
    echo "   $table: $count records\n";
}

echo "\n✅ Ready to test the application!\n";
echo "   URL: http://localhost/grg\n";
echo "   Test user: cliente1@email.com / password123\n";
?>
