-- ============================================
-- GRG - Database Seeder Script
-- Insert initial data for testing
-- ============================================

USE grg_db;

-- ============================================
-- Seed: roles
-- ============================================
INSERT INTO roles (name, description) VALUES
('SUPERADMIN', 'Administrador del sistema con acceso completo'),
('OWNER', 'Propietario de restaurante(s)'),
('RESTAURANT_ADMIN', 'Administrador de restaurante'),
('CLIENTE', 'Cliente que hace reservas');

-- ============================================
-- Seed: users
-- Password for all users: 'password123' (hashed with bcrypt)
-- ============================================
INSERT INTO users (role_id, email, password, first_name, last_name, phone, is_active, email_verified_at) VALUES
-- SuperAdmin
(1, 'admin@grg.com', '$2y$10$eUFABFa/BcpHE2YEyxNwlO8WF.2q7HJbuhFXmbGDywW7XnfPvJJh.', 'Admin', 'Sistema', '+54911-1111-1111', TRUE, NOW()),

-- Owners
(2, 'owner1@restaurant.com', '$2y$10$eUFABFa/BcpHE2YEyxNwlO8WF.2q7HJbuhFXmbGDywW7XnfPvJJh.', 'Carlos', 'Gómez', '+54911-2222-2222', TRUE, NOW()),
(2, 'owner2@restaurant.com', '$2y$10$eUFABFa/BcpHE2YEyxNwlO8WF.2q7HJbuhFXmbGDywW7XnfPvJJh.', 'María', 'López', '+54911-3333-3333', TRUE, NOW()),

-- Restaurant Admins
(3, 'admin1@restaurant.com', '$2y$10$eUFABFa/BcpHE2YEyxNwlO8WF.2q7HJbuhFXmbGDywW7XnfPvJJh.', 'Juan', 'Pérez', '+54911-4444-4444', TRUE, NOW()),
(3, 'admin2@restaurant.com', '$2y$10$eUFABFa/BcpHE2YEyxNwlO8WF.2q7HJbuhFXmbGDywW7XnfPvJJh.', 'Ana', 'Martínez', '+54911-5555-5555', TRUE, NOW()),

-- Clients
(4, 'cliente1@email.com', '$2y$10$eUFABFa/BcpHE2YEyxNwlO8WF.2q7HJbuhFXmbGDywW7XnfPvJJh.', 'Pedro', 'Rodríguez', '+54911-6666-6666', TRUE, NOW()),
(4, 'cliente2@email.com', '$2y$10$eUFABFa/BcpHE2YEyxNwlO8WF.2q7HJbuhFXmbGDywW7XnfPvJJh.', 'Laura', 'Fernández', '+54911-7777-7777', TRUE, NOW()),
(4, 'cliente3@email.com', '$2y$10$eUFABFa/BcpHE2YEyxNwlO8WF.2q7HJbuhFXmbGDywW7XnfPvJJh.', 'Diego', 'Sánchez', '+54911-8888-8888', TRUE, NOW());

-- ============================================
-- Seed: restaurants
-- ============================================
INSERT INTO restaurants (owner_id, name, description, address, city, state, postal_code, phone, email, opening_time, closing_time, is_active, requires_payment, payment_status) VALUES
-- First restaurant for owner1 (FREE)
(2, 'La Parrilla Argentina', 'Auténtica parrilla argentina con cortes premium y vinos selectos', 'Av. Corrientes 1234', 'Buenos Aires', 'CABA', 'C1043', '+54911-1234-5678', 'info@laparrilla.com', '12:00:00', '23:59:00', TRUE, FALSE, 'paid'),

-- Second restaurant for owner1 (REQUIRES PAYMENT)
(2, 'Sushi Tokyo', 'Cocina japonesa tradicional con ingredientes frescos', 'Av. Santa Fe 5678', 'Buenos Aires', 'CABA', 'C1425', '+54911-2345-6789', 'info@sushitokyo.com', '19:00:00', '23:30:00', TRUE, TRUE, 'pending'),

-- First restaurant for owner2 (FREE)
(3, 'Trattoria Italiana', 'Sabores auténticos de Italia en Buenos Aires', 'Av. Cabildo 2345', 'Buenos Aires', 'CABA', 'C1428', '+54911-3456-7890', 'info@trattoria.com', '12:00:00', '23:00:00', TRUE, FALSE, 'paid'),

-- Restaurant for testing
(2, 'El Bistró Francés', 'Cocina francesa contemporánea', 'Av. del Libertador 3456', 'Buenos Aires', 'CABA', 'C1425', '+54911-4567-8901', 'info@bistro.com', '20:00:00', '23:59:00', TRUE, FALSE, 'paid');

-- ============================================
-- Seed: restaurant_users (Multi-tenant relationships)
-- ============================================
INSERT INTO restaurant_users (restaurant_id, user_id, role) VALUES
-- Owner1 owns restaurants 1, 2, 4
(1, 2, 'OWNER'),
(2, 2, 'OWNER'),
(4, 2, 'OWNER'),

-- Owner2 owns restaurant 3
(3, 3, 'OWNER'),

-- Admin1 manages restaurant 1
(1, 4, 'RESTAURANT_ADMIN'),

-- Admin2 manages restaurant 3
(3, 5, 'RESTAURANT_ADMIN');

