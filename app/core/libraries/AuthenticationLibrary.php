<?php
    namespace app\core\libraries;
    class AuthenticationLibrary {
        private $sessionUser;
        private $tokenator;
        private $session;
        function __Construct () {
            if ( php_sapi_name() !== 'cli' ) {
                if ( version_compare(phpversion(), '5.4.0', '>=') ) {
                    $this->sessionUser = session_status() === PHP_SESSION_ACTIVE ? true : false;
                } else {
                    $this->sessionUser = session_id() === '' ? false : true;
                }
            }
            $this->tokenator = new Tokenator;
            $this->session = new AppSession;
        }
        /**
         * Revisa sí la sesión está inicializada
         * Devuelve true sí la sesión existe y el tiempo de vida aún es mayor a cero.
         * @return bool
         */
        public function isSessionStarted() :bool {
            $response = false;
            if ($this->sessionUser === false) @session_start();
            if (!empty($_SESSION)) {
                if (isset($_SESSION['time']) && !empty($_SESSION['time'])) {
                    if (!$this->isTimeOut($_SESSION['time'])) {
                        $response = true;
                    } else {
                        if (!$_SESSION['keepalive']) {
                            $this->sessionKiller();
                        } else {
                            $_SESSION['time'] = $this->keepAlive($_SESSION['time']);
                            $response = true;
                        }
                    }
                } else {
                    $this->sessionKiller();
                }
            }
            return $response;
        }
        /**
         * Mata una sesion de usuario
         * @return bool
         */
        public function userSessionKiller () :bool {
            if ($this->sessionUser === false) @session_start();
            return $this->sessionKiller();
        }
        /**
         * Extrae la información de una sesión
         * @param string $value Contiene la petición del controller o usuario.
         * @return array
         */
        public function getSessionData (string $value) :array {
            return $this->getUserSessionData($value);
        }
        /**
         * Crea un token usando la información proporcionada por la petición
         * @param array $values
         * @return array
         */
        public function createToken (array $values) :array {
            return $this->getToken($this->tokenator->make($values['id']));
        }
        public function startSession (array $values)
        {
            return $this->setSession($values);
        }
        /**
         * Destructor de la clase
         */
        public function __destruct()
        {
            $this->sessionUser = null;
            $this->tokenator = null;
        }
        /**
         * Revisa sí el tiempo de la sesion se ha agotado
         * Devuelve false sí el tiempo actual es mayor al desifnado.
         * @param int $time Contiene el tiempo designado
         * @return bool
         */
        protected function isTimeOut (int $time) :bool {
            date_default_timezone_set("America/El_Salvador");
            $serverTime = time();
            $leftTime = $time - $serverTime;
            return ($leftTime <= 0) ? true : false;
        }
        /**
         * Verifica la existencia de una sesión, sí existe elimina la sesión y cambia el estado del token
         * @return bool
         */
        protected function sessionKiller() :bool {
            //if (isset($_SESSION['token'])) $this->tokenator->kill($_SESSION['token']);
            session_unset();
            session_destroy();
            return true;
        }
        protected function setSession(array $values) :array
        {
            return $this->session->startSession($values);
        }

        private function getUserSessionData ($value) {
            if ($this->sessionUser === false) @session_start();
            //if (isset($_SESSION['token']) && !empty($_SESSION['token'])) {
            if (!empty($_SESSION)) {
                switch ($value) {
                    /* case 'level':
                        $response = $this->decodeSessionLevel($_SESSION['token']);
                        break;
                    case 'sublevel':
                        $response = $this->decodeSessionSubLevel($_SESSION['token']);
                        break; */
                    case 'user':
                        $response = $_SESSION['user']; //$this->decodeSessionUser($_SESSION['token']);
                        break;
                    case 'time':
                        $response = $_SESSION['time']; //$this->decodeSessionTimeOut($_SESSION['time']);
                        break;
                    /* case 'id':
                        $response = $this->decodeSessionUserId($_SESSION['token']);
                        break; */
                    default:
                        //$token    = $this->sessionDecode($_SESSION['token']);
                        $response = $_SESSION;
                        //$response['tokendata'] = $token;
                        break;
                }
            } else {
                $response = false;
            }
            return $response;
        }
        /**
         * Calcula tiempo para el indice time de la sessión
         *
         * @param int $time
         * @return int
         */
        private function keepAlive (int $time = 0) :int {
            date_default_timezone_set("America/El_Salvador");
            $serverTime = time();
            $timeLimit  = (60 ^ 3) * 24;
            if ($time == 0) {
                $response = $serverTime + $timeLimit;
            } else {
                $leftTime = abs($time - $serverTime);
                $response = $serverTime + ($leftTime - $timeLimit);
                if ($response < $serverTime) $response = $timeLimit;
            }
            return $response;
        }
        /**
         * Compara que el token ya exista en la sesión.
         * @param array $vars Contiene el token a ser verificado
         * @return array
         */
        private function getToken(array $vars) : array {
            if (!empty($vars)) {
                $_SESSION['token'] = $vars['token'];
                return $vars;
            }
            return ['error'=>[500,"Token Not created"]];
        }

    }
    class AppSession {
        private $sessionUser;
        function __Construct () {
            if ( php_sapi_name() !== 'cli' ) {
                if ( version_compare(phpversion(), '5.4.0', '>=') ) {
                    $this->sessionUser = session_status() === PHP_SESSION_ACTIVE ? true : false;
                } else {
                    $this->sessionUser = session_id() === '' ? false : true;
                }
            }
        }
        public function isSessionStarted() {
            $response = false;
            if ($this->sessionUser === false) @session_start();
            if (!empty($_SESSION)) {
                if (isset($_SESSION['time']) && !empty($_SESSION['time'])) {
                    if (!$this->isTimeOut($_SESSION['time'])) {
                        $response = true;
                    } else {
                        $this->sessionKiller();
                    }
                } else {
                    $this->sessionKiller();
                }
            }
            return $response;
        }
        public function getSessionData ($value = null) {
            $response = false;
            if ($this->sessionUser === false) @session_start();
            if (isset($_SESSION['token']) && !empty($_SESSION['token'])) {
                switch ($value) {
                    case 'level':
                        $response = $this->decodeSessionLevel($_SESSION['token']);
                        break;
                    case 'sublevel':
                        $response = $this->decodeSessionSubLevel($_SESSION['token']);
                        break;
                    case 'user':
                        $response = $this->decodeSessionUser($_SESSION['token']);
                        break;
                    case 'time':
                        $response = $this->decodeSessionTimeOut($_SESSION['time']);
                        break;
                    case 'id':
                        $response = $this->decodeSessionUserId($_SESSION['token']);
                        break;
                    default:
                        $token    = $this->sessionDecode($_SESSION['token']);
                        $response = $_SESSION;
                        $response['tokendata'] = $token;
                        break;
                }
            }
            return $response;
        }
        public function startSession($values) {
            if ($this->sessionUser === false) @session_start();
            $user         = $values['user_id'] . $values['user_level'] . $values['user_sublevel'];
            if (!isset($values['user_token'])) {
                $tokenator = new Tokenator;
                $tokenSESSION = $tokenator->make($user);
            } else {
                $tokenSESSION = $values['user_token'];
            }
            if (!is_string($tokenSESSION)) {
                return false;
            } else {
                $values['session'] = $tokenSESSION;
                $time              = ($values['user_options']['keepalive'] == true) ? $this->setTimeOut(0,false) : $this->setTimeOut();
                $values['timeout'] = $time;
                $session['options']= $values['user_options'];
                $session['time']   = $time;
                $session['token']  = $tokenSESSION;
                $baker             = new CookieMonster;
                $result            = $baker->makeACookie($values);
                $session['cookie'] = $result;
                /* echo "<pre>";
                var_dump($session);
                echo "</pre>";
                exit; */
                $_SESSION          = $session;
                $session['id'] = session_id();
                session_write_close();
                return $session;
            }
        }
        public function userSessionKiller () {
            if ($this->sessionUser === false) @session_start();
            return $this->sessionKiller();
        }
        public function __destruct() {
            $this->sessionUser = null;
        }

        protected function setTimeOut($time = 0, $limit = true) {
            date_default_timezone_set("America/El_Salvador");
            $serverTime = time();
            $timeLimit = 18000;
            if ($limit) {
                if ($time == 0) {
                    $response = $serverTime + $timeLimit;
                } else {
                    $leftTime = abs($time - $serverTime);
                    $response = $serverTime + ($leftTime - $timeLimit);
                }
            } else {
                $response = $this->keepAlive($time);
            }
            return $response;
        }
        protected function isTimeOut ($time) {
            date_default_timezone_set("America/El_Salvador");
            $serverTime = time();
            $leftTime = $time - $serverTime;
            return ($leftTime <= 0) ? true : false;
        }
        protected function sessionKiller() {
            //if (isset($_SESSION['token'])) $this->killToken($_SESSION['token']);
            session_unset();
            session_destroy();
            return true;
        }

        private function keepAlive ($time = 0) {
            date_default_timezone_set("America/El_Salvador");
            $serverTime = time();
            $timeLimit  = (60 ^ 3) * 24;
            if ($time == 0) {
                $response = $serverTime + $timeLimit;
            } else {
                $leftTime = abs($time - $serverTime);
                $response = $serverTime + ($leftTime - $timeLimit);
            }
            return $response;
        }
        /* private function killToken ($token) {
            $tokenator = new Tokenator;
            return $tokenator->kill($token);
        } */
        private function decodeSessionLevel ($token) {
            $tokenator = new Tokenator;
            return $tokenator->get($token,'level');
        }
        private function decodeSessionSubLevel ($token) {
            $tokenator = new Tokenator;
            return $tokenator->get($token,'sublv');
        }
        private function decodeSessionUser ($token) {
            $tokenator= new Tokenator;
            $result   = $tokenator->get($token,'user');
            $x        = strlen($result);
            $user     = substr($result,0,($x - 2));
            //require_once _MODELO_ . "User.php";
            //$model    = new User;
            //$response = $model->_get_("name",$user);
            //return $response['nombre'];
            return $user;
        }
        private function decodeSessionTimeOut ($time) {
            date_default_timezone_set("America/El_Salvador");
            $serverTime= time();
            $leftTime  = abs($time - $serverTime);
            $response  = ['horas'=>($leftTime /3600), 'mins'=>($leftTime / 60)];
            return $response;
        }
        private function decodeSessionUserId($token) {
            $tokenator= new Tokenator;
            $result   = $tokenator->get($token,'user');
            return $result;
        }
        private function sessionDecode ($val) {
            $tokenator = new Tokenator;
            return $tokenator->get($val,'data');
        }
    }
    class Tokenator {
        public function make($user) {
            return $this->tokenMaker($user);
        }
        public function get($val,$result) {
            /* 'time'=>substr($subTime,0,2) . ":" . substr($subTime,2,2) . ":" . substr($subTime,4,2),
            'date'=>substr($subDate,0,2) . "/" . substr($subDate,2,2) . "/" . substr($subDate,4),
            'user'=>substr($subUser,0,-2),$subUser,'sublevel'=>substr($subUser,-1),'level'=>substr($subUser,-2,1),
            'hashid'=>$arrayHash[0],'hashsession'=>$arrayHash[1],'hashtoken'=>$arrayHash[2]] */
            switch ($result) {
                case "exist":
                    $response = $this->tokenFinder($val);
                    break;
                case "user":
                    $tokenData= $this->tokenDecode($val);
                    $response = $tokenData['user'];
                    break;
                case "id":
                    $tokenData= $this->tokenDecode($val);
                    $response = $tokenData['hashid'];
                    break;
                case "token":
                    $tokenData= $this->tokenDecode($val);
                    $response = $tokenData['hashtoken'];
                    break;
                case "time":
                    $tokenData= $this->tokenDecode($val);
                    $response = $tokenData['time'];
                    break;
                case "date":
                    $tokenData= $this->tokenDecode($val);
                    $response = $tokenData['date'];
                    break;
                case "level":
                    $tokenData= $this->tokenDecode($val);
                    $response = $tokenData['level'];
                    break;
                case "sublv":
                    $tokenData= $this->tokenDecode($val);
                    $response = $tokenData['sublevel'];
                    break;
                default:
                    $response = $this->tokenDecode($val);
                    break;
            }
            return $response;
        }
        /* public function kill($val) {
            if (!empty($val)) {
                try {
                    $this->killToken($val);
                } catch (Exception $e) {
                    return $e;
                }
            } else {
                return false;
            }
        } */
        /**
         * Genera un token usando la información dada
         * @param string $user
         * @return array
         */
        private function tokenMaker(string $user) {
            date_default_timezone_set("America/El_Salvador");
            $fecha = date("dmY");
            $hora  = date("His");
            $text  = $fecha . "-" . $hora . "_$" . $user;
            $encripted = base64_encode($text);
            $hashu = hash("sha256",$encripted);
            $sid   = null;
            if (session_id() != '') $sid = session_id();
            $tokenSession = "@" . $hora . "#" . $fecha . "$" . $user . ".";
            if (!is_null($sid)) $tokenSession .= $sid . ".";
            $tokenSession .= $hashu;
            return [
                'session'=> $tokenSession,
                'token'=> $hashu
            ];
        }
        private function tokenFinder($value) {
            if ($_SESSION['token'] = $value) {
                return $this->tokenDecode($value);
            }
            //require_once _MODELO_ . "User.php";
            //$model = new User;
            //$response = $model->findToken($tokenData['date'],$tokenData['time'],$tokenData['user'],$tokenData['hashid'],$tokenData['hashtoken']);
            //return $response;
            return false;
        }
        private function tokenDecode ($token) {
            $arrayTime = explode("#",$token);
            $arrayDate = explode("$",$token);
            $arrayUser = explode("-",$token);
            $arrayHash = explode(".",$arrayUser[1]);
            $subTime   = substr($arrayTime[0],1);
            $x         = strlen($arrayTime[0]);
            $x        += 1;
            $subDate   = substr($arrayDate[0],$x);
            $x         = (strlen($arrayDate[0]));
            $x        += 1;
            $subUser   = substr($arrayUser[0],$x);
            $x         = (strlen($arrayUser[0]));
            $response  = [
                'time'=>substr($subTime,0,2) . ":" . substr($subTime,2,2) . ":" . substr($subTime,4,2),
                'date'=>substr($subDate,0,2) . "/" . substr($subDate,2,2) . "/" . substr($subDate,4),
                'user'=>substr($subUser,0,-2),$subUser,'sublevel'=>substr($subUser,-1),'level'=>substr($subUser,-2,1),
                'hashid'=>$arrayHash[0],'hashsession'=>$arrayHash[1],'hashtoken'=>$arrayHash[2]];
            return $response;
        }
        /* private function killToken ($token) {
            require_once _MODELO_ . "User.php";
            $model = new User;
            $tokenData = $this->tokenDecode($token);
            $response = $model->deleteToken(
                $tokenData['hashid'],
                $tokenData['hashtoken'],
                $tokenData['user'],
                $tokenData['date'],
                $tokenData['time']);
            return $response;
        } */
    }
    class CookieMonster {
        public function makeACookie ($values) {
            return $this->cookieOven($values);
        }
        public function getACookie ($val,$result) {
            switch ($val) {
                case 'value':
                    # code...
                    break;
                
                default:
                    # code...
                    break;
            }
        }
        /* public function killTheCookie ($val) {
            return CookieMonster::burnTheCookie($val);
        } */

        private function cookieOven ($sessionData) {
            date_default_timezone_set("America/El_Salvador");
            $fecha = date("mY_His");
            $host  = $_SERVER['HTTP_HOST'];
            $cookieName = $fecha . $sessionData['user_id'];
            $cookieStr  = "";
            setcookie($cookieName . "[session]",$cookieStr,$sessionData['timeout'],'/',$host,true,false);
            header("Set-Cookie: key=$cookieName; SameSite=Lax");
            //$response = CookieMonster::saveTheCookie($cookieName,null,$cookieStr);
            if (isset($sessionData['options'])) {
                $cookieOps = $this->cookieOptions($cookieName,$sessionData['options'],$sessionData['timeout'],$host);
                if (!$cookieOps) return $cookieOps;
            }
            return $cookieName;
        }
        private function cookieOptions ($cookieName,$data,$timeOut,$hostName) {
            try {
                foreach ($data as $index => $val) {
                    setcookie($cookieName . "[$index]",$val,$timeOut,'/',$hostName,true,false);
                    //CookieMonster::saveTheCookie($cookieName,$cookieId,$val,$timeOut);
                }
                return true;
            } catch (\Exception $th) {
                return $th;
            }
        }
        /* private function saveTheCookie ($name,$value,$time = 0) {
            require_once _MODELO_ . "User.php";
            $model = new User;
            $response = $model->saveThisCookie($name,$value,$time);
            return $response;
        } */
        /* private function burnTheCookie ($cookieId) {
            require_once _MODELO_ . "User.php";
            $model = new User;
            $response = $model->deleteTheCookie($cookieId);
            return $response;
        } */
    }
?>