<?php

/**
 * @category Helper
 * @author JEcheverria <jecheverria@piensads.com>
 * @version 1.0.0
 * Clase que resuelve la ruta proporcionada.
 */

namespace app\core\helpers;

/**
 * Summary of RouterHelper
 * @package app\core\helpers\RouterHelper
 */
class RouterHelper
{
    private $qmPos;
    private $url;
    public function getPath()
    {
        $this->url = $_SERVER['REQUEST_URI'] ?? '/';
        $this->qmPos = strpos($this->url, '?');
        return ($this->qmPos === false) ? $this->url : substr($this->url, 1, ($this->qmPos - 1));
    }
    public function getMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
    public function resolve()
    {
        $path = $this->getPath();
        $method = $this->getMethod();
        if ($path == "/") {
            $response = [
                'sv_method' => $method,
                'app_module' => "home",
                'app_class' => "home",
                'app_method' => "index",
                'app_params' => null
            ];
        } else {
            $pathArray = explode('/', $path);
            if ($method == "post") {
                array_shift($pathArray);
                $prms = $_POST;
            } else {
                if ($this->qmPos != false) {
                    $params = substr($this->url, ($this->qmPos + 1));
                    $prms = $this->createParams($params);
                } else {
                    array_shift($pathArray);
                    $prms = null;
                }
            }
            $mtd = "index";
            $pathArraySize = sizeof($pathArray);
            $mdl = $pathArray[0];
            $ctr = $pathArray[0];
            $response = [];
            switch ($pathArraySize) {
                case 5:
                    $ctr = $pathArray[1];
                    $mtd = $pathArray[3];
                    if (is_null($prms)) $prms = $pathArray[4];
                    break;
                case 4:
                    $mtd = $pathArray[2];
                    if (is_null($prms)) $prms = $pathArray[3];
                    break;
                case 3:
                    $mtd = $pathArray[1];
                    if (is_null($prms)) $prms = $pathArray[2];
                    break;
                default:
                    $mtd = ($pathArray[1]) ?? 'index';
                    break;
            }
            $response = [
                'sv_method' => $method,
                'app_module' => $mdl,
                'app_controller' => $ctr,
                'app_method' => $mtd,
                'app_params' => $prms
            ];
        }
        return $response;
    }
    protected function createParams(string $uri)
    {
        $uriArray = explode("&", $uri);
        $uriParameters = array();
        foreach ($uriArray as $param) {
            $parameter = explode("=", $param);
            $uriParameters[$parameter[0]] = $parameter[1];
        }
        return $uriParameters;
    }
}
