<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=3306', 'root', '');
    echo "✅ Connected to MySQL successfully!\n";
    
    // Get version
    $result = $pdo->query("SELECT VERSION()");
    $version = $result->fetchColumn();
    echo "MySQL Version: $version\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
