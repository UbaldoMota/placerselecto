<?php
/**
 * database.php
 * Clase de conexión a la base de datos mediante PDO.
 * Patrón Singleton para reutilizar una única conexión por request.
 */

class Database
{
    /** @var Database|null Instancia única */
    private static ?Database $instance = null;

    /** @var PDO Objeto PDO */
    private PDO $pdo;

    /**
     * Constructor privado.
     * Establece la conexión PDO con opciones de seguridad y rendimiento.
     *
     * @throws RuntimeException Si la conexión falla.
     */
    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Lanza excepciones en error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Arrays asociativos por defecto
            PDO::ATTR_EMULATE_PREPARES   => false,                     // Prepared statements reales
            PDO::ATTR_PERSISTENT         => false,                     // Sin conexiones persistentes (más seguro)
            PDO::MYSQL_ATTR_FOUND_ROWS   => true,                      // Retorna filas encontradas en UPDATE
            PDO::ATTR_TIMEOUT            => 5,                         // Timeout de conexión (segundos)
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            // Configurar MySQL para máxima seguridad y compatibilidad
            $this->pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
            $this->pdo->exec("SET time_zone = '-06:00'"); // America/Mexico_City
            $this->pdo->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");

        } catch (PDOException $e) {
            // Nunca exponer credenciales en producción
            $mensaje = APP_DEBUG
                ? 'Error de conexión a BD: ' . $e->getMessage()
                : 'Error interno del servidor. Por favor intente más tarde.';

            // Registrar error real en log
            error_log('[DB] Fallo conexión PDO: ' . $e->getMessage());

            throw new RuntimeException($mensaje, 500);
        }
    }

    /**
     * Obtiene la instancia única de Database (Singleton).
     *
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna el objeto PDO para ejecutar queries.
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Atajo: prepara y ejecuta un statement con parámetros.
     *
     * @param string $sql    Consulta SQL con placeholders
     * @param array  $params Parámetros para bind
     * @return PDOStatement
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('[DB] Query error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new RuntimeException(
                APP_DEBUG ? 'Query error: ' . $e->getMessage() : 'Error procesando solicitud.',
                500
            );
        }
    }

    /**
     * Inicia una transacción.
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Confirma la transacción.
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Revierte la transacción.
     */
    public function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /**
     * Retorna el ID del último registro insertado.
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Evita clonar la instancia.
     */
    private function __clone() {}

    /**
     * Evita deserialización.
     */
    public function __wakeup()
    {
        throw new RuntimeException('No se permite deserializar Database.');
    }
}
