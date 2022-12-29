<?php
    namespace app\core;
    /**
     * Class Loader
     * @author Snithfferx <jecheverria@bytes4run.com>
     * @package app\core
     * @version 2.0.5 dev r1
     */
    use app\core\classes\ControllerClass;
    use app\core\libraries\AuthenticationLibrary;
    use app\core\helpers\MessengerHelper;
    use app\core\helpers\ViewBuilderHelper;
    use app\core\helpers\RouterHelper;
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
        private $viewBuilder;
        /**
         * @var object Contiene el objeto de la clase messenger
         */
        private $messenger;
        public $route;
        /**
         * constructor
         */
        function __construct() {
            $this->controller = new ControllerClass;
            $this->auth = new AuthenticationLibrary;
            $this->viewBuilder = new ViewBuilderHelper;
            $this->messenger = new MessengerHelper;
            $this->route = new RouterHelper;
        }
        /**
         * Función que realiza la verificación de las distintas partes de la request
         * para resolverla
         * @return array
         */
        function verifyRequest () :array {
            $path = $this->route->resolve();
            /* if ($this->userAlive == false) {
                if ($path['app_module'] == "users") {
                    $response = $this->controller->getResponse($path);
                } else {
                    $response = $this->messenger->buildError(401);
                }
            } else {*/
                $response = $this->controller->getResponse($path);
            //} */
            return $response;
        }
        function display ($values) {
            return $this->renderView($values);
        }
        function init () :bool {
            $this->userAlive = $this->auth->isSessionStarted();
            return true;
        }
        function terminate () :void {
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
        private function renderView ($values) :string {
            if (isset($values['view'])) {
                if ($this->viewBuilder->find($values['view'])) {
                    $response = $this->viewBuilder->build($values);
                } else {
                    $response = $this->viewBuilder->build($this->messenger->build(['type'=>"error",'data'=>['code'=>404, $values]]));
                }
            } else {
                $response = (is_string($values)) ? $values : json_encode($values);
            }
            return $response;
        }
    }
?>