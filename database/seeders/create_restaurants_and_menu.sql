-- Crear 2 restaurantes nuevos
INSERT INTO restaurants (owner_id, name, description, address, city, state, phone, email, opening_time, closing_time, is_active, created_at, updated_at) VALUES
(2, 'La Parrilla Criolla', 'Especialistas en carnes a la parrilla y platos tradicionales argentinos. Ambiente familiar y acogedor.', 'Av. Corrientes 1234', 'Buenos Aires', 'CABA', '+54 11 4567-8901', 'contacto@parrillacriollo.com.ar', '12:00:00', '23:30:00', 1, NOW(), NOW()),
(2, 'Trattoria Bella Italia', 'Auténtica cocina italiana con pastas caseras y pizzas al horno de leña. Recetas tradicionales de la nonna.', 'Av. Santa Fe 2345', 'Buenos Aires', 'CABA', '+54 11 4567-8902', 'info@bellaitalia.com.ar', '12:00:00', '00:00:00', 1, NOW(), NOW());

-- Obtener IDs de los nuevos restaurantes (asumiendo que son ID 2 y 3)
SET @rest1 = 1;
SET @rest2 = 2;
SET @rest3 = 3;

-- ============================================================
-- MENÚ COMPLETO PARA RESTAURANTE 1 (existente)
-- ============================================================

-- Categorías para Restaurante 1
INSERT INTO menu_categories (restaurant_id, name, description, is_active, sort_order) VALUES
(@rest1, 'Entradas', 'Para comenzar tu experiencia culinaria', 1, 1),
(@rest1, 'Platos Principales', 'Nuestras especialidades de la casa', 1, 2),
(@rest1, 'Guarniciones', 'Acompañamientos perfectos', 1, 3),
(@rest1, 'Postres', 'El broche dulce perfecto', 1, 4),
(@rest1, 'Bebidas', 'Para acompañar tu comida', 1, 5);

-- Productos para Entradas (Restaurante 1)
INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Empanadas Criollas', 'Jugosas empanadas de carne cortada a cuchillo, receta tradicional argentina', 1500.00, NULL, 1, 1
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Entradas';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Provoleta a la Parrilla', 'Queso provolone gratinado con orégano y aceite de oliva', 2800.00, NULL, 1, 2
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Entradas';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Tabla de Fiambres', 'Selección de quesos duros y blandos, jamón crudo, salame, aceitunas', 4500.00, NULL, 1, 3
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Entradas';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Ensalada Caesar', 'Lechuga romana, crutones, parmesano, pollo grillado y salsa caesar', 3200.00, NULL, 1, 4
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Entradas';

-- Productos para Platos Principales (Restaurante 1)
INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Bife de Chorizo', 'Corte premium de 400g a la parrilla con guarnición a elección', 8500.00, NULL, 1, 1
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Platos Principales';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Asado de Tira', 'Costillar de res a la parrilla, porción 500g con guarnición', 7800.00, NULL, 1, 2
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Platos Principales';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Milanesa Napolitana', 'Milanesa de ternera con jamón, queso, salsa de tomate y papas fritas', 6800.00, NULL, 1, 3
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Platos Principales';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Pasta Casera', 'Pasta fresca del día con salsa a elección', 5500.00, NULL, 1, 4
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Platos Principales';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Pollo al Verdeo', 'Suprema de pollo grillada con crema de verdeo y papas rústicas', 5800.00, NULL, 1, 5
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Platos Principales';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Ñoquis de Papa', 'Ñoquis caseros con salsa a elección, los 29 de cada mes precio especial', 4800.00, NULL, 1, 6
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Platos Principales';

-- Productos para Guarniciones (Restaurante 1)
INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Papas Fritas', 'Papas fritas caseras crocantes', 1800.00, NULL, 1, 1
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Guarniciones';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Puré de Papas', 'Cremoso puré casero con manteca', 1600.00, NULL, 1, 2
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Guarniciones';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Ensalada Mixta', 'Lechuga, tomate, cebolla, zanahoria con aceite de oliva', 1500.00, NULL, 1, 3
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Guarniciones';

