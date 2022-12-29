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
        $this->smarty->setConfigDir(_CONF_ . "smartyconfig");
        $this->smarty->setCacheDir(_CACHE_ . 'smartycache/');
        $this->smarty->setCompileDir(_CACHE_);
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
    public function find(string $viewName) :bool
    {
        if (is_string($viewName)) {
            $path = _VIEW_ . $viewName;
            return file_exists($path);
        } else {
            return false;
        }
    }
    /**
     * Renderiza la vista a ser presentada
     * @param array $viewData Datos de la vista a ser renderizada
     * @return bool
     */
    public function build(array $viewData) :bool
    {
        $path = _VIEW_ . $viewData['view'];
        $datos = $this->createView($viewData);
        if ($this->smarty->templateExists($path)) {
            $this->smarty->assign('data', $datos);
            $this->smarty->display($path);
            $response = true;
        } else {
            $response = false;
        }
        return $response;
    }
    /**
     * Usada por la función build, genera los datos adicionales para las vistas
     * @param mixed $values
     * @return array
     */
    protected function createView($values)
    {
        $config = $this->configs->get();
        $userData = $this->auth->getSessionData('all');
        if (isset($values['view']) && !empty($values['view'])) {
            $viewParts = explode("/", $values['view']);
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
            'content' => $values['content'],
            'layout' => [
                'head' => [
                    'template' => "_shared/_head.tpl",
                    'data' => [
                        'author' => $config['author'],
                        'description' => $config['description'],
                        'lang' => $config['language'],
                        'app_name' => $config['appName'],
                        'app_logo' => $config['appLogo'],
                        'title' => $title,
                        'version' => $config['shortversion'],
                        'app_url' => $config['app_url']
                    ],
                    'css' => ''
                ],
                'navbar' => [
                    'template' => "_shared/_navbar.tpl",
                    'data' => [
                        'app_logo' => ($userData['mode'] == "dark") ? $config['darkLogo'] : $config['appLogo'],
                        'user' => $userData
                    ] //(isset($userData['ops'])) ? ['darkmode' => $userData['ops']['darkmode']] : []
                ],
                'sidebar' => [
                    'template' => "_shared/_sidebar.tpl",
                    'data' => [
                        'app_logo' => $config['appLogo'],
                        'user_name' => (isset($userData['name'])) ? $userData['name'] : "",
                        'user_image' => "assets/img/avatar5.png",
                    ]
                ],
                'footer' => [
                    'tempalate' => "_shared/_footer.tpl",
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
        /* echo '<pre>';
            var_dump($response);
            echo '</pre>';
            exit; */
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
}
?>