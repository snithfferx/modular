<?php
/**
 * @category helper
 * @version 1.0.0
 * @author Jorge Echeverria <jechverria@bytes4run.com>
 */
/**
 * @package app\core\helpers\ViewBuilderHelper
 */
namespace app\core\helpers;
use app\core\libraries\AuthenticationLibrary;
use Smarty;
class ViewBuilderHelper {
    private $smarty;
    private $configs;
    private $auth;
    function __construct()
    {
        $this->smarty = new Smarty;
        $this->smarty->setTemplateDir(_VIEW_);
        $this->smarty->setConfigDir(_CONF_ . "smarty/config");
        $this->smarty->setCacheDir(_CACHE_ . "smarty/cache/");
        $this->smarty->setCompileDir(_CACHE_ . "smarty/compiles/");
        $this->smarty->left_delimiter = '{{';
        $this->smarty->right_delimiter = '}}';
        //$this->smarty->testInstall();
        $this->configs = new ConfigHelper;
        $this->auth = new AuthenticationLibrary;
    }
    /**
     * Verifica la existencia de la vista buscada
     * @param string $viewName Contiene la vista a ser renderizada
     * @return bool
     */
    public function find(array $view) :bool
    {
        if ($view['type'] != 'json') {
            $path = $this->getViewPath($view);
            return file_exists($path);
        }
        return true;
    }
    /**
     * Renderiza la vista a ser presentada
     * @param array $viewData Datos de la vista a ser renderizada
     * @return bool
     */
    public function build(array $viewData) :bool
    {
        if ($viewData['view']['type'] != 'json') {
            $path = $this->getViewPath($viewData['view']);
            $datos = $this->createView($viewData);
            if ($this->smarty->templateExists($path)) {
                $this->smarty->assign('data', $datos);
                try {
                    $this->smarty->display($path);
                    $response = true;
                } catch (\Exception $e) {
                    $response = $this->buildDefault(
                    [
                        'error' => [
                            'message' => $e->getMessage(),
                            'line' => $e->getLine(),
                            'code' => $e->getCode(),
                            'file' => $e->getFile(),
                            'trace' => $e->getTraceAsString()
                        ],
                        'data' => $viewData
                    ]);
                }
            }
        } else {
            $response = json_encode($viewData);
        }
        return $response;
    }
    public function buildDefault($values)
    {
        $this->smarty->assign('data', $this->createPlainView($values));
        $path = _VIEW_ . "_shared/templates/_plain.tpl";
        $path = str_replace('/', '\\', $path);
        return $this->smarty->display($path);
    }
    public function buildMessage($values)
    {
        $type = ($values['type']['name'] == "alert") ? "_shared/templates/_alert.tpl" : "_shared/templates/_message.tpl";
        $path = ($values['view']['name'] != "default") ? _VIEW_ . $values['view']['name'] : _VIEW_ . $type;
        $path = str_replace('/', '\\', $path);
        if ($this->smarty->templateExists($path)) {
            $this->smarty->assign('view', $values['view']['data']);
            $this->smarty->assign('data', $values['data']);
        } else {
            $this->smarty->assign('data', $this->createPlainView($values));
            $path = _VIEW_ . "_shared/templates/_plain.tpl";
            $path = str_replace('/', '\\', $path);
        }
        return $this->smarty->fetch($path);
    }


