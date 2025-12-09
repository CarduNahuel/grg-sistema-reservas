-- Agregar columna element_type para soportar diferentes tipos de elementos en la grilla
-- Tipos: 'table' (mesa), 'stairs' (escalera), 'bathroom' (baño), 'bar' (barra), 'door' (puerta), 'wall' (pared)

USE grg_db;

ALTER TABLE tables 
ADD COLUMN element_type ENUM('table', 'stairs', 'bathroom', 'bar', 'door', 'wall', 'empty') DEFAULT 'table' AFTER table_number;

-- Para elementos que no son mesas, table_number puede ser descriptivo
-- Capacity solo aplica para element_type='table'
