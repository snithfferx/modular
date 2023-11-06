<?php
/**
 * Clase para el manejo de tokens
 * @description This file is used to manage the tokens
 * @category Handler
 * @author snithfferx <snithfferx@outlook.com>
 * @package app\core\handlers\Token
 * @version 1.0.0 rev.1
 * Time: 2023-16-04 15:38:00
 */
declare(strict_types=1);
namespace app\core\handlers;

use DateTime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\JWK;
use Firebase\JWT\SignatureInvalidException;
use app\core\entities\Token as TokenEntity;

class Token
{
    private $key;
    private $token;
    private TokenEntity $tokenEntity;
    private Ormconnection $entityManager;
    public string $user;
    public float $time;
    public DateTime $date;
    public string $url;
    public function make(array $values) :array
    {
        return $this->tokenMaker($values);
    }
    public function get($val, $result)
    {
        /* 'time'=>substr($subTime,0,2) . ":" . substr($subTime,2,2) . ":" . substr($subTime,4,2),
            'date'=>substr($subDate,0,2) . "/" . substr($subDate,2,2) . "/" . substr($subDate,4),
            'user'=>substr($subUser,0,-2),$subUser,'sublevel'=>substr($subUser,-1),'level'=>substr($subUser,-2,1),
            'hashid'=>$arrayHash[0],'hashsession'=>$arrayHash[1],'hashtoken'=>$arrayHash[2]] */
        switch ($result) {
            case "exist":
                $response = $this->tokenFinder($val);
                break;
            case "user":
                $tokenData = $this->tokenDecode($val);
                $response = $tokenData['user'];
                break;
            case "id":
                $tokenData = $this->tokenDecode($val);
                $response = $tokenData['hashid'];
                break;
            case "token":
                $tokenData = $this->tokenDecode($val);
                $response = $tokenData['hashtoken'];
                break;
            case "time":
                $tokenData = $this->tokenDecode($val);
                $response = $tokenData['time'];
                break;
            case "date":
                $tokenData = $this->tokenDecode($val);
                $response = $tokenData['date'];
                break;
            case "level":
                $tokenData = $this->tokenDecode($val);
                $response = $tokenData['level'];
                break;
            case "sublv":
                $tokenData = $this->tokenDecode($val);
                $response = $tokenData['sublevel'];
                break;
            default:
                $response = $this->tokenDecode($val);
                break;
        }
        return $response;
    }
    public function tokenRenew(string $old) {
        $tokenData = $this->tokenDecode($old);
        $tokenData['creation'] = time();
        $tokenData['expiration'] = time() + 60 * 60 * 24 * 7;
        $token = $this->tokenMaker($tokenData);
        return $token;
    }
    /**
     * Genera un token usando la información dada
     * @param string $user
     * @return array
     */
    private function tokenMaker(array $tokenInformation) :string
    {
        date_default_timezone_set("America/El_Salvador");
        $fecha = date("d/m/Y H:i:s");
        $user  = $tokenInformation['user'];
        $log   = $fecha . "_$" . $user;
        $logEnc= base64_encode($log);
        $hashu = hash("sha256", $logEnc);
        $session = "@" . $fecha . "#" . $tokenInformation['session'];
        $key = $tokenInformation['key'];
        $payload = [
            'iat' => ($tokenInformation['creation']) ?? time(),
            'exp' => ($tokenInformation['expiration']) ?? time() + 60 * 60,
            'data' => [
                'session' => $session,
                'log' => $hashu
            ]
        ];
        $jwt  = JWT::encode($payload, $key,'HS256');
        $jwtd = JWT::decode($jwt, new key($key,'HS256'));
        $this->saveToken([
            'token' => $jwt,
            'session' => $session,
            'data' => $jwtd,
            'log' => $hashu
        ]);
        return $jwt;
    }
    private function tokenFinder($value)
    {
        if ($_SESSION['token'] = $value) {
            return $this->tokenDecode($value);
        }
        return false;
    }
    private function tokenDecode($token)
    {
        $arrayTime = explode("#", $token);
        $arrayDate = explode("$", $token);
        $arrayUser = explode("-", $token);
        $arrayHash = explode(".", $arrayUser[1]);
        $subTime   = substr($arrayTime[0], 1);
        $x         = strlen($arrayTime[0]);
        $x        += 1;
        $subDate   = substr($arrayDate[0], $x);
        $x         = (strlen($arrayDate[0]));
        $x        += 1;
        $subUser   = substr($arrayUser[0], $x);
        $x         = (strlen($arrayUser[0]));
        $response  = [
            'time' => substr($subTime, 0, 2) . ":" . substr($subTime, 2, 2) . ":" . substr($subTime, 4, 2),
            'date' => substr($subDate, 0, 2) . "/" . substr($subDate, 2, 2) . "/" . substr($subDate, 4),
            'user' => substr($subUser, 0, -2), $subUser, 'sublevel' => substr($subUser, -1), 'level' => substr($subUser, -2, 1),
            'hashid' => $arrayHash[0], 'hashsession' => $arrayHash[1], 'hashtoken' => $arrayHash[2]
        ];
        return $response;
    }
    private function saveToken(array $tokenData)
    {
        $this->tokenEntity->set($tokenData);
        return $this->entityManager->save($this->tokenEntity);
    }
}