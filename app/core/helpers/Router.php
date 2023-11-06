<?php

/**
 * Ayudante para resolver la ruta proporcionada
 * @description Helper to resolve the provided route
 * @category Helper
 * @author JEcheverria <jecheverria@piensads.com>
 * @package app\core\helpers\Router
 * @version 1.0.0 rev. 1
 * @Time: 2021-04-27 19:00:00
 */

declare(strict_types=1);

namespace app\core\helpers;

class Router
{
    /**
     * Url de la ruta
     * @var string
     */
    private string $url;
    /**
     * Request de la ruta
     * @var array
     */
    public array $request;
    /**
     * Método de la petición
     * @var string
     */
    protected string $http_method = "GET";
    /**
     * Callback de la ruta
     * @var array
     */
    protected array $callback = array();
    /**
     * Parámetros de la ruta
     * @var array
     */
    protected array $params = array();
    /**
     * Función para extraer la ruta
     * @return array
     */
    public function getPath(): array
    {
        $this->url = $_SERVER['REQUEST_URI'];
        if ($this->url == '/' || $this->url == "/index.php" || $this->url == "/index.html") {
            $this->url = '';
        } else {
            $this->url = substr($this->url, 1);
        }
        $re = '/\?/';
        if (preg_match($re, $this->url)) {
            return preg_split($re, $this->url, -1, PREG_SPLIT_NO_EMPTY);
        } else {
            return (!empty($this->url)) ? explode("/", $this->url) : array();
        }
    }
    /**
     * Función para obtener el método de la petición
     * @return string
     */
    public function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
    /**
     * Función que devuelve la ruta resuelta
     * @return array
     */
    public function resolve(): array
    {
        $path = $this->getPath(); // Obtiene la ruta
        $method = $this->getMethod(); // Obtiene el método
        $this->request = array();
        if (!empty($path)) {
            if ($path[0] == "assets" || $path[0] == "css" || $path[0] == "js" || $path[0] == "img") {
                $this->request = [
                    'resource' => $path[0],
                    'file' => implode("/", $path)
                ];
            } else {
                if ($method == "post" || $method === "put" || $method === "patch") {
                    $this->params = (!empty($_POST)) ? $_POST : array();
                    if (empty($this->params)) {
                        $result = json_decode(file_get_contents('php://input'), true);
                        if ($result != false || $result != null) {
                            $this->params = $result;
                        }
                    }
                    $this->callback = $path;
                    $this->request = $path;
                    $this->request['params'] = $this->params;
                } elseif ($method == "get") {
                    if (!empty($path[0])) {
                        $this->callback = (!empty($path[0])) ? explode("/", $path[0]) : array();
                        $this->params = $this->createParams($path[1]);
                    } else {
                        $this->callback = (!empty($url)) ? explode("/", $url) : array();
                    }
                    $this->request = $this->callback;
                    $this->request['params'] = $this->params;
                }
            }
        }
        $this->http_method = $method;
        return $this->request;
    }
    public function __get(string $args) {
        if (isset($this->$args)) {
            return $this->$args;
        } else if (isset($this->params[$args])) {
            return $this->params[$args];
        } else {
            return null;
        }
    }
    /**
     * Función que crea el arreglo de parametros
     * @param string $uri Uri de la petición
     * @return array
     */
    protected function createParams(string $uri): array
    {
        if (empty($uri)) return array();
        $uriArray = explode("&", $uri);
        $uriParameters = array();
        foreach ($uriArray as $param) {
            $parameter = explode("=", $param);
            if ($parameter[0] != "_") {
                $uriParameters[$parameter[0]] = $parameter[1];
            }
        }
        return (!empty($uriParameters)) ? $uriParameters : array();
    }
    /**
     * Función para validar la ruta
     * @param array $raw Ruta a validar
     * @throws \Exception
     * @return array
     */
    private function validatepath(array $raw): array
    { 
        $response = [
            'module' => "homes",
            'control' => "home",
            'method' => "index",
            'params' => array(),
        ];
        if (!empty($raw)) {
            if ($raw[0] == "home") {
                if (count($raw) == 1) {
                    return $response;
                } else {
                    if (count($raw) > 3) {
                        $response['module'] = $raw[0];
                        $response['control'] = $raw[1];
                        $response['method'] = $raw[2];
                        for ($i = 0; $i < 2; $i++) {
                            unset($raw[$i]);
                        }
                        $response['params'] = $raw;
                    } elseif (count($raw) == 3) {
                        $response['module'] = $raw[0];
                        $response['control'] = $raw[1];
                        $response['method'] = $raw[2];
                    }
                    for ($i = 1; $i < count($raw); $i++) {
                        $response['params'][] = $raw[$i];
                    }
                    return $response;
                }
            } else {
                try {
                    $cuenta = count($raw);
                    $path = _MODULE_;
                    $response['module'] = $raw[0];
                    if ($cuenta == 1) {
                        $response['control'] = $raw[0];
                    } elseif ($cuenta == 2) {
                        $path .= $raw[0] . "/controllers/" . ucfirst($raw[1]) . ".php";
                        if (file_exists($path)) {
                            $response['control'] = $raw[1];
                        } else {
                            $response['control'] = $raw[0];
                            //$component = $this->getComponent($raw[0]);
                            //if (is_array($component)) {
                            //    throw new \Exception("Error Processing Request.<br>Controller not valid", 1);
                            //} else {
                                //$ctr = $raw[1];
                                //if (method_exists($component, $ctr)) {
                                    $response['method'] = $raw[1];
                                    //$response['method'] = $raw[1];
                                //} else {
                                //    $response['params'] = $raw[1];
                                //}
                            //}
                        }
                    } elseif ($cuenta == 3) {
                        $path .= $raw[0] . "/controllers/" . ucfirst($raw[1]) . ".php";
                        if (file_exists($path)) {
                            $response['control'] = $raw[1];
                            $response['method'] = $raw[2];
                        } else {
                            $response['control'] = $raw[0];
                            //$component = $this->getComponent($raw[0]);
                            //if (is_array($component)) {
                            //    throw new \Exception("Error Processing Request.<br>Controller not valid", 1);
                            //} else {
                            //    if (method_exists($component, $ctr)) {
                            //        $response['method'] = $raw[1];
                            //        $rawArray['params'] = $raw[2];
                            //    } else {
                            //        throw new \Exception("Error Processing Request.<br>Method not available", 1);
                            //    }
                            //}
                            $response['method'] = $raw[1];
                            $response['params'][0] = $raw[2];
                        }
                    } elseif ($cuenta > 3) {
                        $path .= $raw[0] . "/controllers/" . ucfirst($raw[1]) . ".php";
                        if (file_exists($path)) {
                            $response['control'] = $raw[1];
                            $response['method'] = $raw[2];
                            for ($i = 0; $i < 3; $i++) {
                                unset($raw[$i]);
                            }
                            $response['params'] = $raw;
                        } else {
                            $response['control'] = $raw[0];
                            /* $component = $this->getComponent($raw[0]);
                            if (is_array($component)) {
                                throw new \Exception("Error Processing Request.<br>Controller not valid", 1);
                            } else {
                                if (method_exists($component, $ctr)) {
                                } else {
                                    throw new \Exception("Error Processing Request.<br>Method not available", 1);
                                }
                            } */
                            $response['method'] = $raw[1];
                            for ($i = 0; $i < 2; $i++) {
                                unset($raw[$i]);
                            }
                            $response['params'] = $raw;
                        }
                    }
                } catch (\Exception $e) {
                    return [
                        'module' => "error",
                        'control' => "error",
                        'method' => "rutes",
                        'params' => $e->getMessage(),
                    ];
                } catch (\Throwable $e) {
                    return [
                        'module' => "error",
                        'control' => "error",
                        'method' => "rutes",
                        'params' => $e->getMessage(),
                    ];
                }
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
     * @param string|null $controllerName Nombre del componente
     * @param string|null $className Nombre de la clase
     * @return array<array>|object
     */
    /* protected function getComponent(string $moduleName, string $controllerName = null, string $className = null)
    {
        $path = $moduleName . "/controllers/";
        $componentFile = (!is_null($controllerName)) ? ucfirst($controllerName) : $componentFile = ucfirst($moduleName);
        $path .= $componentFile;
        $componentNameSpace = "app/modules/$path";
        $componentToUse = str_replace('/', '\\', $componentNameSpace);
        try {
            if (!is_null($className)) {
                $class = ucfirst($className);
                $response = new $componentToUse;
                $response = $response->$class();
            } else {
                $response = new $componentToUse;
            }
        } catch (\Throwable $th) {
            return [
                'module' => "error",
                'control' => "error",
                'method' => "rutes",
                'params' => [
                    'type' => "error",
                    'code' => 404,
                    'data' => $th->__toString()
                ]
            ];
        } catch (\Exception $exepcion) {
            return [
                'module' => "error",
                'control' => "error",
                'method' => "rutes",
                'params' => [
                    'type' => "error",
                    'code' => 500,
                    'data' => $exepcion->__toString()
                ]
            ];
        }
        return $response;
    } */
}
