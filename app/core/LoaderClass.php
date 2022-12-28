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
        /**
         * @var object Contiene el objeto de la clase controller
         */
        private $controller;
        /**
         * @var bool Contiene la resolución sí el usuario está o no logueado.
         */
        private $userAlive;
        /**
         * @var object Contiene ela resolución sí el objeto de la clase authentication
         */
        private $auth;
        /**
         * @var object Contiene el objeto de la clase viewbuilder
         */
        private $viewBuildeer;
        /**
         * @var object Contiene el objeto de la clase messenger
         */
        private $messanger;
        /**
         * constructor
         */
        function __construct() {
            $this->controller = new ControllerClass;
            $this->auth = new AuthenticationLibrary;
            $this->viewBuildeer = new ViewBuilderHelper;
            $this->messanger = new MessageHelper;
        }
        /**
         * Función que realiza la verificación de las distintas partes de la request
         * para resolverla
         * @return array
         */
        function verifyRequest () :array {
            $url = $_SERVER['REQUEST_URI'] ?? '/';
            $srvRqstMtd = strtolower($_SERVER['REQUEST_METHOD']);
            return $this->routeResolve($srvRqstMtd,$url);
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
        protected function routeResolve(string $method,string $uri) :array {
            $pos = strpos($uri, '?');
            $path = ($pos === false) ? $uri : substr($uri, 1, ($pos - 1));
            if ($path == "/") {
                $response = $this->controller->getResponse([
                    'sv_method' => $method,
                    'app_module'=> "home",
                    'app_class' => "home",
                    'app_method'=> "index",
                    'app_params'=> null
                ]);
            } else {
                $pathArray = explode('/', $path);
                if ($pos != false) {
                    $prms = substr($uri, $pos);
                } else {
                    array_shift($pathArray);
                    $prms = null;
                }
                $mtd = "index";
                $pathArraySize = sizeof($pathArray);
                $ctr = $pathArray[0];
                $clss = $pathArray[0];
                $request = [];
                if ($pathArraySize > 3) {
                    $clss = $pathArray[1];
                    $mtd = $pathArray[2];
                    $prms = $pathArray[3];
                } elseif ($pathArraySize > 2) {
                    $mtd = $pathArray[1];
                    $prms = $pathArray[2];
                } elseif ($pathArraySize > 1) {
                    $mtd = $pathArray[1];
                } else {
                    $request = $_GET;
                }
                if (empty($request)) {
                    $request = [
                        'sv_method' => $method,
                        'app_module'=> $ctr,
                        'app_class' => $clss,
                        'app_method'=> $mtd,
                        'app_params'=> $prms
                    ];
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
    }
?>