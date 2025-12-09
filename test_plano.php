<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Plano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Test del Plano</h2>
        <div class="card">
            <div class="card-body">
                <?php
                // Cargar datos de la BD
                $db = new \PDO('mysql:host=localhost;port=3307;dbname=grg_db', 'root', '');
                $stmt = $db->query('SELECT * FROM tables WHERE restaurant_id = 1');
                $tables = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // Configurar variables para la partial
                $gridId = 'testGrid1';
                $onCellClick = 'testClickHandler(TABLEID)';
                $selectable = true;
                $assignedIds = [];
                $occupiedIds = [];
                
                // Incluir la partial
                include __DIR__ . '/views/partials/plano_grid.php';
                ?>
            </div>
        </div>
    </div>
    
    <script>
    function testClickHandler(tableId) {
        alert('Clicked table: ' + tableId);
    }
    </script>
</body>
</html>
