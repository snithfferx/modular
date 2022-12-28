<?php
    namespace app\core\classes;
    use app\core\helper\configHelper;
    use app\core\libraries\AuthenticationLibrary;
    /**
     * Clase que analiza y busca el controlador en la aplicación.
     * @category File
     * @author Jorge Rivera
     * @version 1.1.0
     */
    class ControllerClass {
        /**
         * Contiene el nombre del controlador al cuál se le hace la petición
         * @var string controller
         */
        private $controller;
        /**
         * Contiene el nombre del método al cual se le realiza la petición
         * @var string method
         */
        private $method;
        /**
         * Contiene la petición a realizarse
         * @var string|array params
         */
        private $params;
        /**
         * Contiene el arreglo de la configuración de la aplicación
         * @var array $configs
         */
        private $configs;
        /**
         * Contiene el método usado para realizar la petición
         * @var string $serverMethod
         */
        private $serverMethod;
        /**
         * Contiene el nombre del módulo a realizarle la petición
         * @var string $module
         */
        private $module;
        /**
         * Clase a la que se le realiza la petición
         * @var string $class
         */
        private $class;
        /**
         * Función parar resolver la peticón al controlador
         * @param array $values
         * @return array
         */
        public function getResponse($values) {
            if (!empty($values)) {
                if (isset($values['ctr'])) {
                    $this->serverMethod = "get";
                    $this->module = $values['ctr'];
                    $this->controller = $values['ctr'];
                    $this->class = $values['ctr'];
                    $this->method = $values['mtd'];
                    $this->params = $values['prm'];
                } else {
                    $this->serverMethod = ($values['sv_method']) ?? false;
                    $this->module = $values['app_module'] ?? 'default';
                    $this->controller = ($values['app_module']) ?? 'default';
                    $this->class = ($values['app_class']) ?? $this->controller;
                    $this->method = ($values['app_method']) ?? 'index';
                    $this->params = ($values['app_params']) ?? null;
                }
                $response = $this->getControllerResponse(
                    $this->module,
                    $this->controller,
                    $this->class,
                    $this->method,
                    $this->params);/* 
                $response = "Petici&oacute;n realizada con m&eacute;todo: $this->serverMethod.<br>
                    Hacia el m&oacute;dulo: $this->module.<br>
                    Usando el controller: $this->controller.<br>
                    Con Clase: $this->class.<br>
                    Usando la funci&oacute;n: $this->method.<br>
                    Con parametros: $this->params."; */
            } else {
                $response = $this->getDefaultResponse();
            }
            return $response;
        }
        public function getDefaultResponse () {
            return "Respuesta predeterminada";
        }
        public function view () {
            
        }
        public function getController () {}
        //public function get

        protected function getControllerResponse (string $module, string $controller, string $class, string $method, $params) {
            if (!empty($module)) {
                if ($module == "default") {
                    $module = $this->getConfigVars['default_ctr'];
                }
                if (empty($controller) || is_null($controller)) {
                    $response = $this->createMessage("view",[
                        'type'=>"error",
                        'message'=>"Error, el modulo no existe"]);
                } else {
                    $path = _MODULE_;
                    $controller = ucfirst($controller) . "Controller";
                    $path .= ucfirst($module) . "Module/";
                    $path .= "$controller.php";
                    if (file_exists($path)) {
                        try {
                            require_once($path);
                            if (class_exists($class)) {
                                $ctr = new $class;
                                if (method_exists($ctr,$method)) {
                                    try {
                                        $response = $ctr->$method($params);
                                    } catch (\Exception $exepcion) {
                                        $response = $this->createMessage("view",[
                                            'type'=>"error",
                                            'message'=>$exepcion->getMessage()]);
                                    }
                                } else {

                                }
                            } else {
                                $response = $this->createMessage("view",[
                                    'type'=>"error",
                                    'message'=>"Lo sentimos, el metodo no existe"]);
                            }
                        } catch (\Exception $exepcion) {
                            $response = $this->createMessage("view",[
                                'type'=>"error",
                                'message'=>$exepcion->getMessage()]);
                        }
                    } else {
                        $response = $this->createMessage("view",[
                            'type'=>"error",
                            'message'=>"Error, el modulo no existe"]);
                    }
                }
            } else {
                $response = $this->createMessage("view",[
                    'type'=>"error",
                    'message'=>"Error, el modulo no existe"]);
            }
            return $response;
        }
        protected function createMessage () {
            return "holea";
        }
        protected function isMethod () {

        }
        private function getConfigVars () {
            //$cnfg = new configHelper;
            //$this->configs = $cnfg->getConfigVars();
        }
    }
?>