-- Script para limpiar datos corruptos y mantener estructura
-- Ejecutar en: mysql -h localhost -u root -P 3307 grg_db < C:\xampp\htdocs\grg\clean_database.sql

-- Desactivar chequeos de foreign keys temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- Limpiar tablas de datos (mantener estructura)
TRUNCATE TABLE reservation_tables;
TRUNCATE TABLE reservations;
TRUNCATE TABLE notifications;
TRUNCATE TABLE audit_logs;

-- Reactivar chequeos
SET FOREIGN_KEY_CHECKS = 1;

-- Verificar que las tablas están vacías
SELECT COUNT(*) as 'reservations_count' FROM reservations;
SELECT COUNT(*) as 'reservation_tables_count' FROM reservation_tables;
SELECT COUNT(*) as 'notifications_count' FROM notifications;

-- Confirmar
SELECT 'Database cleaned successfully!' as status;
