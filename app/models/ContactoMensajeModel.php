<?php
/**
 * ContactoMensajeModel.php
 * Mensajes recibidos a través del formulario público /contacto.
 * Se diferencia de SoporteMensajeModel en que NO requiere usuario registrado.
 */

require_once APP_PATH . '/Model.php';

class ContactoMensajeModel extends Model
{
    protected string $table      = 'contacto_mensajes';
    protected string $primaryKey = 'id';

    public const ASUNTOS = ['soporte', 'pagos', 'reporte', 'legal', 'otro'];

    public const ASUNTOS_LABELS = [
        'soporte' => 'Soporte general',
        'pagos'   => 'Pagos y facturación',
        'reporte' => 'Reporte o denuncia',
        'legal'   => 'Asuntos legales / ARCO',
        'otro'    => 'Otro',
    ];

    public function crear(array $data): int
    {
        return $this->insert([
            'nombre'  => mb_substr($data['nombre']  ?? '', 0, 80),
            'email'   => mb_substr($data['email']   ?? '', 0, 180),
            'asunto'  => in_array($data['asunto'] ?? 'otro', self::ASUNTOS, true) ? $data['asunto'] : 'otro',
            'mensaje' => mb_substr($data['mensaje'] ?? '', 0, 5000),
            'ip'      => $data['ip'] ?? null,
            'leido'   => 0,
        ]);
    }

    public function marcarLeido(int $id, bool $leido = true): bool
    {
        return $this->raw(
            "UPDATE contacto_mensajes SET leido = ? WHERE id = ?",
            [$leido ? 1 : 0, $id]
        )->rowCount() >= 0;
    }

    public function listarAdmin(int $page = 1, array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (isset($filtros['leido']) && $filtros['leido'] !== '') {
            $where[]  = 'leido = ?';
            $params[] = (int) $filtros['leido'];
        }
        if (!empty($filtros['asunto']) && in_array($filtros['asunto'], self::ASUNTOS, true)) {
            $where[]  = 'asunto = ?';
            $params[] = $filtros['asunto'];
        }
        if (!empty($filtros['buscar'])) {
            $where[]  = '(nombre LIKE ? OR email LIKE ? OR mensaje LIKE ?)';
            $like     = '%' . $filtros['buscar'] . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }

        $whereSQL = implode(' AND ', $where);
        $perPage  = 25;
        $offset   = (max(1, $page) - 1) * $perPage;

        $total = (int) $this->raw(
            "SELECT COUNT(*) FROM contacto_mensajes WHERE {$whereSQL}",
            $params
        )->fetchColumn();

        $items = $this->raw(
            "SELECT * FROM contacto_mensajes
             WHERE {$whereSQL}
             ORDER BY leido ASC, fecha_creacion DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'   => $items,
            'total'   => $total,
            'pages'   => (int) ceil($total / $perPage),
            'current' => max(1, $page),
            'perPage' => $perPage,
        ];
    }

    public function contarNoLeidos(): int
    {
        return (int) $this->raw(
            "SELECT COUNT(*) FROM contacto_mensajes WHERE leido = 0"
        )->fetchColumn();
    }
}
