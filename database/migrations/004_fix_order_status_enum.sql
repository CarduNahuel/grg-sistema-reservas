-- Add missing status values to orders table
ALTER TABLE orders MODIFY COLUMN status ENUM('enviado', 'en_preparacion', 'listo', 'entregado', 'cancelado') DEFAULT 'enviado';
