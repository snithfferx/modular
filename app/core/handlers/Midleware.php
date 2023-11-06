<?php
/**
 * Clase que será el corredor entre el usuario y la aplicación.
 * @description This class is the broker between users and aplication.
 * @category handler
 * @author snithfferx <snithfferx@outlook.com>
 * @package   app\core\handlers\Midleware
 * @version 1.0.0
 * Time: 2023-16-04 13:19:00
 */
declare (strict_types = 1);
namespace app\core\handlers;

use app\core\handlers\Authorization;

class Midleware
{
    public $headers;
    public $state;
    private $token;
    private $auth;
    public function __construct()
    {
        $this->headers = $this->requestHeaders();
        $httpAuth = $this->authStatus($this->headers);
        if ($httpAuth['state'] === true) {
            $this->state = $httpAuth['state'];
        } else {
            $this->state = false;
        }
    }
    public function set (string $element, array $data) {
        if ($element === 'state') {
            $this->state = $data;
        } elseif ($element === 'headers') {
            $this->headers = $this->makeHeaders($data);
        }
        return $this;
    }
    public function get (string $element) {
        if ($element === 'state') {
            return $this->state;
        } elseif ($element === 'headers') {
            return $this->headers;
        } elseif ($element === 'token') {
            return $this->token;
        }
    }

    private function requestHeaders()
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) != 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }
        return $headers;
    }
    private function authStatus($headers)
    {
        if (isset($headers['Authorization'])) {
            return ['state' => true, 'token' => $headers['Authorization']];
        } else {
            return ['state' => false, 'token' => null];
        }
    }
    private function makeHeaders (array $headers) {
        // make http headers for each element in array
        $httpHeaders = [];
        foreach ($headers as $key => $value) {
            $httpHeaders[] = strtoupper($key) . ': ' . $value;
        }
        return $httpHeaders;
    }
    /**
     *  An example CORS-compliant method.  It will allow any GET, POST, or OPTIONS requests from any
     *  origin.
     *
     *  In a production environment, you probably want to be more restrictive, but this gives you
     *  the general idea of what is involved.  For the nitty-gritty low-down, read:
     *
     *  - https://developer.mozilla.org/en/HTTP_access_control
     *  - https://fetch.spec.whatwg.org/#http-cors-protocol
     *
     */
    function cors()
    {

        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
            // you want to allow, and if so:
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 3600');    // cache for 1 hour
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, HEAD, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }
        return true;
    }
}
