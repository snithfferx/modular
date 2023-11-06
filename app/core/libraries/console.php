<?php
    use Doctrine\ORM\Tools\Console\ConsoleRunner;
    use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
    require dirname(__DIR__,3) . "/vendor/autoload.php";
    require dirname(__DIR__) . "/helpers/Definer.php";
    use app\core\handlers\Ormconnection;
    use app\core\helpers\Config;
    $config = new Config();
    $config->get("default");
    $connection = new Ormconnection();
    $entityManager = $connection->getEntityManager();
    $commands = [];
    ConsoleRunner::run(
        new SingleManagerProvider($entityManager),
        $commands
    );