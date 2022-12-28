<?php
namespace app\modules\users\models;
use app\core\classes\context\ContextClass;
class UserModel extends ContextClass{
    public function get() {
        return true;
    }
    public function findToken(string $token) :array
    {
        $result = $this->findSessionToken($token);
    }
    private function findSessionToken(string $values)
    {
        $this->select(
            'session_token',
            [
                'session_token' => ['id', 'user', 'time'],
                'users' => ['name', 'level']
            ],
            [
                [
                    'type' => "INNER",
                    'table' => "session_token",
                    'main_table' => "sesion_token",
                    'main_filter' => "user_id",
                    'compare_table' => "users",
                    'compara_filter' => "id"
                ]
                ],
            "token_string=:$values"
        );
    }
}
?>