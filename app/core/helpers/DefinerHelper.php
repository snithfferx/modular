<?php
    ##GLOBAL APP Core Variable
    if (!defined("_APP_")) define("_APP_", dirname(__FILE__,2));
    ##GLOBAL CLASS Core Variable
    if (!defined("_CLASS_")) define("_CLASS_",_APP_ . "/core/class/");
    ##GLOBAL HELPER Core Variable
    if (!defined("_HELPER_")) define("_HELPER_",_APP_ . "/core/helpers/");
    ##GLOBAL MODULE Core Variable
    if (!defined("_MODULE_")) define("_MODULE_",dirname(_APP_) . "/modules/");
    ##GLOBAL VIEW Variable
    if (!defined("_VIEW_")) define("_VIEW_",dirname(_APP_) . "/public/views/");
    ##GLOBAL CONFIGURATION Variable
    if (!defined("_CONF_")) define("_CONF_",dirname(_APP_,2) . "/configs/");
    if (!defined("_CACHE_")) define("_CACHE_", dirname(_APP_,2) . "/cache/");
?>