<?php

/**
 * Clase que realiza la busqueda del controlador en la aplicación.
 * @description This file is used to search the controller in the application.
 * @category Class
 * @author Bytes4Run <jecheverria@bytes4run.com>
 * @package app\core\classes\Controller
 * @version 1.0.0 dev r2
 * @date 2023-05-02
 * @time 01:00:00
 */

declare(strict_types=1);

namespace app\core\classes;

use app\core\helpers\Config;
use app\core\helpers\Messenger;
use app\core\helpers\Router;
use app\core\helpers\ViewBuilder;
use Exception;
use Throwable;

class Controller
{
    /**
     * Contiene el nombre del módulo a realizarle la petición
     * @var string $module
     */
    private string $module;
    /**
     * Contiene el nombre del controlador al cuál se le hace la petición
     * @var string $controller
     */
    private string $controller;
    /**
     * Contiene el nombre del método al cual se le realiza la petición
     * @var string $method
     */
    private string $method;
    /**
     * Contiene la petición a realizarse
     * @var string|array $params
     */
    private array $params;
    /**
     * Contiene el método usado para realizar la petición
     * @var string $serverMethod
     */
    private string $serverMethod;
    /**
     * Contiene el arreglo de la configuración de la aplicación
     * @var object $configs
     */
    protected Config $configs;
    /**
     * Helper Messenger
     * @var object $messenger
     */
    protected Messenger $messenger;
    /**
     * Helper ViewBuilder
     * @var object $viewBuilder
     */
    protected ViewBuilder $viewBuilder;
    protected array $data;
    protected ?array $error;
    public $response;
    public function __construct()
    {
        $this->messenger = new Messenger;
        $this->configs = new Config;
        $this->viewBuilder = new ViewBuilder;
    }
    /**
     * Función parar resolver la peticón al controlador
     * @param array $values
     * @return void|array
     */
    public function run(Router $routes) :void
    {
        if (!empty($routes)) {
            $this->serverMethod = $routes->__get("http_method");
            if ($this->serverMethod) {
                $mtd = match ($this->serverMethod) {
                    "get" => "read",
                    "post" => "create",
                    "put" => "update",
                    "delete" => "delete",
                    default => "index"
                };
                $this->__set('method',$mtd);
                $this->__set('params',$routes->__get('params'));
                $request = $routes->__get("callback");
                if (count($request) > 0) {
                    if (count($request) == 1) {
                        $this->__set('module',$request[0]);
                        $this->__set('controller',$request[0]);
                    } elseif (count($request) == 2) {
                        $this->__set('module',$request[0]);
                        $this->__set('controller',$request[1]);
                    } elseif (count($request) == 3) {
                        $this->__set('module',$request[0]);
                        $this->__set('controller',$request[1]);
                        $this->__set('method',$request[2]);
                    } else {
                        $this->__set('module',$request[0]);
                        $this->__set('controller',$request[1]);
                        $this->__set('method',$request[2]);
                        $this->__set('params',$request[3]);
                    }
                    $this->getControllerResponse();
                } else {
                    $this->__set('module','home');
                    $this->__set('controller','home');
                }
            } else {
                $this->error = $this->messenger->build('error', [
                    'code' => 404,
                    'message' => "Method not found."
                ]);
            }
            $this->getControllerResponse();
        } else {
            $this->error =  $this->messenger->build('error', [
                'code' => 404,
                'message' => "No request."
            ]);
        }
    }
    /**
     * Función que devuelve la respuesta predeterminada de la aplicación, 
     * dependiendo de la acción a realizarse.
     * @return void|array
     */
    public function action(string $action) :void
    {
        if ($action == "login") {
            $this->getControllerResponse("accounts", "auth", "login");
        } elseif ($action == "home") {
            $this->getControllerResponse("home", "home", "index");
        } else {
            $this->error = $this->messenger->build('error', [
                'code' => 404,
                'message' => "Action not found."
            ]);
        }
    }
    /**
     * Busca un controller y devuelve el objeto.
     * @param string $controller Controlador a buscarse
     * @return array|object
     */
    public function getControllerInstance(string $controller) : object
    {
        $name = explode("/", $controller);
        if (count($name) > 1) {
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
    public function getModelInstance(string $model) : object
    {
        $name = explode("/", $model);
        /* if (count($name) > 2) {
            $response = $this->getComponent($name[0], "models", $name[1], $name[2]);
        } else */if (count($name) > 1) {
            $response = $this->getComponent($name[0], "models", $name[1]);
        } else {
            $response = $this->getComponent($model, "models");
        }
        return $response;
    }
    /**
     * Realiza la renderización de la vista solicitada.
     * Sí el motor de plantillas no está declarado o está declarado como "json", 
     * devuelve la vista en formato JSON.
     * @param array|string $values
     * @return void
     */
    public function render(array|string $values) :void
    {
        $this->viewBuilder->render($values);
    }
    public function view(string $view, array $data = array()) :array
    {
        return $this->createViewData($view, $data);
    }
    public function template(string $template, array $data = array())
    {
        return $this->createViewData($template, $data, "template");
    }
    #Seters
    public function __set(string $name, $value): void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }
    public function getError() :array|null {
        return $this->error;
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
     * @return void
     */
    protected function getControllerResponse(string $module = '', string $controller = '', string $method = '', $params = array()) :Controller
    {
        if (!empty($module)) {
            $this->module = $module;
        }
        if (!empty($controller)) {
            $this->controller = $controller;
        }
        if (!empty($method)) {
            $this->method = $method;
        }
        if (!empty($params)) {
            $this->params = $params;
        }
        if (!is_null($this->module) && !empty($this->module)) {
            if ($this->module == "default") {
                $vars = $this->configs->get('config');
                $this->module = $vars['default_ctr'];
                $this->controller = $vars['default_ctr'];
            }
            if (empty($this->controller) || is_null($this->controller)) {
                $this->response = $this->view('error/400',
                    $this->messenger->messageBuilder('message', 
                        $this->messenger->build('error', [
                            'code' => 400,
                            'message' => "Error, el controlador no inicializado."
                        ])
                    ));
            } else {
                $controllerInstance = $this->getComponent($this->module, "controller", $this->controller);
                if ($controllerInstance instanceof \Error) {
                    $this->response = $this->view('error/500',
                        $this->messenger->messageBuilder('message', 
                            $this->messenger->build('error',[
                                'message' => $controllerInstance->getMessage()
                            ])));
                } else {
                    if (method_exists($controllerInstance, $this->method)) {
                        try {
                            $this->response = $controllerInstance->$method($this->params);
                        } catch (Exception $ex) {
                            $this->response = $this->view('error/500',
                                $this->messenger->messageBuilder('message', 
                                    $this->messenger->build('error',[
                                        'message' => $ex->getMessage()
                                    ])
                                ));
                        } catch (Throwable $th) {
                            $this->response = $this->view('error/500',
                                $this->messenger->messageBuilder('message', 
                                    $this->messenger->build('error',[
                                        'message' => $th->getMessage()
                                    ])
                                ));
                        }
                    } else {
                        $this->response = $this->view('error/400',
                            $this->messenger->messageBuilder('message', 
                                $this->messenger->build('error',[
                                    'code' => 404,
                                    'message' => "Method Requested Is Not Valid"
                                ])
                            )
                        );
                    }
                }
            }
        } else {
            $this->response = $this->view('error/400',
                $this->messenger->messageBuilder('message', 
                    $this->messenger->build('error', [
                        'code' => 400,
                        'message' => "Error inicializar el modulo."
                    ])
                ));
        }
        return $this;
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
    protected function getComponent (string $moduleName, string $type = "controller", string $componentName = null)
    {
        if (is_null($componentName)) $componentName = $moduleName;
        if ($type == "model") $type = "models";
        if ($type == "controller") $type = "controllers";
        if ($type == "helper") $type = "helpers";
        if ($type == "handler") $type = "handlers";
        $componentFile = ucfirst($componentName);
        $component = "app\\modules\\" . $moduleName . "\\" . $type . "\\" . $componentFile;
        try {
            return new $component;
        } catch (Throwable $th) {
            return $th;
        } catch (Exception $ex) {
            return $ex;
        }
    }
    /**
     * Crea una respuesta de tipo vista, para el helper view
     * @param string $name
     * @param array $content
     * @param array $breadcrumbs
     * @param string $type
     * @param string|int $code
     * @param array $style
     * @return array
     */
    public function createViewData (string $name,array $content = [],string $type = 'template',array $breadcrumbs = [],string|int $code = '',array $style = []): array {
        if (!empty($name)) {
            if (empty($breadcrumbs)) $breadcrumbs = $this->createBreadcrumbs($name);
        }
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
    /**
     * Función que genera un arreglo de breadcrums.
     * @param string|array $values puede recibir una cade de caracteres con el nombre de la vista, ej.: "home/index"
     * o puede recibir un arreglo con los hijos de una vista, ej.:
     * ```php 
     * $arreglo = [
     *  'view'=>"home/index",
     *  'children'=>[
     *    'main'=>"zapatos",
     *    'module'=>"accesorios",
     *    'method'=>"list",
     *    'params'=>null
     *   ]
     * ]
     * ```
     * @return array
     */
    protected function createBreadcrumbs(string|array $values): array
    {
        $routes = array();
        $mdl = 'home';
        $ctr = 'home';
        $mtd = 'index';
        $prm = null;
        if (is_string($values)) {
            $name = explode("/", $values);
            if (sizeof($name) > 2) {
                $mdl = $name[0];
                $ctr = $name[0];
                $mtd = $name[1];
                $prm = $name[2];
            } else {
                $mdl = $name[0];
                $ctr = $name[0];
                $mtd = "index";
            }
            array_push($routes, [
                'text' => $mdl,
                'controller' => $ctr,
                'method' => $mtd,
                'param' => $prm
            ]);
        } else {
            if (isset($values['view'])) $name = explode("/", $values['view']);
            if (sizeof($name) > 1) {
                $mdl = $name[0];
                $ctr = $name[0];
            }
            foreach ($values['children'] as $child) {
                $mdl = ($child['main']) ?? $child['module'];
                $ctr = $child['module'];
                $mtd = $child['method'];
                if (isset($child['params'])) {
                    if (is_array($child['params'])) {
                        $prm = implode("|", $child['params']);
                    } else {
                        $prm = $child['params'];
                    }
                }
                array_push($routes, [
                    'text' => $mdl,
                    'controller' => $ctr,
                    'method' => $mtd,
                    'param' => $prm
                ]);
            }
        }
        return [
            'main' => $mdl,
            'routes' => $routes
        ];
    }
}
