<?php
declare (strict_types = 1);
/**
 * Clase encargada de renderizar las vistas
 * @description Esta clase se encarga de renderizar las vistas de la aplicación
 * @category Helper
 * @author Jorge Echeverria <jechverria@bytes4run.com>
 * @package app\core\helpers\ViewBuilder
 * @version 1.0.0 rev 1
 * @date 2023-05-03
 * @time 16:08:00
 */
namespace app\core\helpers;

use app\core\helpers\Config;
use Smarty;

class ViewBuilder
{
    /**
     * Motor de plantillas
     * @var Smarty
     */
    private $smarty;
    /**
     * Configuración de la aplicación
     * @var Config
     */
    private Config $configs;
    private array $vars = [];
    /**
     * Ruta de la plantilla
     * @var string
     */
    private string $path;
    public function __construct()
    {
        $this->smarty = new Smarty;
        $this->smarty->setTemplateDir(_VIEW_);
        $this->smarty->setConfigDir(_CONF_ . "smarty/config");
        $this->smarty->setCacheDir(_CACHE_ . "smarty/cache/");
        $this->smarty->setCompileDir(_CACHE_ . "smarty/compiles/");
        $this->smarty->left_delimiter = '{{';
        $this->smarty->right_delimiter = '}}';
        $this->smarty->caching = false;
        //$this->smarty->testInstall();
        $this->configs = new Config;
        $this->vars = $this->configs->get('config', 'json');
    }
    /**
     * Función que renderiza la vista
     * @param mixed $values Datos a ser renderizados
     * @return void
     */
    public function render($values)
    {
        $engine = $this->vars['app_view']['engine'];
        $theme = $this->vars['app_view']['theme'];/* 
        if (isset($values['resource'])) {
            if ($values['resource'] == "assets") {
                header('Location: ' . $values['file']);
            } else {
                header('Location: ' . '/assets/' . $engine . '/' . $theme . '/' . $values['file']);
            }
        } */
        /* Array
            (
                [view] => Array
                    (
                        [type] => template
                        [name] => home/index
                        [data] => Array
                            (
                                [code] => 
                                [style] => Array()
                            )
                    )
                [data] => Array
                    (
                        [breadcrumbs] => Array
                            (
                                [main] => home
                                [routes] => Array
                                    (
                                        [0] => Array
                                            (
                                                [text] => home
                                                [controller] => home
                                                [method] => index
                                                [param] => 
                                            )
                                    )
                            )
                        [datos] => Array()
                    )
            ) */
        if ($engine == "json" || empty($engine)) {
            header('Content-Type: application/json'); //Especificamos el tipo de contenido a devolver
            $code = (isset($values['data']['code'])) ? $values['data']['code'] : $values['view']['data']['code'];
            http_response_code(intval($code));
            echo json_encode($values['data'], JSON_THROW_ON_ERROR); //Devolvemos el contenido
        } elseif ($engine == "smarty") {
            $this->path = $engine . "/" . $theme . "/";
            if (isset($values['view'])) {
                if ($this->find($values['view'], $engine, $theme)) { //Verificamos si existe la vista
                    $this->build($values, $engine, $theme); //Construimos la vista
                } else {
                    $this->buildDefault($values, $engine, $theme);
                }
            } else {
                $this->buildDefault($values, $engine, $theme);
            }
        } else {
            $this->path = $engine . "/" . $theme . "/";
            $this->smarty->assign('data', $this->createPlainView($values)); //Asignamos los datos a la vista
            $this->smarty->assign('theme', $this->path); //Asignamos la ruta de la plantilla
            $view = _VIEW_ . $this->path . "_shared/_templates/plain.tpl";
            $view = str_replace('/', '\\', $view);
            $this->smarty->display($view); //Renderizamos la vista
        }
        //Adaptar a más engines
    }
    /**
     * Verifica la existencia de la vista buscada
     * @param string $viewName Contiene la vista a ser renderizada
     * @return bool
     */
    public function find(array $view, string $engine, string $theme): bool
    {
        if ($view['type'] != 'json') {
            $path = $this->getViewPath($view, $engine, $theme);
            return file_exists($path);
        }
        return false;
    }
    /**
     * Renderiza la vista a ser presentada
     * @param array $viewData Datos de la vista a ser renderizada
     * @return bool
     */
    public function build(array $viewData, string $viewEngine, string $viewTheme): bool
    {
        $response = true;
        $path = $this->getViewPath($viewData['view'], $viewEngine, $viewTheme);
        $datos = $this->createView($viewData);
        if ($this->smarty->templateExists($path)) {
            $this->smarty->assign('data', $datos);
            $this->smarty->assign('theme', $this->path);
            try {
                $this->smarty->display($path);
            } catch (\Throwable $e) {
                $response = $this->buildDefault([
                    'error' => [
                        'message' => $e->getMessage(),
                        'line' => $e->getLine(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'trace' => $e->getTraceAsString(),
                    ],
                    'data' => $viewData,
                ], $viewEngine, $viewTheme);
            }
        } else {
            $response = $this->buildDefault([
                'error' => [
                    'message' => "View not found",
                    'code' => 404,
                    'file' => $viewData['view']
                ],
                'data' => $viewData,
            ], $viewEngine, $viewTheme);
        }
        return $response; 
    }
    /**
     * Crea la vista predeterminada
     * @param array $viewData Datos de la vista a ser renderizada
     * @return void|bool
     */
    public function buildDefault($values, string $engine = "Smarty", string $theme = "default"): void
    {
        //Adaptar a más engines
        if ($engine == "smarty") {
            $this->smarty->assign('data', $this->createPlainView($values));
            $this->smarty->assign('theme', $this->path);
            $path = _VIEW_ . $this->path . "_shared/_templates/plain.tpl";
            $path = str_replace('/', '\\', $path);
            $this->smarty->display($path);
        } else {
            header('Content-Type: application/json');
            echo json_encode($values, JSON_THROW_ON_ERROR);
            //return true;
        }
    }
    /**
     * Contruye una vista con los datos recibidos.
     * Devuelve el html de la vista en texto plano.
     * @param array $values
     * @return string
     */
    public function buildMessage(array $values): string
    {
        $type = ($values['type']['name'] == "alert") ? "_shared/templates/_alert.tpl" : "_shared/templates/_message.tpl";
        $path = ($values['view']['name'] != "default") ? _VIEW_ . $values['view']['name'] : _VIEW_ . $type;
        $path = str_replace('/', '\\', $path);
        if ($this->smarty->templateExists($path)) {
            $this->smarty->assign('view', $values['view']['data']);
            $this->smarty->assign('data', $values['data']);
        } else {
            $this->smarty->assign('data', $this->createPlainView($values));
            $path = _VIEW_ . $this->path . "_shared/_templates/plain.tpl";
            $path = str_replace('/', '\\', $path);
        }
        return $this->smarty->fetch($path);
    }
    /**
     * Usada por la función build, genera los datos adicionales para las vistas;
     * cuando se usa un motor de plantillas.
     * @param mixed $values
     * @return array
     */
    protected function createView($values)
    {
        if (isset($values['view']) && !empty($values['view'])) {
            $viewParts = explode("/", $values['view']['name']);
            $title = $viewParts[0];
        }
        $theme = "default";
        $tech = "default";
        foreach ($this->vars['app_colab'] as $colab) {
            if (isset($colab['theme'])) {
                $theme = $colab['theme'];
            }

            if (isset($colab['technology'])) {
                $tech = $colab['technology'];
            }

        }
        $tech[] = ['name' => "PHP " . phpversion(), 'url' => "http://www.php.net"];
        $response = [
            'content' => $values['data'],
            'layout' => [
                'head' => [
                    'template' => "_shared/templates/_head.tpl",
                    'data' => [
                        'author' => $this->vars['app_author'],
                        'description' => $this->vars['app_description'],
                        'lang' => $this->vars['app_language'],
                        'app_name' => $this->vars['app_name'],
                        'app_logo' => $this->vars['app_logo'],
                        'title' => $title,
                        'version' => $this->vars['app_short_version'],
                        'app_url' => $this->vars['app_url'],
                        'meta'=>[
                            ['meta_name'=>"msapplication-TileColor",'meta_content'=>$this->vars['app_tile_color']],
                            ['meta_name'=>"msapplication-TileImage",'meta_content'=>"assets/img/app_icons/ms-icon-144x144.png"],
                            ['meta_name'=>"theme-color",'meta_content'=>$this->vars['app_theme_color']],
                            ['meta_name'=>"background_color",'meta_content'=>$this->vars['app_background_color']],
                            ['meta_name'=>"apple-mobile-web-app-capable",'meta_content'=>"yes"],
                            ['meta_name'=>"apple-mobile-web-app-status-bar-style",'meta_content'=>"black"],
                            ['meta_name'=>"apple-mobile-web-app-title",'meta_content'=>$this->vars['app_name']],
                            ['meta_name'=>"application-name",'meta_content'=>$this->vars['app_name']],
                            ['meta_name'=>"description",'meta_content'=>$this->vars['app_description']],
                            ['meta_name'=>"format-detection",'meta_content'=>"telephone=no"],
                            ['meta_name'=>"mobile-web-app-capable",'meta_content'=>"yes"],
                            ['meta_name'=>"msapplication-config",'meta_content'=>""],
                            ['meta_name'=>"msapplication-tap-highlight",'meta_content'=>"no"],
                            ['meta_name'=>"viewport",'meta_content'=>"width=device-width, initial-scale=1, shrink-to-fit=no"],
                        ]
                    ],
                    'css' => '',
                ],
                'body' => ['layout' => '', 'darkmode' => ''],
                'footer' => [
                    'tempalate' => "_shared/templates/_footer.tpl",
                    'data' => [
                        'version' => $this->vars['app_version'],
                        'theme' => $theme,
                        'technology' => $tech,
                        'year' => $this->vars['app_start_year'],
                        'companyURL' => $this->vars['app_company_url'],
                        'company' => $this->vars['app_company'],
                    ],
                ],
                'navbar' => [
                    'template' => "_shared/templates/_navbar.tpl",
                    'data' => [
                        //'app_logo' => ($userData['mode'] == "dark") ? $this->vars['darkLogo'] : $this->vars['app_logo'],
                        'app_logo' => $this->vars['app_logo'],
                        //'user' => $userData
                    ],
                ],
                'scripts' => '',
            ],
        ];
        /*
        'sidebar' => [
        'template' => "_shared/templates/_sidebar.tpl",
        'data' => [
        'app_logo' => $this->vars['app_logo'],
        //'user_name' => (isset($userData['name'])) ? $userData['name'] : "",
        'user_image' => "assets/img/avatar5.png",
        ]
        ], */
        return $response;
    }
    /**
     * Crea una vista de texto plano
     * @param array $values
     * @return array
     */
    protected function createPlainView(array $values)
    {
        if (isset($values['view']) && !empty($values['view'])) {
            $viewParts = explode("/", $values['view']['name']);
            $title = $viewParts[0];
        }
        foreach ($this->vars['app_colab'] as $colab) {
            if (isset($colab['theme'])) {
                $theme = $colab['theme'];
            }

            if (isset($colab['technology'])) {
                $tech = $colab['technology'];
            }

        }
        $tech[] = ['name' => "PHP " . phpversion(), 'url' => "http://www.php.net"];
        $response = [
            'content' => $values,
            'layout' => [
                'head' => [
                    'template' => "_shared/templates/_head.tpl",
                    'data' => [
                        'author' => $this->vars['app_author'],
                        'description' => $this->vars['app_description'],
                        'lang' => $this->vars['app_language'],
                        'app_name' => $this->vars['app_name'],
                        'app_logo' => $this->vars['app_logo'],
                        'title' => (isset($title)) ?? "",
                        'version' => $this->vars['app_short_version'],
                        'app_url' => $this->vars['app_url'],
                    ],
                    'css' => '',
                ],
                'body' => ['layout' => '', 'darkmode' => null],
                'footer' => [
                    'tempalate' => "_shared/templates/_footer.tpl",
                    'data' => [
                        'version' => $this->vars['app_version'],
                        'theme' => $theme,
                        'technology' => $tech,
                        'year' => $this->vars['app_start_year'],
                        'companyURL' => $this->vars['app_company_url'],
                        'company' => $this->vars['app_company'],
                    ],
                ],
                'scripts' => '',
            ],
        ];
        return $response;
    }
    /**
     * Función que busca la vista y retorna su ruta
     * @param array $view ['type' => 'view|template|layout', 'name' => '']
     * @param mixed $engine Nombre del motor que se usa para renderizar la vista
     * @param mixed $theme Nombre del tema que se usa para renderizar la vista
     * @return string
     */
    protected function getViewPath(array $view, string $engine, string $theme): string
    {
        $type = $view['type'];
        $name = $view['name'];
        $path = _VIEW_ . $engine . "/" . $theme . "/";
        switch ($type) {
            case "view":
                $path .= $name . ".tpl";
                break;
            case "template":
                $nameSplited = explode("/", $name);
                $app = $nameSplited[0];
                $module = (isset($nameSplited[1])) ? $nameSplited[1] : "default";
                $viewName = (isset($nameSplited[2])) ? $nameSplited[2] : 'main';
                $path .= $app . "/" . $module . "/templates/" . $viewName . ".tpl";
                break;
            case "layout":
                $nameSplited = explode("/", $name);
                $app = $nameSplited[0];
                $module = (isset($nameSplited[1]))  ? $nameSplited[1] : "default";
                $viewName = (isset($nameSplited[2])) ? $nameSplited[2] : 'main';
                $path .= $app . "/" . $module . "/layouts/" . $viewName . ".tpl";
                break;
        }
        return $path;
    }
}
