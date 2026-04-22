<?php
/**
 * StorageScannerModel.php
 * Escanea directorios de uploads y devuelve estadísticas + listado.
 * No es un modelo de BD, pero se deja aquí para mantener la convención.
 */

class StorageScannerModel
{
    /**
     * Categorías con su ruta física.
     */
    public const CATEGORIAS = [
        'fotos'                 => ['dir' => '/anuncios',                 'label' => 'Fotos de perfiles y anuncios', 'icon' => 'images',             'color' => '#FF2D75'],
        'videos'                => ['dir' => '/videos',                   'label' => 'Videos de perfiles',           'icon' => 'play-btn-fill',      'color' => '#3B82F6'],
        'videos_verificacion'   => ['dir' => '/verificaciones',           'label' => 'Videos de verificación',       'icon' => 'camera-video-fill',  'color' => '#8B5CF6', 'only_files' => true],
        'videos_verif_perfil'   => ['dir' => '/verificaciones/perfiles',  'label' => 'Videos de verificación perfiles', 'icon' => 'camera-reels-fill', 'color' => '#10B981'],
        'documentos'            => ['dir' => '/verificaciones/documentos','label' => 'Documentos de identidad',      'icon' => 'file-earmark-person','color' => '#F59E0B'],
    ];

    /**
     * Resumen por categoría.
     * @return array<string, array{label:string, icon:string, color:string, files:int, bytes:int}>
     */
    public function resumen(): array
    {
        $out = [];
        foreach (self::CATEGORIAS as $key => $meta) {
            $dir = UPLOADS_PATH . $meta['dir'];
            [$files, $bytes] = $this->escanearDir($dir, $meta['only_files'] ?? false);
            $out[$key] = [
                'label' => $meta['label'],
                'icon'  => $meta['icon'],
                'color' => $meta['color'],
                'path'  => $meta['dir'],
                'files' => $files,
                'bytes' => $bytes,
            ];
        }
        return $out;
    }

    /** Total de bytes y archivos en todas las categorías. */
    public function total(): array
    {
        $bytes = 0; $files = 0;
        foreach ($this->resumen() as $r) {
            $bytes += $r['bytes'];
            $files += $r['files'];
        }
        return ['files' => $files, 'bytes' => $bytes];
    }

    /** Listado de archivos de una categoría, ordenados por tamaño DESC. */
    public function listar(string $categoria, int $limit = 200): array
    {
        if (!isset(self::CATEGORIAS[$categoria])) return [];
        $meta = self::CATEGORIAS[$categoria];
        $dir  = UPLOADS_PATH . $meta['dir'];
        if (!is_dir($dir)) return [];

        $list = [];
        $it = new DirectoryIterator($dir);
        foreach ($it as $f) {
            if ($f->isDot() || !$f->isFile()) continue;
            // Excluir .htaccess y ocultos
            if (str_starts_with($f->getFilename(), '.')) continue;
            $list[] = [
                'nombre' => $f->getFilename(),
                'bytes'  => $f->getSize(),
                'mtime'  => $f->getMTime(),
                'ext'    => strtolower($f->getExtension()),
            ];
        }
        usort($list, fn($a, $b) => $b['bytes'] <=> $a['bytes']);
        return array_slice($list, 0, $limit);
    }

    /** Los N archivos más grandes del sistema. */
    public function topArchivos(int $n = 20): array
    {
        $all = [];
        foreach (self::CATEGORIAS as $key => $meta) {
            $files = $this->listar($key, 500);
            foreach ($files as $f) {
                $f['categoria'] = $key;
                $f['categoria_label'] = $meta['label'];
                $all[] = $f;
            }
        }
        usort($all, fn($a, $b) => $b['bytes'] <=> $a['bytes']);
        return array_slice($all, 0, $n);
    }

    private function escanearDir(string $dir, bool $onlyFiles = false): array
    {
        if (!is_dir($dir)) return [0, 0];
        $bytes = 0; $files = 0;
        $it = new DirectoryIterator($dir);
        foreach ($it as $f) {
            if ($f->isDot()) continue;
            if ($f->isFile()) {
                if (str_starts_with($f->getFilename(), '.')) continue;
                $files++;
                $bytes += $f->getSize();
            } elseif (!$onlyFiles && $f->isDir()) {
                // Recurse pero sin contar subdirs anidados que sean otras categorías
                [$sf, $sb] = $this->escanearDir($f->getPathname(), false);
                $files += $sf;
                $bytes += $sb;
            }
        }
        return [$files, $bytes];
    }

    /** Formateo humano: 1.2 GB, 450 MB, 30 KB. */
    public static function fmtBytes(int $b): string
    {
        if ($b >= 1073741824) return number_format($b / 1073741824, 2) . ' GB';
        if ($b >= 1048576)    return number_format($b / 1048576,    1) . ' MB';
        if ($b >= 1024)       return number_format($b / 1024,       0) . ' KB';
        return $b . ' B';
    }
}
