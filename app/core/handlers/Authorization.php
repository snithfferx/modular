<?php
/**
 * Clase que maneja la autorización del usuario
 * @description This file is used to manage the authorization of the user
 * @category handler
 * @author snirthfferx <jecheverria@bytes4run.com>
 * @package app\core\handlers\AuthorizationHandler.php
 * @version 1.0.0 rev. 1
 * Time: 2021-04-27 19:00:00
 */
namespace app\core\handlers;

use app\core\helpers\Session;
use app\core\handlers\Midleware;

class Authorization
{
    private Session $sessionUser;
    private Midleware $midleware;
    public function __Construct()
    {
        $this->sessionUser = new Session;
        //$this->midleware = new Midleware;
    }
    public function isSessionActive()
    {
        //return $this->sessionUser->isSessionStarted();
        return true;
    }
    public function getSession()
    {
        return $this->sessionUser->getSessionData();
    }
    public function tokenValidator(bool $httpState, string $httpToken = null, Session $session = null): array
    {
        if ($httpState == false) {
            return ['state' => 'no token', 'token' => null];
        } else {
            if ($session === null) {
                $sessionToken = $this->sessionUser->getSessionData('token');
                if ($sessionToken === null) {
                    return ['state' => 'no token', 'token' => null];
                } else {
                    if (strcmp($httpToken, $sessionToken) === 0) {
                        return ['state' => 'valid', 'token' => $httpToken];
                    } else {
                        return ['state' => 'invalid', 'token' => null];
                    }
                }
            } else {
                $sessionValid = $this->sessionUser->validSession($session);
                if ($sessionValid === false) {
                    return ['state' => 'no token', 'token' => null];
                } else {
                    $sessionToken = $this->sessionUser->getSessionData('token');
                    if ($sessionToken === null) {
                        return ['state' => 'no token', 'token' => null];
                    } else {
                        if (strcmp($httpToken, $sessionToken) === 0) {
                            return ['state' => 'valid', 'token' => $httpToken];
                        } else {
                            return ['state' => 'invalid', 'token' => null];
                        }
                    }
                }
            }
        }
    }
    
    public function getMidleware()
    {
        $midleware = $this->midleware;
        $midleware->cors();
        $authorizacion = ($midleware->headers['Authorization']) ?? null;
        $token = $this->tokenValidator($midleware->state, $authorizacion);
        //$midleware->set('token', $token);
        return ($token['state'] === 'valid') ? true : false;
    }
}
