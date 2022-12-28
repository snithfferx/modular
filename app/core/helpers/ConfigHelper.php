<?php
    namespace app\core\helpers;
    class ConfigHelper {
        public function get () {
            return $this->getConfigVars();
        }
        public function set () {
            return true;
        }
        private function getConfigVars () {
            try {
                $path = _CONF_ . "config.json";
                $conf = json_decode(file_get_contents($path), true);
            } catch (\Exception $ex) {
                return $ex;
            }
            return $conf;
        }
    }