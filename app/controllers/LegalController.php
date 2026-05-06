<?php
/**
 * LegalController.php
 * Sirve las páginas legales: términos, privacidad y aviso +18.
 */

require_once APP_PATH . '/Controller.php';

class LegalController extends Controller
{
    public function terms(array $params = []): void
    {
        $this->render('legal/terms', ['pageTitle' => 'Términos y condiciones']);
    }

    public function privacy(array $params = []): void
    {
        $this->render('legal/privacy', ['pageTitle' => 'Aviso de privacidad']);
    }

    public function adultNotice(array $params = []): void
    {
        $this->render('legal/adult-notice', ['pageTitle' => 'Aviso para mayores de edad (+18)']);
    }

    public function dmca(array $params = []): void
    {
        $this->render('legal/dmca', ['pageTitle' => 'Política de Derechos de Autor']);
    }

    public function statement2257(array $params = []): void
    {
        $this->render('legal/2257', ['pageTitle' => 'Declaración de verificación de edad (2257)']);
    }

    public function cookies(array $params = []): void
    {
        $this->render('legal/cookies', ['pageTitle' => 'Política de Cookies']);
    }

    public function payments(array $params = []): void
    {
        $this->render('legal/pagos', ['pageTitle' => 'Política de Pagos y Devoluciones']);
    }
}
