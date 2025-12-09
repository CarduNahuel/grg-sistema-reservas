ALTER TABLE reservations MODIFY table_id INT UNSIGNED NULL;
ALTER TABLE reservations ADD COLUMN IF NOT EXISTS preferred_table_id INT UNSIGNED NULL AFTER table_id;
ALTER TABLE reservations ADD COLUMN IF NOT EXISTS preferred_zone VARCHAR(100) NULL AFTER preferred_table_id;
ALTER TABLE reservations ADD CONSTRAINT fk_reservations_preferred_table FOREIGN KEY (preferred_table_id) REFERENCES tables(id) ON DELETE SET NULL;
