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
    echo $app->display($response);
    /* echo "<pre>";
    var_dump($response);
    echo "</pre>";
    exit; */
    $app->terminate();
?>