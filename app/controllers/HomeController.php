<?php
/**
 * HomeController.php
 * Página de inicio pública — muestra perfiles destacados.
 */

require_once APP_PATH . '/Controller.php';
require_once APP_PATH . '/models/PerfilModel.php';
require_once APP_PATH . '/models/CategoriaModel.php';
require_once APP_PATH . '/models/EstadoModel.php';

class HomeController extends Controller
{
    public function index(array $params = []): void
    {
        $perfilModel = new PerfilModel();
        $categorias  = new CategoriaModel();
        $estados     = new EstadoModel();

        // 8 perfiles a mostrar — listarPublicos ya ordena top > resaltado > recientes
        $result = $perfilModel->listarPublicos([], 1);
        $top    = array_slice($result['items'], 0, 8);

        $this->render('home/index', [
            'pageTitle'     => 'Inicio — Clasificados adultos',
            'perfiles'      => $top,
            'categorias'    => $categorias->conConteoPerfiles(),
            'estados'       => $estados->activos(),
            'totalPerfiles' => $result['total'],
        ]);
    }
}
