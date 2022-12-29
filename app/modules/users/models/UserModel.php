<?php
namespace app\modules\users\models;
use app\core\classes\context\ContextClass;
class UserModel extends ContextClass{
    public $user;
    public $logAction;
    public $logData;
    public $userToken;
    public $userSession;
    public function get() {
        return true;
    }
    public function set () {
        return true;
    }
    public function findToken(string $token) :array
    {
        return $this->findSessionToken($token);
    }
    public function saveLog (string $action,$data) {
        $this->logAction = $action;
        $this->logData = $data;
        return $this->saveUserLog();
    }
    public function getUser(string $userName) :array
    {
        if ($userName == "jorge") {
            return ['data' => ['id'=>65,'user'=>$userName,'type'=>"success"]];
        } else {
            return ['error' => "Not Found"];
        }
    }
    public function saveSession() :array
    {
        return $this->saveUserSession();
    }

    private function findSessionToken(string $values)
    {
        $result = $this->select(
            'session_token',
            [
                'fields'=>[
                    'session_token' => ['id', 'user', 'time'],
                    'users' => ['name', 'level']
                ],
                'joins'=>[
                    [
                        'type' => "INNER",
                        'table' => "session_token",
                        'main_table' => "sesion_token",
                        'main_filter' => "user_id",
                        'compare_table' => "users",
                        'compara_filter' => "id"
                    ]
                ],
                'params'=>"token_string=:$values"]
        );
        return $result;
    }
    private function saveUserLog () {
        $result = $this->insert("user_logs",[
            'fields'=>['time','user','action','extra'],
            'values'=>['NOW()',$this->user,$this->logAction,$this->logData]
        ]);
        return $result;
    }
    private function saveUserSession() {
        $result = $this->insert("user_session", [
            'fields' => ['time', 'user', 'action', 'extra'],
            'values' => ['NOW()', $this->user, $this->logAction, $this->logData]
        ]);
        return $result;
    }
}