-- ============================================
-- Seed: tables
-- ============================================
-- La Parrilla Argentina (Restaurant 1)
INSERT INTO tables (restaurant_id, table_number, capacity, area, floor, position_x, position_y, is_available, can_be_joined) VALUES
(1, 'A1', 2, 'Interior', 1, 10, 10, TRUE, FALSE),
(1, 'A2', 2, 'Interior', 1, 10, 30, TRUE, FALSE),
(1, 'A3', 4, 'Interior', 1, 10, 50, TRUE, TRUE),
(1, 'A4', 4, 'Interior', 1, 10, 70, TRUE, TRUE),
(1, 'B1', 6, 'Terraza', 1, 50, 10, TRUE, FALSE),
(1, 'B2', 6, 'Terraza', 1, 50, 30, TRUE, FALSE),
(1, 'V1', 8, 'VIP', 2, 10, 10, TRUE, FALSE),
(1, 'V2', 10, 'VIP', 2, 10, 30, TRUE, FALSE);

-- Sushi Tokyo (Restaurant 2)
INSERT INTO tables (restaurant_id, table_number, capacity, area, floor, position_x, position_y, is_available, can_be_joined) VALUES
(2, '1', 2, 'Barra', 1, 10, 10, TRUE, FALSE),
(2, '2', 2, 'Barra', 1, 10, 20, TRUE, FALSE),
(2, '3', 2, 'Barra', 1, 10, 30, TRUE, FALSE),
(2, '4', 4, 'Salón', 1, 50, 10, TRUE, FALSE),
(2, '5', 4, 'Salón', 1, 50, 30, TRUE, FALSE),
(2, '6', 6, 'Privado', 1, 80, 10, TRUE, FALSE);

-- Trattoria Italiana (Restaurant 3)
INSERT INTO tables (restaurant_id, table_number, capacity, area, floor, position_x, position_y, is_available, can_be_joined) VALUES
(3, 'T1', 2, 'Interior', 1, 10, 10, TRUE, FALSE),
(3, 'T2', 2, 'Interior', 1, 10, 30, TRUE, FALSE),
(3, 'T3', 4, 'Interior', 1, 10, 50, TRUE, TRUE),
(3, 'T4', 4, 'Interior', 1, 10, 70, TRUE, TRUE),
(3, 'T5', 6, 'Terraza', 1, 50, 10, TRUE, FALSE),
(3, 'T6', 8, 'Terraza', 1, 50, 30, TRUE, FALSE);

-- El Bistró Francés (Restaurant 4)
INSERT INTO tables (restaurant_id, table_number, capacity, area, floor, position_x, position_y, is_available, can_be_joined) VALUES
(4, '1A', 2, 'Interior', 1, 10, 10, TRUE, FALSE),
(4, '2A', 4, 'Interior', 1, 10, 30, TRUE, FALSE),
(4, '3A', 6, 'Terraza', 1, 50, 10, TRUE, FALSE);

-- ============================================
-- Seed: reservations (Sample data)
-- ============================================
INSERT INTO reservations (restaurant_id, table_id, user_id, reservation_date, start_time, end_time, guest_count, status, special_requests, confirmed_by, confirmed_at) VALUES
-- Confirmed reservations
(1, 1, 6, '2025-12-10', '2025-12-10 20:00:00', '2025-12-10 22:00:00', 2, 'confirmed', 'Mesa cerca de la ventana', 4, NOW()),
(1, 5, 7, '2025-12-10', '2025-12-10 21:00:00', '2025-12-10 23:00:00', 6, 'confirmed', NULL, 4, NOW()),

-- Pending reservations
(1, 3, 8, '2025-12-12', '2025-12-12 19:30:00', '2025-12-12 21:30:00', 4, 'pending', 'Cumpleaños - ¿Tienen postre especial?', NULL, NULL),
(3, 13, 6, '2025-12-15', '2025-12-15 20:00:00', '2025-12-15 22:00:00', 2, 'pending', NULL, NULL, NULL),

-- Past completed reservation
(1, 2, 7, '2025-12-05', '2025-12-05 20:00:00', '2025-12-05 22:00:00', 2, 'completed', NULL, 4, '2025-12-04 10:00:00');

-- ============================================
-- Seed: notifications (Sample data)
-- ============================================
INSERT INTO notifications (user_id, reservation_id, type, title, message, is_read, email_sent, email_sent_at) VALUES
-- Client notifications
(6, 1, 'reservation_confirmed', 'Reserva Confirmada', 'Tu reserva en La Parrilla Argentina para el 10/12/2025 a las 20:00 ha sido confirmada.', TRUE, TRUE, NOW()),
(7, 2, 'reservation_confirmed', 'Reserva Confirmada', 'Tu reserva en La Parrilla Argentina para el 10/12/2025 a las 21:00 ha sido confirmada.', FALSE, TRUE, NOW()),
(8, 3, 'reservation_created', 'Reserva Creada', 'Tu reserva en La Parrilla Argentina está pendiente de confirmación.', FALSE, TRUE, NOW()),

-- Owner/Admin notifications
(2, 3, 'reservation_created', 'Nueva Reserva', 'Nueva reserva pendiente en La Parrilla Argentina para el 12/12/2025.', FALSE, TRUE, NOW()),
(2, 4, 'reservation_created', 'Nueva Reserva', 'Nueva reserva pendiente en Trattoria Italiana para el 15/12/2025.', FALSE, FALSE, NULL);

-- ============================================
-- Seed: payments (Sample data)
-- ============================================
INSERT INTO payments (user_id, restaurant_id, amount, currency, status, payment_method, transaction_id, paid_at) VALUES
-- Successful payment for second restaurant
(2, 2, 50.00, 'USD', 'completed', 'credit_card', 'TXN_12345_MOCK', '2025-12-01 10:30:00'),

-- Pending payment (stub)
(2, 2, 50.00, 'USD', 'pending', NULL, NULL, NULL);

-- ============================================
-- End of Seeder Script
-- ============================================
