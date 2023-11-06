<?php
/**
 * @category Application
 * @package  app\Loader
 * @author   snirthfferx <jecheverria@bytes4run.com>
 * @version  1.5.0
 * Time: 2023-10-30 18:00:00
 */
declare (strict_types = 1);
namespace app;

require 'core/helpers/Definer.php';
use app\core\classes\Controller;
use app\core\handlers\Authorization;
use app\core\handlers\Midleware;
use app\core\helpers\Router;

class Loader
{
    private Authorization $auth;
    private Router $routes;
    private Midleware $midleware;
    private Controller $controller;

    public function __construct()
    {
        $this->auth       = new Authorization();
        $this->routes     = new Router();
        $this->midleware  = new Midleware();
        $this->controller = new Controller();
    }
    public function init()
    {
        if ($this->midleware->cors()) {
            return $this->routes->resolve();
        } else {
            return $this->controller->action('error');
        }
    }
    public function run()
    {
        if ($this->midleware->get('state') !== false) {
            if (!$this->auth->isSessionActive()) {
                $this->controller->run($this->routes);
            } else {
                $this->controller->action('login');
            }
        } else {
            $this->controller->action('login');
        }
        return $this->controller->response;
    }
    public function render($result)
    {
        return $this->controller->render($result);
    }
    
    public function end()
    {
        if (isset($this->controller)) {
            unset($this->controller);
        }
        if (isset($this->auth)) {
            unset($this->auth);
        }
    }
}
