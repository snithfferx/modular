<?php

namespace app\core\classes;

use app\core\helpers\ConfigHelper;
use app\core\helpers\MessengerHelper;

/**
 * Clase que analiza y busca el controlador en la aplicación.
 * @category File
 * @author Jorge Rivera
 * @package app\core\classes\ControllerClass
 * @version 1.3.0 dev r1
 */
class ControllerClass
{
    /**
     * Contiene el nombre del controlador al cuál se le hace la petición
     * @var string $controller
     */
    private $controller;
    /**
     * Contiene el nombre del método al cual se le realiza la petición
     * @var string $method
     */
    private $method;
    /**
     * Contiene la petición a realizarse
     * @var string|array $params
     */
    private $params;
    /**
     * Contiene el arreglo de la configuración de la aplicación
     * @var object $configs
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
     * Helper Messenger
     * @var object $messenger
     */
    private $messenger;
    public function __construct()
    {
        $this->messenger = new MessengerHelper;
        $this->configs = new ConfigHelper;
    }
    /**
     * Función parar resolver la peticón al controlador
     * @param array $values
     * @return array
     */
    public function getResponse($values)
    {
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
                $this->params
            );
        } else {
            $response = $this->messenger->build('message', ['type' => "error", 'data' => ['code' => 404, 'message' => "No request."]]);
        }
        return $response;
    }
    /**
     * Función que devuelve la respuesta predeterminada de la aplicación.
     * @return array
     */
    public function getDefaultResponse()
    {
        return $this->getControllerResponse("default");
    }
    /**
     * Busca un controller y devuelve el objeto.
     * @param string $controller Controlador a buscarse
     * @return array<array>|object
     */
    public function getControllerInstance(string $controller)
    {
        $name = explode("/", $controller);
        if (count($name) > 2) {
            $response = $this->getComponent($name[0], $name[1], $name[2]);
        } elseif (count($name) > 1) {
            $response = $this->getComponent($name[0], $name[1]);
        } else {
            $response = $this->getComponent($controller);
        }
        return $response;
    }
    /**
     * Devuelve el objecto del modelo buscado
     * @param string $model Modelo a ser instanciado
     * @return array<array>|object
     */
    public function getModelInstance(string $model)
    {
        $name = explode("/", $model);
        if (count($name) > 2) {
            $response = $this->getComponent($name[0], "models", $name[1], $name[2]);
        } elseif (count($name) > 1) {
            $response = $this->getComponent($name[0], "models", $name[1]);
        } else {
            $response = $this->getComponent($model, "models");
        }
        return $response;
    }

    /**
     * Devuelve la respuesta del controlador solicitado.
     *
     * Devuelve la respuesta en formato array conteniendo la vista a ser renderizada.
     *
     * @param string $module Nombre del modulo solicitado
     * @param string|null $controller Controlador solitado del modulo
     * @param string|null $class ~Nombre de la clase a solicitar en el controller~(deprecated)
     * @param string $method Nombre del método a realizarle la petición
     * @param array|null $params Contiene el arreglo de parametros a ser usados por el método; puede estár vacio o no declarado
     * @return array
     */
    protected function getControllerResponse(string $module, string $controller = null, string $class = null, string $method = "index", $params = null): array
    {
        if (!empty($module)) {
            if ($module == "default") {
                $vars = $this->configs->get('config');
                $module = $vars['default_ctr'];
                $controller = $vars['default_ctr'];
                $class = $vars['default_ctr'];
            }
            if (empty($controller) || is_null($controller)) {
                $response = $this->messenger->build('message', [
                    'type' => "error",
                    'data' => [
                        'code' => 404,
                        'message' => "Error, el modulo no existe"
                    ]
                ]);
            } else {
                $controllerInstance = $this->getComponent($module, "controllers", $controller, $class);
                if (!is_null($controllerInstance) && !is_array($controllerInstance)) {
                    if (method_exists($controllerInstance, $method)) {
                        try {
                            $response = $controllerInstance->$method($params);
                        } catch (\Exception $exepcion) {
                            $response = $this->messenger->build('message', ['type' => "error", 'data' => [
                                'code' => 404,
                                'message' => $exepcion->getMessage()
                            ]]);
                        }
                    } else {
                        $response = $this->messenger->build('message', ['type' => "error", 'data' => [
                            'code' => 404,
                            'message' => "Method Requested Is Not Valid"
                        ]]);
                    }
                    return $response;
                }
                return $controllerInstance;
            }
        }
        return $response;
    }
    /**
     * Función que devuelve el objeto del componente solicitado
     *
     * Devuvle un objeto del componente solicitado por la aplicación, este debe estar en formato SPR-4
     *
     * @param string $moduleName Nombre del modulo
     * @param string $type Tipo de componente
     * @param string|null $componentName Nombre del componente
     * @param string|null $className Nombre de la clase
     * @return array<array>|object
     */
    protected function getComponent(string $moduleName, string $type = "controllers", string $componentName = null, string $className = null)
    {
        $path = _MODULE_;
        $path .= $moduleName . "/" . $type . "/";
        $componentFile = ucfirst($componentName);
        $componentClass = ucfirst($className);
        $componentFile .= (!is_null($className) && $className != $componentName) ? "_" . $componentClass . "Controller" : "Controller";
        $path .= "$componentFile.php";
        if (file_exists($path)) {
            try {
                $componentNameSpace = "app/modules/$moduleName/$type/$componentFile";
                $componentToUse = str_replace('/', '\\', $componentNameSpace);
                $response = new $componentToUse;
            } catch (\Exception $exepcion) {
                $response = $this->messenger->build('message', [
                    'type' => "error", 'data' => [
                        'code' => 404,
                        'message' => $exepcion->getMessage()
                    ]
                ]);
            }
        } else {
            $response = $this->messenger->build('message', ['type' => "error", 'data' => [
                'code' => 404,
                'message' => "Error, el modulo no existe"
            ]]);
        }
        return $response;
    }
    public function createViewData(string $name, array $content = [], array $breadcrumbs = [], string $type = 'template', $code = '', array $style = [])
    {
        if (empty($breadcrumbs)) $breadcrumbs = $this->createBreadcrumbs($name);
        return [
            'view' => [
                'type' => $type,
                'name' => $name,
                'data' => [
                    'code' => $code,
                    'style' => $style
                ],
            ],
            'data' => [
                'breadcrumbs' => $breadcrumbs,
                'datos' => $content
            ]
        ];
    }
    protected function createBreadcrumbs(string|array $values): array
    {
        if (is_string($values)) {
            $name = explode("/", $values);
            $mdl = $name[0];
            $ctr = $name[0];
            $mtd = $name[1];
        } else {
            $name = explode("/", $values['view']);
            $mdl = $name[0];
            $ctr = $name[0];
            $prm = (isset($values['params']['id'])) ? $values['params']['id'] : $values['params'];
            $mtd = $values['method'];
        }
        return [
            'main' => $mdl,
            'routes' => [
                [
                    'text' => $mdl,
                    'controller' => $mdl,
                    'method' => 'read',
                    'param' => null
                ],
                [
                    'text' => $mdl,
                    'controller' => $ctr,
                    'method' => $mtd ?? null,
                    'param' => $prm ?? null
                ]
            ]
        ];
        /* ['text' => 'home', 'controller' => 'home', 'method' => 'index', 'param' => []] */
    }
}
