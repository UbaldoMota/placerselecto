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
}
