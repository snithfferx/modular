<?php
    /**
     * @author jecheverria@
     * @version 1.0
     */
    use app\core\classes\ControllerClass;
    use app\core\libraries\AuthenticationLibrary;
    use app\modules\model\UserModel;
    /**
     * Clase Usuarios
     */
    class UserController extends ControllerClass {
        /**
         * @var user_model Contiene el model de los usuarios
         */
        private $user_model;
        /**
         * @var auth Contiene el objeto de la libreria Autorizacion
         */
        private $auth;
        /**
         * Construye la clase Usuario
         * @return Object User
         */
        public function __construct() {
            $this->user_model = new UserModel;
            $This->auth = new AuthenticationLibrary;
        }
        /**
         * Crea un Token para el usuario el cual es guardado en la base de datos 
         * para llevar la bitacora del usuario.
         * @param string $values Contiene la informacón del usuario solicitante
         * @return array
         */
        public function create_token ($values) {
            return $this->getToken($values);
            
        }

        protected function getToken($values) {
            $result = $this->auth->createToken($values);
            if (!isset($result['error'])) {
                if (!$this->findToken($result)) {
                    $this->saveToken($result);
                }
                return [
                    'type'=>"success",
                    'string'=>$result['token'],
                ];
            }   
            return $result;
        }
        private function saveToken (array $values) :array {
            $this->user_model->token = $values['token'];
            $this->user_model->session = $values['id'];
            $this->user_model->saveLog($values['token']);
            $result = $this->user_model->create();
            if (!empty($result['error'])) {
                $this->user_model->saveLog($values['token'],"faild");
                return $result;
            }
            $this->user_model->saveLog($values['token'],"success");
            return true;
        }
        private function findToken (array $values) :array {
            if ($this->user_model->findToken($values['token'])) {
                $this->user_model->saveLog($values['token']);
                return true;
            }
            return false;
        }
    }