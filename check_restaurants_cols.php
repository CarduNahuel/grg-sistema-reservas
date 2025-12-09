<?php
require 'bootstrap/app.php';
$config = require 'config/database.php';
$db = \App\Services\Database::getInstance($config);
$cols = $db->fetchAll('DESCRIBE restaurants');
foreach($cols as $c) {
    echo $c['Field'] . "\n";
}
