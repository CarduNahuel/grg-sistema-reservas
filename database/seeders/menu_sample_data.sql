-- Sample menu data for testing
-- Run this after migration 003

-- Add categories for restaurant #1 (adjust restaurant_id as needed)
INSERT INTO menu_categories (restaurant_id, name, description, is_active, sort_order) VALUES
(1, 'Entradas', 'Para comenzar tu experiencia', 1, 1),
(1, 'Platos Principales', 'Nuestras especialidades', 1, 2),
(1, 'Postres', 'El broche perfecto', 1, 3),
(1, 'Bebidas', 'Para acompañar', 1, 4);

-- Get category IDs (adjust manually based on your auto_increment)
SET @cat_entradas = LAST_INSERT_ID();
SET @cat_principales = @cat_entradas + 1;
SET @cat_postres = @cat_entradas + 2;
SET @cat_bebidas = @cat_entradas + 3;

-- Add menu items
INSERT INTO menu_items (category_id, restaurant_id, name, description, price, image_url, is_active, sort_order) VALUES
-- Entradas
(@cat_entradas, 1, 'Empanadas Criollas', 'Jugosas empanadas de carne cortada a cuchillo', 1500.00, NULL, 1, 1),
(@cat_entradas, 1, 'Provoleta Casera', 'Provolone a la parrilla con orégano', 2500.00, NULL, 1, 2),
(@cat_entradas, 1, 'Tabla de Fiambres', 'Selección de quesos y embutidos', 3800.00, NULL, 1, 3),

-- Platos Principales
(@cat_principales, 1, 'Bife de Chorizo', 'Corte premium 400g con guarnición', 8500.00, NULL, 1, 1),
(@cat_principales, 1, 'Milanesa Napolitana', 'Con jamón, queso y salsa', 6800.00, NULL, 1, 2),
(@cat_principales, 1, 'Pasta Casera', 'Pasta fresca con salsa a elección', 5500.00, NULL, 1, 3),
(@cat_principales, 1, 'Pollo Grillado', 'Pechuga grillada con vegetales', 5200.00, NULL, 1, 4),

-- Postres
(@cat_postres, 1, 'Flan Casero', 'Con dulce de leche y crema', 1800.00, NULL, 1, 1),
(@cat_postres, 1, 'Tiramisu', 'Receta italiana tradicional', 2200.00, NULL, 1, 2),
(@cat_postres, 1, 'Helado Artesanal', 'Tres sabores a elección', 1500.00, NULL, 1, 3),

-- Bebidas
(@cat_bebidas, 1, 'Coca Cola', 'Lata 354ml', 800.00, NULL, 1, 1),
(@cat_bebidas, 1, 'Agua Mineral', 'Con o sin gas 500ml', 600.00, NULL, 1, 2),
(@cat_bebidas, 1, 'Cerveza Artesanal', 'Pinta 500ml', 1800.00, NULL, 1, 3);

-- Add options for some items
-- Get item IDs
SET @item_empanadas = LAST_INSERT_ID();
SET @item_bife = @item_empanadas + 3;
SET @item_pasta = @item_empanadas + 5;
SET @item_helado = @item_empanadas + 9;
SET @item_cerveza = @item_empanadas + 12;

-- Empanadas: Sabor (required, single)
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_empanadas, 'Sabor', 'single', 1, 1);
SET @opt_sabor = LAST_INSERT_ID();

INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_sabor, 'Carne', 0, 1, 1),
(@opt_sabor, 'Pollo', 0, 1, 2),
(@opt_sabor, 'Jamón y Queso', 200, 1, 3);

-- Bife: Cocción (required, single)
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_bife, 'Cocción', 'single', 1, 1);
SET @opt_coccion = LAST_INSERT_ID();

INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_coccion, 'Jugoso', 0, 1, 1),
(@opt_coccion, 'A punto', 0, 1, 2),
(@opt_coccion, 'Bien cocido', 0, 1, 3);

-- Bife: Guarnición (required, single)
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_bife, 'Guarnición', 'single', 1, 2);
SET @opt_guarnicion = LAST_INSERT_ID();

INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_guarnicion, 'Papas Fritas', 0, 1, 1),
(@opt_guarnicion, 'Puré', 0, 1, 2),
(@opt_guarnicion, 'Ensalada', 0, 1, 3);

-- Pasta: Tipo (required, single)
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_pasta, 'Tipo de Pasta', 'single', 1, 1);
SET @opt_pasta = LAST_INSERT_ID();

INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_pasta, 'Tallarines', 0, 1, 1),
(@opt_pasta, 'Ravioles', 500, 1, 2),
(@opt_pasta, 'Sorrentinos', 800, 1, 3);

-- Pasta: Salsa (required, single)
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_pasta, 'Salsa', 'single', 1, 2);
SET @opt_salsa = LAST_INSERT_ID();

INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_salsa, 'Tuco', 0, 1, 1),
(@opt_salsa, 'Bolognesa', 300, 1, 2),
(@opt_salsa, 'Cuatro Quesos', 600, 1, 3),
(@opt_salsa, 'Pesto', 400, 1, 4);

-- Helado: Sabores (multiple)
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_helado, 'Sabores', 'multiple', 1, 1);
SET @opt_helado = LAST_INSERT_ID();

INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_helado, 'Chocolate', 0, 1, 1),
(@opt_helado, 'Vainilla', 0, 1, 2),
(@opt_helado, 'Frutilla', 0, 1, 3),
(@opt_helado, 'Dulce de Leche', 0, 1, 4),
(@opt_helado, 'Limón', 0, 1, 5);

-- Cerveza: Variedad (required, single)
INSERT INTO menu_item_options (menu_item_id, name, selection_type, is_required, sort_order) VALUES
(@item_cerveza, 'Variedad', 'single', 1, 1);
SET @opt_cerveza = LAST_INSERT_ID();

INSERT INTO menu_item_option_values (option_id, label, extra_price, is_active, sort_order) VALUES
(@opt_cerveza, 'Rubia', 0, 1, 1),
(@opt_cerveza, 'Roja', 200, 1, 2),
(@opt_cerveza, 'Negra', 300, 1, 3),
(@opt_cerveza, 'IPA', 400, 1, 4);
