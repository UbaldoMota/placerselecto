<?php
/**
 * Model.php
 * Clase base de la que extienden todos los modelos.
 * Provee acceso a la conexión PDO y helpers CRUD genéricos.
 */

abstract class Model
{
    /** @var PDO Conexión PDO compartida */
    protected PDO $db;

    /** @var string Nombre de la tabla (debe definirse en cada modelo) */
    protected string $table = '';

    /** @var string Columna de clave primaria */
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ---------------------------------------------------------
    // CRUD GENÉRICO
    // ---------------------------------------------------------

    /**
     * Busca un registro por su clave primaria.
     *
     * @return array|null
     */
    public function find(int $id): ?array
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Retorna todos los registros (usar con precaución en tablas grandes).
     *
     * @return array<int, array>
     */
    public function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $sql  = "SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$direction}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inserta un nuevo registro.
     * Retorna el ID generado.
     *
     * @param array<string, mixed> $data Columna => Valor
     * @return int
     */
    public function insert(array $data): int
    {
        $columns      = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $this->table,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza un registro por su PK.
     *
     * @param int                  $id
     * @param array<string, mixed> $data
     * @return int Número de filas afectadas
     */
    public function update(int $id, array $data): int
    {
        $sets = array_map(fn($col) => "`{$col}` = ?", array_keys($data));
        $sql  = sprintf(
            'UPDATE `%s` SET %s WHERE `%s` = ?',
            $this->table,
            implode(', ', $sets),
            $this->primaryKey
        );

        $values   = array_values($data);
        $values[] = $id;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        return $stmt->rowCount();
    }

    /**
     * Elimina un registro por su PK.
     *
     * @return int Filas eliminadas
     */
    public function delete(int $id): int
    {
        $sql  = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    /**
     * Cuenta el total de registros en la tabla.
     */
    public function count(string $where = '', array $params = []): int
    {
        $sql  = "SELECT COUNT(*) FROM `{$this->table}`";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Busca un registro por una columna específica.
     *
     * @return array|null
     */
    public function findBy(string $column, mixed $value): ?array
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE `{$column}` = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$value]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Busca múltiples registros por una columna.
     *
     * @return array<int, array>
     */
    public function findAllBy(string $column, mixed $value, string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` = ? ORDER BY `{$orderBy}` {$dir}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ---------------------------------------------------------
    // PAGINACIÓN
    // ---------------------------------------------------------

    /**
     * Obtiene resultados paginados.
     *
     * @param int    $page    Página actual (1-indexed)
     * @param int    $perPage Registros por página
     * @param string $where   Cláusula WHERE opcional (sin la palabra WHERE)
     * @param array  $params  Parámetros para el WHERE
     * @param string $orderBy Columna de ordenación
     * @param string $dir     ASC | DESC
     * @return array{items: array, total: int, pages: int, current: int}
     */
    public function paginate(
        int    $page    = 1,
        int    $perPage = ITEMS_PER_PAGE,
        string $where   = '',
        array  $params  = [],
        string $orderBy = 'id',
        string $dir     = 'DESC'
    ): array {
        $dir    = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $whereClause = !empty($where) ? "WHERE {$where}" : '';

        // Total para paginación
        $countSql  = "SELECT COUNT(*) FROM `{$this->table}` {$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Datos paginados
        $sql = "SELECT * FROM `{$this->table}` {$whereClause}
                ORDER BY `{$orderBy}` {$dir}
                LIMIT ? OFFSET ?";

        $allParams = array_merge($params, [$perPage, $offset]);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($allParams);

        return [
            'items'   => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'   => $total,
            'pages'   => (int) ceil($total / $perPage),
            'current' => $page,
            'perPage' => $perPage,
        ];
    }

    // ---------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------

    /**
     * Verifica si existe un registro con el valor dado.
     */
    public function exists(string $column, mixed $value, ?int $excludeId = null): bool
    {
        $sql    = "SELECT 1 FROM `{$this->table}` WHERE `{$column}` = ?";
        $params = [$value];

        if ($excludeId !== null) {
            $sql    .= " AND `{$this->primaryKey}` != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Ejecuta una query raw con parámetros.
     * Útil para queries complejas que no encajan en los helpers.
     */
    protected function raw(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
