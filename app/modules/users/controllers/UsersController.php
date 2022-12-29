<?php
    namespace app\modules\users\controllers;
    /**
     * @author jecheverria@bytes4run.com
     * @version 1.0
     */
    use app\core\classes\ControllerClass;
    use app\core\libraries\AuthenticationLibrary;
    use app\modules\users\models\UserModel;
    /**
     * Clase Usuarios
     */
    class UsersController extends ControllerClass {
        /**
         * @var object $user_model Contiene el model de los usuarios
         */
        private $user_model;
        /**
         * @var object $auth Contiene el objeto de la libreria Autorizacion
         */
        private $auth;
        public function __construct() {
            $this->user_model = new UserModel;
            $this->auth = new AuthenticationLibrary;
        }
        /**
         * Crea un Token para el usuario el cual es guardado en la base de datos 
         * para llevar la bitacora del usuario.
         * @param string $values Contiene la informacón del usuario solicitante
         * @return array
         */
        public function create_token(array $values)
        {
            if (isset($values['token'])) {
                return $this->getToken($values);
            } else {
                return $this->verifyUser($values);
            }
        }

        protected function getToken($values) {
            $result = $this->auth->createToken($values);
            if (!isset($result['error'])) {
                if (!$this->findToken($result['token'])) {
                    $this->saveToken($result);
                }
                return [
                    'type'=>"success",
                    'string'=>$result['token'],
                ];
            }   
            return $result;
        }
        protected function verifyUser(array $values) :array
        {
            //$user = base64_decode($values['user']);
            $result = $this->user_model->getUser($values['user']);
            if (isset($result['error']) && !empty($result['error'])) {
                return $result;
            } else {
                $token = $this->auth->createToken($result['data']);
                $session = $this->auth->startSession([
                    'user_id'=>$result['data']['id'],
                    'user_level'=>1,
                    'user_sublevel'=>1,
                    'user_token'=>$token['session'],
                    'user_options' => [
                        'keepalive'=>$values['alive'],
                        'mode'=>$values['mode']
                    ]
                ]);
                //$this->user_model->saveLog('create a token',$values);
                $this->user_model->userSession = $session;
                //$this->user_model->saveSession();
                return (!isset($token['error'])) ? ['type' => "OK", 'string' => $token['token']] : $token;
            }
        }


        private function saveToken (array $values) :array {
            $this->user_model->userToken = $values['token'];
            $this->user_model->userSession = $values['id'];
            $this->user_model->saveLog('create token', $values['token']);
            $result = $this->user_model->set();
            if (!empty($result['error'])) {
                $this->user_model->saveLog("create token fail", $values['token']);
                return $result;
            }
            $this->user_model->saveLog($values['token'],"success");
            return ['type' => "OK", 'string' => $values['token']];
        }
        private function findToken (array $values) :array {
            $result = $this->user_model->findToken($values['token']);
            if (empty($result['error'])) {
                $this->user_model->saveLog('create token',$values['token']);
                return ['type'=>"OK",'string'=>$values['token']];
            }
            return $result;
        }
    }