<?php
/**
 * Upload.php
 * Gestor seguro de subida de archivos.
 * Valida tipo MIME real (no solo extensión), renombra con hash,
 * verifica dimensiones mínimas y protege contra path traversal.
 */

class Upload
{
    /** @var array<string> Errores generados durante el proceso */
    private array $errors = [];

    /** @var string|null Nombre final del archivo guardado */
    private ?string $savedFilename = null;

    // ---------------------------------------------------------
    // VALIDACIÓN Y ALMACENAMIENTO
    // ---------------------------------------------------------

    /**
     * Procesa y guarda una imagen subida.
     *
     * @param array  $file      Elemento de $_FILES['campo']
     * @param string $subdir    Subdirectorio dentro de /uploads (ej. 'anuncios')
     * @param int    $maxWidth  Ancho máximo en px (0 = sin límite)
     * @param int    $maxHeight Alto máximo en px (0 = sin límite)
     * @param int    $minWidth  Ancho mínimo en px
     * @param int    $minHeight Alto mínimo en px
     * @return bool true si el archivo se guardó correctamente
     */
    public function saveImage(
        array  $file,
        string $subdir    = 'anuncios',
        int    $maxWidth  = 2000,
        int    $maxHeight = 2000,
        int    $minWidth  = 200,
        int    $minHeight = 200
    ): bool {
        $this->errors        = [];
        $this->savedFilename = null;

        // 1. Verificar que el upload llegó sin errores de PHP
        if (!$this->checkUploadError($file['error'] ?? UPLOAD_ERR_NO_FILE)) {
            return false;
        }

        // 2. Validar tamaño máximo
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            $maxMb = UPLOAD_MAX_SIZE / 1024 / 1024;
            $this->errors[] = "La imagen no debe superar {$maxMb} MB.";
            return false;
        }

        // 3. Validar tipo MIME REAL (usando finfo, no el header del browser)
        $mimeType = $this->getRealMimeType($file['tmp_name']);
        if (!in_array($mimeType, UPLOAD_ALLOWED_TYPES, true)) {
            $this->errors[] = 'Tipo de archivo no permitido. Solo se aceptan: JPG, PNG, WEBP.';
            return false;
        }

        // 4. Validar extensión coherente con el MIME
        $ext = $this->mimeToExt($mimeType);
        if ($ext === null) {
            $this->errors[] = 'Extensión de archivo no reconocida.';
            return false;
        }

