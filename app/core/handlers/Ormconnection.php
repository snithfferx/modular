<?php

/**
 * Clase que maneja la conexión a la base de datos
 * @description This file is used to manage the database connection
 * @category handler
 * @author JEcheverria <jecheverria@bytes4run>
 * @package app\core\handlers\Connection
 * @version 1.0.0 rev. 1
 * Time: 2023-05-03 12:20:00
 */

declare(strict_types=1);

namespace app\core\handlers;

use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Exception\ORMException;

class Ormconnection
{
    private $entityManager;
    public function __construct()
    {
        $dbParams = [
            'host'     => $_ENV['APP_DB_HOST'],
            'port'     => $_ENV['APP_DB_PORT'],
            'user'     => $_ENV['APP_DB_USER'],
            'driver'   => $_ENV['APP_DB_DRIVER'],
            'dbname'   => $_ENV['APP_DB_NAME'],
            'charset'  => $_ENV['APP_DB_CHARSET'],
            'password' => $_ENV['APP_DB_PASS'],
        ];
        $mode = $_ENV['APP_DEBUG'] == 'true' ? true : false;
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: array(_ENT_), // Constante definida en el archivo app\core\helpers\Definer.php
            isDevMode: $mode,
        );
        $connection = DriverManager::GetConnection($dbParams, $config);
        $this->entityManager = new EntityManager($connection, $config);
    }
    public function getEntityManager()
    {
        return $this->entityManager;
    }
    public function save($entity)
    {
        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        } catch (ORMException $orme) {
            return [
                'message'=>$orme->getMessage(),
                'code'=>$orme->getCode()
            ];
        }
        return [
            'message'=>'Entity saved successfully',
            'code'=>"ok"
        ]; 
    }
    /**
     * Función que hace un select a la base de datos
     * 
     * @param string $entity Nombre de la entidad a la que se le hará la consulta
     * @param array $args Argumentos para la consulta
     * 
     * type: all, find, findBy
     * id: int
     * criteria: array de criterios para la consulta
     * orderBy: array de ordenamiento
     * limit: int
     * offset: int
     * 
     * @return array
     */
    public function _get (string $entity, array $args) {
        if ($args['type'] == 'all') {
            try {
                $result = $this->entityManager->getRepository($entity)->findAll();
            } catch (ORMException $orme) {
                return [
                    'message'=>$orme->getMessage(),
                    'code'=>$orme->getCode()
                ];
            }
        } elseif ($args['type'] == 'find') {
            try {
                $result = $this->entityManager->getRepository($entity)->find($args['id']);
            } catch (ORMException $orme) {
                return [
                    'message'=>$orme->getMessage(),
                    'code'=>$orme->getCode()
                ];
            }
        } else {
            try {
                $result = $this->entityManager->getRepository($entity)->findBy($args['criteria'],$args['orderBy'],$args['limit'],$args['offset']);
            } catch (ORMException $orme) {
                return [
                    'message'=>$orme->getMessage(),
                    'code'=>$orme->getCode()
                ];
            }
        }
        return $result;
    }
    /**
     * Función que hace un insert a la base de datos
     *
     * @param string $entity Nombre de la entidad a la que se le hará la consulta
     * @param array $values Propiedad de la entidad a la que se le hará la consulta y el valor de la propiedad
     * @return void
     */
    public function _set(string $entity, array $values):void {
        $entity = 'app\\core\\entites\\'.$entity;
        $entityInstance = new $entity;
        foreach ($values as $ind => $val) {
        if (property_exists($entityInstance, $ind)) {
            $entityInstance->$ind = $val;
        }}
    }
}
