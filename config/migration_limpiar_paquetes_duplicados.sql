-- migration_limpiar_paquetes_duplicados.sql
-- Limpia paquetes duplicados (mismo nombre) y añade restricción para que no vuelva a pasar.

-- 1. Si hay pagos apuntando a paquetes que vamos a borrar, redirigirlos al más viejo (menor id) con el mismo nombre.
UPDATE pagos pg
JOIN token_paquetes t1 ON pg.id_paquete = t1.id
JOIN (
    SELECT MIN(id) AS id_keep, nombre
    FROM token_paquetes
    GROUP BY nombre
) t2 ON t1.nombre = t2.nombre
SET pg.id_paquete = t2.id_keep
WHERE pg.id_paquete != t2.id_keep;

-- 2. Borrar paquetes duplicados dejando solo el de id más bajo por cada nombre.
DELETE p1 FROM token_paquetes p1
INNER JOIN token_paquetes p2
    ON p1.nombre = p2.nombre
   AND p1.id > p2.id;

-- 3. Añadir UNIQUE para prevenir futuros duplicados.
ALTER TABLE token_paquetes ADD UNIQUE KEY uq_paquete_nombre (nombre);
