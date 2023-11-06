<?php

/**
 * Clase que maneja las galletas de la sesion
 * @description This file is used to manage the cookie session
 * @category handler
 * @author JEcheverria <jecheverria@bytes4run>
 * @package app\core\handlers\Cookie
 * @version 1.0.0 rev. 1
 * Time: 2023-05-03 12:20:00
 */

namespace app\core\handlers;

use \Exception;

class Cookie
{
    /**
     * Crea una galleta de sesion
     * @param array $values
     * @return \Exception|bool|string
     */
    public function makeMeACookie(array $values) :string
    {
        return $this->cookieOven($values);
    }
    /**
     * Retorna una galleta de sesion
     * @param mixed $val
     * @return void
     */
    public function giveMeACookie($val)
    {
        switch ($val) {
            case 'value':
                # code...
                break;

            default:
                # code...
                break;
        }
    }
    /**
     * Elimina una galleta de sesion
     * @param mixed $val
     * @return void
     */
    public function ThrowThisCookie ($val) {
        $this->burnTheCookie($val);
    }

    /**
     * Cocina una galleta de sesion
     * @param mixed $sessionData
     * @return Exception|string
     */
    private function cookieOven($sessionData) :string
    {
        date_default_timezone_set("America/El_Salvador");
        $fecha = date("mY_His");
        $host  = $_SERVER['HTTP_HOST'];
        $cookieName = $fecha . $sessionData['user_id'];
        $cookieStr  = "";
        setcookie($cookieName . "[session]", $cookieStr, $sessionData['timeout'], '/', $host, true, false);
        header("Set-Cookie: key=$cookieName; SameSite=Lax");
        if (isset($sessionData['options'])) {
            $cookieOps = $this->cookieOptions($cookieName, $sessionData['options'], $sessionData['timeout'], $host);
            if ($cookieOps != true) return $cookieOps;
        }
        return $cookieName;
    }
    /**
     * Crea las opciones de la galleta de sesion
     * @param mixed $cookieName
     * @param mixed $data
     * @param mixed $timeOut
     * @param mixed $hostName
     * @return Exception|bool
     */
    private function cookieOptions($cookieName, $data, $timeOut, $hostName)
    {
        try {
            foreach ($data as $index => $val) {
                setcookie($cookieName . "[$index]", $val, $timeOut, '/', $hostName, true, false);
                //CookieMonster::saveTheCookie($cookieName,$cookieId,$val,$timeOut);
            }
            return true;
        } catch (Exception $th) {
            return $th;
        }
    }
    /**
     * Elimina una galleta de sesion
     * @param mixed $cookieName
     */
    private function burnTheCookie($cookieName) {
        $host  = $_SERVER['HTTP_HOST'];
        setcookie($cookieName . "[session]", "", time() - 3600, '/', $host, true, false);
        header("Set-Cookie: key=$cookieName; SameSite=Lax");
        return true;
    }
}