    /**
     * Usada por la función build, genera los datos adicionales para las vistas
     * @param mixed $values
     * @return array
     */
    protected function createView($values)
    {
        $config = $this->configs->get('config');
        //$userData = $this->auth->getSessionData('all');
        if (isset($values['view']) && !empty($values['view'])) {
            $viewParts = explode("/", $values['view']['name']);
            $title = $viewParts[0];
        }
        $theme = "default";
        $tech = "default";
        foreach ($config['colaboration'] as $colab) {
            if (isset($colab['theme'])) $theme = $colab['theme'];
            if (isset($colab['technology'])) $tech = $colab['technology'];
        }
        $tech[] = ['name' => "PHP " . phpversion(), 'url' => "http://www.php.net"];
        $response =  [
            'content' => $values['data'],
            'layout' => [
                'head' => [
                    'template' => "_shared/templates/_head.tpl",
                    'data' => [
                        'author' => $config['author'],
                        'description' => $config['description'],
                        'lang' => $config['language'],
                        'app_name' => $config['app_name'],
                        'app_logo' => $config['app_logo'],
                        'title' => $title,
                        'version' => $config['shortversion'],
                        'app_url' => $config['app_url']
                    ],
                    'css' => '
                    <link rel="stylesheet" type="text/css" href="\assets\css\style.css">
                    '
                ],
                'body' => ['layout' => '', 'darkmode' => ''],
                'footer' => [
                    'tempalate' => "_shared/templates/_footer.tpl",
                    'data' => [
                        'version' => $config['version'],
                        'theme' => $theme,
                        'technology' => $tech,
                        'year' => $config['startYear'],
                        'companyURL' => $config['companyURL'],
                        'company' => $config['company']
                    ]
                ],
                'navbar' => [
                    'template' => "_shared/templates/_navbar.tpl",
                    'data' => [
                        //'app_logo' => ($userData['mode'] == "dark") ? $config['darkLogo'] : $config['app_logo'],
                        'app_logo' => $config['app_logo'],
                        //'user' => $userData
                    ]
                ],
                'scripts' => ''
            ]
        ];
        /*
        'sidebar' => [
            'template' => "_shared/templates/_sidebar.tpl",
            'data' => [
                'app_logo' => $config['app_logo'],
                //'user_name' => (isset($userData['name'])) ? $userData['name'] : "",
                'user_image' => "assets/img/avatar5.png",
            ]
        ], */
        return $response;
    }
    /* private function getUserData()
    {
        $auth = new AuthenticationLibrary;
        $session = $auth->getSession();
        $user = $auth->getUserData(base64_decode($session['id_usuario']));
        $uName = explode(" ", $user[0]['nombre']);
        if ($uName > 2) {
            $userName = $uName[2] . ", " . $uName[0];
        } elseif ($uName > 1) {
            $userName = $uName[1] . ", " . $uName[0];
        } else {
            $userName = $uName[0];
        }
        return [
            'name'  => $userName,
            'user'  => base64_decode($session['usuario']),
            'image' => $user[0]['imagen'],
            'email' => $user[0]['email'],
            'movil' => $user[0]['movil'],
            'phone' => $user[0]['telefono_fijo'],
            'area'  => $user[0]['area'],
            'mode'  => $user[0]['mode']
        ];
    } */
    protected function createPlainView($values)
    {
        $config = $this->configs->get('config');
        if (isset($values['view']) && !empty($values['view'])) {
            $viewParts = explode("/", $values['view']['name']);
            $title = $viewParts[0];
        }
        foreach ($config['colaboration'] as $colab) {
            if (isset($colab['theme'])) $theme = $colab['theme'];
            if (isset($colab['technology'])) $tech = $colab['technology'];
        }
        $tech[] = ['name' => "PHP " . phpversion(), 'url' => "http://www.php.net"];
        $response =  [
            'content' => $values,
            'layout' => [
                'head' => [
                    'template' => "_shared/templates/_head.tpl",
                    'data' => [
                        'author' => $config['author'],
                        'description' => $config['description'],
                        'lang' => $config['language'],
                        'app_name' => $config['app_name'],
                        'app_logo' => $config['app_logo'],
                        'title' => $title,
                        'version' => $config['shortversion'],
                        'app_url' => $config['app_url']
                    ],
                    'css' => '<link rel="stylesheet" type="text/css" href="\assets\css\style.css">'
                ],
                'body'=> ['layout'=>'', 'darkmode'=>null],
                'footer' => [
                    'tempalate' => "_shared/templates/_footer.tpl",
                    'data' => [
                        'version' => $config['version'],
                        'theme' => $theme,
                        'technology' => $tech,
                        'year' => $config['startYear'],
                        'companyURL' => $config['companyURL'],
                        'company' => $config['company']
                        ]
                    ],
                    'scripts' => ''
                    ]
                ];
                /* 'navbar' => [
                    'template' => "_shared/templates/_navbar.tpl",
                    'data' => [
                        'app_logo' => $config['app_logo'],
                    ]
                ], */
        return $response;
    }
    protected function getViewPath (array $view) :string {
        $type = $view['type'];
        $name = $view['name'];
        $path = _VIEW_;
        switch ($type) {
            case "view":
                $path .= $name . ".tpl";
                break;
            case "template":
                $nameSplited = explode("/", $name);
                $module = $nameSplited[0];
                $viewName = $nameSplited[1];
                $path .= $module . "/templates/" . $viewName . ".tpl";
                break;
            case "layout":
                $nameSplited = explode("/", $name);
                $module = $nameSplited[0];
                $viewName = $nameSplited[1];
                $path .= $module . "/layouts/" . $viewName . ".tpl";
                break;
        }
        return $path;
    }
}
?>
