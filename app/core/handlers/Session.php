<?php
/**
 * @category   Application
 * @package   app\core\handlers\AuthorizationHandler.php
 * @author snirthfferx <jecheverria@bytes4run.com>
 * @version 1.0.0
 * Date: 15/4/2023
 * Time: 12:58
 */
declare(strict_types=1);
namespace app\core\handlers;
use app\core\handlers\Token;
use app\core\handlers\Cookie;
class Session {
    private Token $token;
    private Cookie $coockie;
    protected int $sessionid;
    protected int $sessionTime;
    protected int $sessionTimeout;
    protected string $sessionName;
    private string $sessionalgo;
    private array $session;
    public Authorization $authorization;
    public function __construct(Authorization $session) {
        $this->authorization = $session;
        /* $this->token = $session->token;
        $this->coockie = $session->coockie;
        $this->sessionid = $session->sessionid;
        $this->sessionTime = $session->sessionTime;
        $this->sessionTimeout = $session->sessionTimeout;
        $this->sessionName = $session->sessionName;
        $this->sessionalgo = $session->sessionalgo; */
    }
    public function session() {
        /* if ($this->token->session()) {
            return $this->token->session();
        } else if ($this->coockie->session()) {
            return $this->coockie->session();
        } else {
            return null;
        } */
        return $this->authorization->getSession();
    }
}