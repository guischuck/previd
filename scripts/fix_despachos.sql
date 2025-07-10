SET FOREIGN_KEY_CHECKS=0;

-- Modificar a coluna para aceitar NULL
ALTER TABLE despachos MODIFY id_empresa bigint unsigned NULL;

-- Atualizar registros existentes para NULL
UPDATE despachos SET id_empresa = NULL;

SET FOREIGN_KEY_CHECKS=1; 