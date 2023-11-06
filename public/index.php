<?php
/**
 * @category Application
 * @package  Application
 * @author   snirthfferx <jecheverria@bytes4run.com>
 * @version  1.0.0
 */
require '../vendor/autoload.php';
use app\Loader;
$app = new Loader();
$app->init();
$response = $app->run();
$app->render($response);
$app->end();