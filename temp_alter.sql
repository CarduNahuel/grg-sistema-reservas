ALTER TABLE tables ADD COLUMN IF NOT EXISTS zone VARCHAR(100) DEFAULT 'General' AFTER element_type;
ALTER TABLE tables ADD COLUMN IF NOT EXISTS connected_zone VARCHAR(100) NULL AFTER zone;
ALTER TABLE tables ADD COLUMN IF NOT EXISTS description VARCHAR(255) NULL AFTER connected_zone;
SELECT id, element_type, zone, connected_zone, description FROM tables LIMIT 3;