        // 5. Validar que es una imagen real (getimagesize)
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->errors[] = 'El archivo no es una imagen válida.';
            return false;
        }

        [$width, $height] = $imageInfo;

        // 6. Validar dimensiones mínimas
        if ($width < $minWidth || $height < $minHeight) {
            $this->errors[] = "La imagen debe tener al menos {$minWidth}x{$minHeight} píxeles.";
            return false;
        }

        // 7. Si supera las dimensiones máximas — escalar automáticamente (no rechazar)
        $needsResize = ($maxWidth > 0 && $width > $maxWidth) || ($maxHeight > 0 && $height > $maxHeight);

        // 8. Construir nombre de archivo único y seguro (sin path traversal)
        $filename = $this->generateSafeFilename($ext);
        $destDir  = UPLOADS_PATH . '/' . $subdir;
        $destPath = $destDir . '/' . $filename;

        // 9. Asegurar que el directorio existe
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // 10. Mover archivo temporal al destino final
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->errors[] = 'No se pudo guardar la imagen. Intenta de nuevo.';
            return false;
        }

        // 10b. Redimensionar si supera el límite
        if ($needsResize) {
            $this->resizeImage($destPath, $mimeType, $maxWidth, $maxHeight);
        }

        // 11. Aplicar marca de agua (si está habilitada)
        if (defined('WATERMARK_ENABLED') && WATERMARK_ENABLED) {
            $this->applyWatermark($destPath, $mimeType);
        }

        // 12. Establecer permisos restrictivos
        chmod($destPath, 0644);

        // 13. Generar variantes (thumb 300, medium 800) + WebP de cada una
        $this->generateVariants($destPath, $mimeType);

        $this->savedFilename = $filename;
        return true;
    }

    /**
     * Genera thumbnails 300 / 800 y una versión WebP de cada tamaño.
     * Se escriben al lado del archivo original con sufijo:
     *   {stem}_thumb.{ext}   (~300px largo lado)
     *   {stem}_thumb.webp
     *   {stem}_medium.{ext}  (~800px largo lado)
     *   {stem}_medium.webp
     *   {stem}.webp          (full en webp — mismo tamaño que el original)
     */
    private function generateVariants(string $fullPath, string $mimeType): void
    {
        $info = @getimagesize($fullPath);
        if ($info === false) return;
        [$w, $h] = $info;

        $dir  = dirname($fullPath);
        $stem = pathinfo($fullPath, PATHINFO_FILENAME);

        $src = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($fullPath),
            'image/png'  => @imagecreatefrompng($fullPath),
            'image/webp' => @imagecreatefromwebp($fullPath),
            default      => null,
        };
        if (!$src) return;

        // WebP del original (mismo tamaño). Si ya era webp, no duplicamos.
        if ($mimeType !== 'image/webp' && function_exists('imagewebp')) {
            @imagewebp($src, $dir . '/' . $stem . '.webp', 82);
        }

        // Variantes
        $variantes = ['thumb' => 300, 'medium' => 800];
        $ext = pathinfo($fullPath, PATHINFO_EXTENSION);

        foreach ($variantes as $nombre => $maxLado) {
            $escala = min($maxLado / $w, $maxLado / $h, 1);
            if ($escala >= 1) {
                // Imagen ya es menor que el tamaño — copiar original como variante
                @copy($fullPath, $dir . '/' . $stem . '_' . $nombre . '.' . $ext);
                if ($mimeType !== 'image/webp' && function_exists('imagewebp')) {
                    @imagewebp($src, $dir . '/' . $stem . '_' . $nombre . '.webp', 82);
                }
                continue;
            }

            $nw = (int)round($w * $escala);
            $nh = (int)round($h * $escala);
            $dst = imagecreatetruecolor($nw, $nh);

            // Preservar transparencia en PNG/WEBP
            if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

            // Guardar en formato original
            $outOrig = $dir . '/' . $stem . '_' . $nombre . '.' . $ext;
            match ($mimeType) {
                'image/jpeg' => @imagejpeg($dst, $outOrig, 82),
                'image/png'  => @imagepng($dst, $outOrig, 6),
                'image/webp' => @imagewebp($dst, $outOrig, 82),
                default      => null,
            };
            @chmod($outOrig, 0644);

            // Guardar también en WebP (si no era ya)
            if ($mimeType !== 'image/webp' && function_exists('imagewebp')) {
                $outWebp = $dir . '/' . $stem . '_' . $nombre . '.webp';
                @imagewebp($dst, $outWebp, 80);
                @chmod($outWebp, 0644);
            }

            imagedestroy($dst);
        }

        imagedestroy($src);
    }

    /**
     * Elimina un archivo de imagen del servidor.
     *
     * @param string $filename Nombre del archivo (sin path)
     * @param string $subdir   Subdirectorio (ej. 'anuncios')
     * @return bool
     */
    public function delete(string $filename, string $subdir = 'anuncios'): bool
    {
        if (empty($filename)) {
            return false;
        }

        // Prevenir path traversal
        $filename = basename($filename);
        $path     = UPLOADS_PATH . '/' . $subdir . '/' . $filename;

        if (file_exists($path) && is_file($path)) {
            return unlink($path);
        }

        return false;
    }

    // ---------------------------------------------------------
    // RESULTADO
    // ---------------------------------------------------------

    /**
     * Retorna el nombre del archivo guardado (null si falló).
     */
    public function getSavedFilename(): ?string
    {
        return $this->savedFilename;
    }

    /**
     * Retorna la URL pública del archivo guardado.
     *
     * @param string $subdir
     */
    public function getSavedUrl(string $subdir = 'anuncios'): ?string
    {
        if ($this->savedFilename === null) {
            return null;
        }
        return APP_URL . '/uploads/' . $subdir . '/' . $this->savedFilename;
    }

    /**
     * Retorna true si hubo errores.
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Retorna los errores generados.
     *
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Retorna el primer error.
     */
    public function firstError(): string
    {
        return $this->errors[0] ?? '';
    }

    // ---------------------------------------------------------
    // HELPERS PRIVADOS
    // ---------------------------------------------------------

    /**
     * Verifica el código de error de PHP en la subida.
     */
    private function checkUploadError(int $errorCode): bool
    {
        switch ($errorCode) {
            case UPLOAD_ERR_OK:
                return true;
            case UPLOAD_ERR_NO_FILE:
                $this->errors[] = 'No se seleccionó ningún archivo.';
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->errors[] = 'El archivo supera el tamaño máximo permitido.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $this->errors[] = 'El archivo se subió de forma incompleta.';
                break;
            default:
                $this->errors[] = 'Error desconocido al subir el archivo.';
        }
        return false;
    }

    /**
     * Obtiene el tipo MIME real del archivo usando finfo.
     */
    private function getRealMimeType(string $tmpPath): string
    {
        if (!file_exists($tmpPath)) {
            return '';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        return $mime ?: '';
    }

    /**
     * Mapea un tipo MIME a su extensión de archivo.
     */
    private function mimeToExt(string $mime): ?string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        return $map[$mime] ?? null;
    }

    /**
     * Genera un nombre de archivo único y seguro.
     * Formato: [timestamp]_[random16chars].[ext]
     */
    private function generateSafeFilename(string $ext): string
    {
        return time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    }

    // ---------------------------------------------------------
    // MARCA DE AGUA
    // ---------------------------------------------------------

    /**
     * Aplica una marca de agua en mosaico diagonal sobre la imagen.
     * Usa exclusivamente GD (sin fuentes externas).
     * La marca queda grabada en el archivo definitivo.
     */
    /**
     * Redimensiona una imagen si excede maxWidth/maxHeight, manteniendo proporción.
     */
    private function resizeImage(string $path, string $mimeType, int $maxWidth, int $maxHeight): void
    {
        $info = @getimagesize($path);
        if ($info === false) return;
        [$w, $h] = $info;

        // Factor de escala (el menor de los dos para que quepa en ambos)
        $scaleW = $maxWidth  > 0 ? ($maxWidth  / $w) : 1;
        $scaleH = $maxHeight > 0 ? ($maxHeight / $h) : 1;
        $scale  = min($scaleW, $scaleH, 1); // nunca ampliar
        if ($scale >= 1) return;

        $newW = (int)round($w * $scale);
        $newH = (int)round($h * $scale);

        $src = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default      => null,
        };
        if (!$src) return;

        $dst = imagecreatetruecolor($newW, $newH);
        // Preservar transparencia en PNG/WEBP
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

        match ($mimeType) {
            'image/jpeg' => imagejpeg($dst, $path, 88),
            'image/png'  => imagepng($dst, $path, 6),
            'image/webp' => imagewebp($dst, $path, 88),
            default      => null,
        };

        imagedestroy($src);
        imagedestroy($dst);
    }

    private function applyWatermark(string $path, string $mimeType): void
    {
        // Cargar imagen según tipo
        $img = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default      => null,
        };
        if (!$img) return;

        $w = imagesx($img);
        $h = imagesy($img);

        imagealphablending($img, true);

        $texto = defined('WATERMARK_TEXT') ? WATERMARK_TEXT : (defined('APP_NAME') ? APP_NAME : 'Marca de agua');

        // Escalar la fuente según el ancho de la imagen (mín 2, máx 5)
        $font = match (true) {
            $w >= 1200 => 5,
            $w >= 700  => 4,
            $w >= 400  => 3,
            default    => 2,
        };

        $cw = imagefontwidth($font);
        $ch = imagefontheight($font);
        $tw = $cw * strlen($texto);   // ancho total del texto en px
        $th = $ch;                     // alto del texto en px

        // Espaciado entre repeticiones
        $gapX = (int)($tw  * 1.6);
        $gapY = (int)($th  * 4.0);

        // Colores semi-transparentes (alpha: 0=opaco, 127=invisible)
        $opacity = defined('WATERMARK_OPACITY') ? (int)WATERMARK_OPACITY : 75;
        $opacity = max(0, min(127, $opacity));

        $colorText   = imagecolorallocatealpha($img, 255, 255, 255, $opacity);
        $colorShadow = imagecolorallocatealpha($img, 0,   0,   0,   min(127, $opacity + 20));

        // Mosaico: filas desplazadas para dar sensación diagonal
        for ($row = -1; ($row * $gapY) < ($h + $gapY); $row++) {
            $y      = $row * $gapY;
            $offset = ($row % 2 === 0) ? 0 : (int)($gapX / 2);

            for ($col = -1; ($col * $gapX + $offset) < ($w + $tw); $col++) {
                $x = $col * $gapX + $offset;
                // Sombra (+1 px abajo-derecha)
                imagestring($img, $font, $x + 1, $y + 1, $texto, $colorShadow);
                // Texto
                imagestring($img, $font, $x, $y, $texto, $colorText);
            }
        }

        imagesavealpha($img, true);

        // Guardar sobreescribiendo el archivo original
        match ($mimeType) {
            'image/jpeg' => imagejpeg($img, $path, 88),
            'image/png'  => imagepng($img, $path, 6),
            'image/webp' => imagewebp($img, $path, 88),
        };

        imagedestroy($img);
    }
}
