-- Crear 2 restaurantes nuevos y menú simple para restaurante 1

-- Insertar restaurantes
INSERT INTO restaurants (owner_id, name, description, address, city, state, phone, email, opening_time, closing_time, is_active, created_at, updated_at) VALUES
(2, 'La Parrilla Criolla', 'Especialistas en carnes a la parrilla y platos tradicionales argentinos. Ambiente familiar y acogedor.', 'Av. Corrientes 1234', 'Buenos Aires', 'CABA', '+54 11 4567-8901', 'contacto@parrillacriollo.com.ar', '12:00:00', '23:30:00', 1, NOW(), NOW()),
(2, 'Trattoria Bella Italia', 'Auténtica cocina italiana con pastas caseras y pizzas al horno de leña. Recetas tradicionales de la nonna.', 'Av. Santa Fe 2345', 'Buenos Aires', 'CABA', '+54 11 4567-8902', 'info@bellaitalia.com.ar', '12:00:00', '00:00:00', 1, NOW(), NOW());

-- Verificar categorías existentes para restaurante 1
SELECT * FROM menu_categories WHERE restaurant_id = 1;

-- Si no existen, crear categorías para Restaurante 1
INSERT IGNORE INTO menu_categories (restaurant_id, name, description, is_active, sort_order) VALUES
(1, 'Entradas', 'Para comenzar tu experiencia culinaria', 1, 1),
(1, 'Platos Principales', 'Nuestras especialidades de la casa', 1, 2),
(1, 'Guarniciones', 'Acompañamientos perfectos', 1, 3),
(1, 'Postres', 'El broche dulce perfecto', 1, 4),
(1, 'Bebidas', 'Para acompañar tu comida', 1, 5);

-- Productos para Restaurante 1
-- Entradas
INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Empanadas Criollas (6 unidades)', 'Jugosas empanadas de carne cortada a cuchillo, receta tradicional argentina', 1500.00, NULL, 1, 1
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Entradas' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Provoleta a la Parrilla', 'Queso provolone gratinado con orégano y aceite de oliva', 2800.00, NULL, 1, 2
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Entradas' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Tabla de Fiambres', 'Selección de quesos duros y blandos, jamón crudo, salame, aceitunas', 4500.00, NULL, 1, 3
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Entradas' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Ensalada Caesar', 'Lechuga romana, crutones, parmesano, pollo grillado y salsa caesar', 3200.00, NULL, 1, 4
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Entradas' LIMIT 1;

-- Platos Principales
INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Bife de Chorizo', 'Corte premium de 400g a la parrilla con papas fritas', 8500.00, NULL, 1, 1
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Platos Principales' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Asado de Tira', 'Costillar de res a la parrilla, porción 500g con papas fritas', 7800.00, NULL, 1, 2
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Platos Principales' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Milanesa Napolitana', 'Milanesa de ternera con jamón, queso, salsa de tomate y papas fritas', 6800.00, NULL, 1, 3
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Platos Principales' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Tallarines con Tuco', 'Pasta fresca con salsa de tomate casera y parmesano', 4500.00, NULL, 1, 4
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Platos Principales' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Ravioles de Ricota', 'Ravioles caseros rellenos de ricota con salsa bolognesa', 5800.00, NULL, 1, 5
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Platos Principales' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Pollo al Verdeo', 'Suprema de pollo grillada con crema de verdeo y papas rústicas', 5800.00, NULL, 1, 6
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Platos Principales' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Ñoquis con Salsa', 'Ñoquis caseros con tu salsa favorita', 4800.00, NULL, 1, 7
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Platos Principales' LIMIT 1;

-- Guarniciones
INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Papas Fritas', 'Papas fritas caseras crocantes', 1800.00, NULL, 1, 1
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Guarniciones' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Puré de Papas', 'Cremoso puré casero con manteca', 1600.00, NULL, 1, 2
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Guarniciones' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Ensalada Mixta', 'Lechuga, tomate, cebolla, zanahoria con aceite de oliva', 1500.00, NULL, 1, 3
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Guarniciones' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Vegetales Grillados', 'Mix de vegetales de estación a la parrilla', 2200.00, NULL, 1, 4
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Guarniciones' LIMIT 1;

-- Postres
INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Flan Casero con Dulce de Leche', 'Flan tradicional con dulce de leche y crema chantilly', 2200.00, NULL, 1, 1
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Postres' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Tiramisu', 'Clásico postre italiano con café y mascarpone', 2800.00, NULL, 1, 2
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Postres' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Helado Artesanal (3 bochas)', 'Tres bochas de helado a elección', 1800.00, NULL, 1, 3
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Postres' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Panqueques con Dulce de Leche', 'Panqueques rellenos con dulce de leche repostero', 2400.00, NULL, 1, 4
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Postres' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Volcán de Chocolate', 'Bizcocho tibio con corazón de chocolate fundido', 2600.00, NULL, 1, 5
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Postres' LIMIT 1;

-- Bebidas
INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Coca Cola 354ml', 'Lata 354ml fría', 900.00, NULL, 1, 1
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Bebidas' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Agua Mineral 500ml', 'Con o sin gas', 700.00, NULL, 1, 2
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Bebidas' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Cerveza Artesanal', 'Pinta 500ml - Variedades: Rubia, Roja, Negra, IPA', 2200.00, NULL, 1, 3
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Bebidas' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Vino de la Casa (Copa)', 'Copa 150ml - Tinto Malbec o Blanco Torrontés', 1500.00, NULL, 1, 4
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Bebidas' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Limonada Casera', 'Limonada natural con hierbas frescas y jengibre', 1200.00, NULL, 1, 5
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Bebidas' LIMIT 1;

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, 1, 'Jugo Natural', 'Jugo recién exprimido de naranja o pomelo', 1400.00, NULL, 1, 6
FROM menu_categories WHERE restaurant_id = 1 AND name = 'Bebidas' LIMIT 1;

-- Confirmar
SELECT 'Restaurantes y menú creados exitosamente' as mensaje;
SELECT COUNT(*) as total_restaurantes FROM restaurants;
SELECT COUNT(*) as total_categorias FROM menu_categories WHERE restaurant_id = 1;
SELECT COUNT(*) as total_productos FROM menu_items WHERE restaurant_id = 1;