-- Productos para Postres (Restaurante 1)
INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Flan Casero con Dulce de Leche', 'Flan tradicional con dulce de leche y crema chantilly', 2200.00, NULL, 1, 1
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Postres';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Tiramisu', 'Clásico postre italiano con café y mascarpone', 2800.00, NULL, 1, 2
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Postres';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Helado Artesanal', 'Tres bochas de helado a elección', 1800.00, NULL, 1, 3
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Postres';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Panqueques con Dulce de Leche', 'Panqueques rellenos con dulce de leche repostero', 2400.00, NULL, 1, 4
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Postres';

-- Productos para Bebidas (Restaurante 1)
INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Coca Cola', 'Lata 354ml', 900.00, NULL, 1, 1
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Bebidas';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Agua Mineral', 'Con o sin gas 500ml', 700.00, NULL, 1, 2
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Bebidas';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Cerveza Artesanal', 'Pinta 500ml - Variedades disponibles', 2200.00, NULL, 1, 3
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Bebidas';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Vino de la Casa', 'Copa 150ml - Tinto o Blanco', 1500.00, NULL, 1, 4
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Bebidas';

INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order)
SELECT id, @rest1, 'Limonada Casera', 'Limonada natural con hierbas frescas', 1200.00, NULL, 1, 5
FROM menu_categories WHERE restaurant_id = @rest1 AND name = 'Bebidas';

-- ============================================================
-- OPCIONES PARA PRODUCTOS (Restaurante 1)
-- ============================================================

-- Opciones para Empanadas
SET @item_empanadas = (SELECT id FROM menu_items WHERE restaurant_id = @rest1 AND name = 'Empanadas Criollas');
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_empanadas, 'Cantidad', 'single', 1, 1);
SET @opt_cant_emp = LAST_INSERT_ID();
INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_cant_emp, '6 unidades', 0, 1, 1),
(@opt_cant_emp, '12 unidades', 1500, 1, 2),
(@opt_cant_emp, '24 unidades', 3000, 1, 3);

INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_empanadas, 'Sabor', 'single', 1, 2);
SET @opt_sabor_emp = LAST_INSERT_ID();
INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_sabor_emp, 'Carne', 0, 1, 1),
(@opt_sabor_emp, 'Pollo', 0, 1, 2),
(@opt_sabor_emp, 'Jamón y Queso', 300, 1, 3),
(@opt_sabor_emp, 'Humita', 200, 1, 4);

-- Opciones para Bife de Chorizo
SET @item_bife = (SELECT id FROM menu_items WHERE restaurant_id = @rest1 AND name = 'Bife de Chorizo');
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_bife, 'Cocción', 'single', 1, 1);
SET @opt_coccion = LAST_INSERT_ID();
INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_coccion, 'Jugoso', 0, 1, 1),
(@opt_coccion, 'A punto', 0, 1, 2),
(@opt_coccion, 'Bien cocido', 0, 1, 3);

INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_bife, 'Guarnición', 'single', 1, 2);
SET @opt_guarnicion = LAST_INSERT_ID();
INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_guarnicion, 'Papas Fritas', 0, 1, 1),
(@opt_guarnicion, 'Puré', 0, 1, 2),
(@opt_guarnicion, 'Ensalada Mixta', 0, 1, 3),
(@opt_guarnicion, 'Papas Rústicas', 400, 1, 4);

-- Opciones para Asado de Tira
SET @item_asado = (SELECT id FROM menu_items WHERE restaurant_id = @rest1 AND name = 'Asado de Tira');
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_asado, 'Cocción', 'single', 1, 1);
SET @opt_coccion_asado = LAST_INSERT_ID();
INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_coccion_asado, 'Jugoso', 0, 1, 1),
(@opt_coccion_asado, 'A punto', 0, 1, 2),
(@opt_coccion_asado, 'Bien cocido', 0, 1, 3);

INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_asado, 'Guarnición', 'single', 1, 2);
SET @opt_guarnicion_asado = LAST_INSERT_ID();
INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_guarnicion_asado, 'Papas Fritas', 0, 1, 1),
(@opt_guarnicion_asado, 'Ensalada', 0, 1, 2),
(@opt_guarnicion_asado, 'Vegetales Grillados', 600, 1, 3);

-- Opciones para Pasta Casera
SET @item_pasta = (SELECT id FROM menu_items WHERE restaurant_id = @rest1 AND name = 'Pasta Casera');
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_pasta, 'Tipo de Pasta', 'single', 1, 1);
SET @opt_tipo_pasta = LAST_INSERT_ID();
INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_tipo_pasta, 'Tallarines', 0, 1, 1),
(@opt_tipo_pasta, 'Ravioles de Ricota', 800, 1, 2),
(@opt_tipo_pasta, 'Sorrentinos', 1000, 1, 3),
(@opt_tipo_pasta, 'Ñoquis', 0, 1, 4);

INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_pasta, 'Salsa', 'single', 1, 2);
SET @opt_salsa = LAST_INSERT_ID();
INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_salsa, 'Tuco (Salsa de Tomate)', 0, 1, 1),
(@opt_salsa, 'Bolognesa', 400, 1, 2),
(@opt_salsa, 'Cuatro Quesos', 800, 1, 3),
(@opt_salsa, 'Pesto', 600, 1, 4),
(@opt_salsa, 'Fileto', 500, 1, 5);

-- Opciones para Helado
SET @item_helado = (SELECT id FROM menu_items WHERE restaurant_id = @rest1 AND name = 'Helado Artesanal');
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_helado, 'Sabores (elige 3)', 'multiple', 1, 1);
SET @opt_sabores_helado = LAST_INSERT_ID();
INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_sabores_helado, 'Chocolate', 0, 1, 1),
(@opt_sabores_helado, 'Vainilla', 0, 1, 2),
(@opt_sabores_helado, 'Frutilla', 0, 1, 3),
(@opt_sabores_helado, 'Dulce de Leche', 0, 1, 4),
(@opt_sabores_helado, 'Limón', 0, 1, 5),
(@opt_sabores_helado, 'Menta Granizada', 0, 1, 6),
(@opt_sabores_helado, 'Chocolate Amargo', 0, 1, 7);

-- Opciones para Cerveza
SET @item_cerveza = (SELECT id FROM menu_items WHERE restaurant_id = @rest1 AND name = 'Cerveza Artesanal');
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_cerveza, 'Variedad', 'single', 1, 1);
SET @opt_cerveza = LAST_INSERT_ID();
INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_cerveza, 'Rubia', 0, 1, 1),
(@opt_cerveza, 'Roja', 300, 1, 2),
(@opt_cerveza, 'Negra', 400, 1, 3),
(@opt_cerveza, 'IPA', 500, 1, 4);

-- Opciones para Vino
SET @item_vino = (SELECT id FROM menu_items WHERE restaurant_id = @rest1 AND name = 'Vino de la Casa');
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_vino, 'Tipo', 'single', 1, 1);
SET @opt_vino = LAST_INSERT_ID();
INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_vino, 'Tinto Malbec', 0, 1, 1),
(@opt_vino, 'Blanco Torrontés', 0, 1, 2),
(@opt_vino, 'Rosé', 200, 1, 3);

-- Confirmar todo
SELECT 'Menú completo creado exitosamente' as mensaje;
SELECT COUNT(*) as total_categorias FROM menu_categories WHERE restaurant_id = @rest1;
SELECT COUNT(*) as total_productos FROM menu_items WHERE restaurant_id = @rest1;
SELECT COUNT(*) as total_opciones FROM menu_item_options WHERE menu_item_id IN (SELECT id FROM menu_items WHERE restaurant_id = @rest1);
