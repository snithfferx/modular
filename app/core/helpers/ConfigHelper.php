<?php
    namespace app\core\helpers;
    class ConfigHelper {
        public function get (string $value) :array {
            return $this->getConfigVars($value);
        }
        public function set () {
            return true;
        }
        private function getConfigVars (string $file) :array {
            try {
                $path = _CONF_ . $file . ".json";
                $conf = json_decode(file_get_contents($path), true);
            } catch (\Exception $ex) {
                return ['type'=>"error",'data'=> $ex];
            }
            return $conf;
        }
    }
