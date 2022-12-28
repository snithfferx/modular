<?php
    /**
     * CIRACO
     * @author Jorge Echeverria <snithfferx@outlook.com>
     * @version 1.0.0
     */
    require __DIR__ . '/../../vendor/autoload.php';
    use app\core\LoaderClass;
    $app = new LoaderClass;
    $app->init();
    $response = $app->verifyRequest();
    $app->display($response);
    $app->terminate();
?>