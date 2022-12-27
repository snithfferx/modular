<?php
    namespace app\core;
    /**
     * Class Loader
     * @author Snithfferx <jecheverria@bytes4run.com>
     * @package app\core
     * @version 1.0.0
     */
    use app\core\classes\ControllerClass;
    use app\core\libraries\AuthenticationLibrary;
    use app\core\helpers\MessageHelper;
    use app\core\helpers\ViewBuilderHelper;
    /**
     * Clase de carga de la aplicación
     */
    class LoaderClass {
        private $controller;
        private $userAlive;
        private $auth;
        private $viewBuildeer;
        private $messanger;
        function __construct() {
            $this->controller = new ControllerClass;
            $this->auth = new AuthenticationLibrary;
            $this->viewBuildeer = new ViewBuilderHelper;
            $this->messanger = new MessageHelper;
        }
        function verifyRequest () {
            $url = $_SERVER['REQUEST_URI'] ?? '/';
            $serverRequestMethod = strtolower($_SERVER['REQUEST_METHOD']);
            $pos = strpos($url,'?');
            $path = ($pos === false) ? $url : substr($url,1,($pos - 1));
            if ($path == "/") {
                $response = $this->controller->getResponse([
                    'sv_method'=>$serverRequestMethod,
                    'app_module'=>"home",
                    'app_class' => "home",
                    'app_method'=>"index",
                    'app_params'=>null
                ]);
            } else {
                $pathArray = explode('/', $path);
                if ($pos != false) {
                    $params = substr($url,$pos);
                }else {
                    array_shift($pathArray);
                    $params = null;
                }
                $method = "index";
                $pathArraySize = sizeof($pathArray);
                $controller = $pathArray[0];
                $class = $pathArray[0];
                $request = [];
                if ($pathArraySize > 3) {
                    $class = $pathArray[1];
                    $method = $pathArray[2];
                    $params = $pathArray[3];
                } elseif ($pathArraySize > 2) {
                    $method = $pathArray[1];
                    $params = $pathArray[2];
                } elseif ($pathArraySize > 1) {
                    $method = $pathArray[1];
                } else {
                    $request = $_GET;
                }
                if (empty($request)) {
                    $request = [
                        'sv_method' => $serverRequestMethod,
                        'app_module' => $controller,
                        'app_class' => $class,
                        'app_method' => $method,
                        'app_params' => $params];
                }
                /* if ($this->userAlive == false) {
                    if (!empty($_REQUEST) && $_REQUEST['ctr'] == "users") {
                        $response = $this->controller->getResponse($_REQUEST);
                    } else {*/
                        $response = $this->controller->getResponse($request);
                    //}
                //} else {
                  //  $response = (empty($_REQUEST)) ? $this->controller->getDefaultResponse() : $this->controller->getResponse($_REQUEST);
                //}
            }
            return $response;
        }
        function display ($values) {
            return $this->controller->view($values);
        }
        function init () {
            //$this->userAlive = $this->auth->isSessionStarted();
            return true;
        }
        function terminate () {
            $this->controller = null;
            $this->userAlive = null;
        }
    }
?>