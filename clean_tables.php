<?php
require 'vendor/autoload.php';
require 'src/Services/Database.php';
require '.env.php';

$db = \App\Services\Database::getInstance($config['database']);
$db->query('DELETE FROM tables WHERE restaurant_id = 3');
echo "Tablas del restaurante 3 eliminadas\n";
