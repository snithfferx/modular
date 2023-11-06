<?php
declare(strict_types=1);
/**
 * Conexión a servidor local para consumir recursos de la base de datos
 * @description This file is used to connect to the local server to consume resources from the database
 * @category Class
 * @author JEcheverria <jecheverria@bytes4run.com>
 * @package app\core\classes\Connection
 * @version 1.6.3 rev. 1
 * @date 2023-01-10 / 2023-05-03
 * @time 12:58:00 / 16:20:00
 */
namespace app\core\classes;
use app\core\helpers\Config;
use PDO;
use PDOException;
use Exception;

class Connection
{
    private $globalConf;
    private PDO|array $conexion;
    /**
     * Host de la base de datos
     * @var string
     */
    private string $host;
    /**
     * Puerto del host de la base de datos
     * @var string
     */
    private string $port;
    /**
     * Nombre de la base de datos
     * @var string
     */
    private string $dbName;
    /**
     * Nombre del usuario de la base de datos
     * @var string
     */
    private string $user;
    /**
     * Contraseña del usuario de la base de datos
     * @var string
     */
    private string $password;
    /**
     * Función para establecer la conexión a la base de datos
     * @return PDO|array
     */
    private function stablish_connection()
    {
        $db_DNS = "mysql:host=$this->host;port=$this->port;dbname=$this->dbName;charset=utf8mb4";
        try {
            $this->conexion = new PDO($db_DNS, $this->user, $this->password);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $excepcion) {
            $this->conexion = [
                'code' => "00500",
                'message' => "Error: " . $excepcion->getMessage() . "\nCode: " . $excepcion->getCode()
            ];
        }
        return $this->conexion;
    }

    /**
     * Función para ejecutar la consulta en la base de datos
     * @param array $query ['prepare_string' => 'string', 'params' => []]
     * @param string $type insert|update|delete|select
     * @param string|null $base default|null|nombre de la base de datos
     * @return array
     */
    private function getDBResponse(array $query, string $type, string|null $base = null) :array
    {
        $retorna = [];
        $errors = null;
        $affected = null;
        $id = null;
        $rows = null;
        if ($this->getConfig($base)) {
            $this->stablish_connection(); // Establecer conexión
            if (is_array($this->conexion)) {
                $errors = $this->conexion; // Si la conexión no se establece, retorna el error
            } else {
                if (!empty($type)) {
                    $pdo_Statement = $this->conexion->prepare($query['prepare_string']); // Preparar consulta
                    if ($type == 'insert' || $type == 'update' || $type == 'delete') {
                        try {
                            if ($pdo_Statement->execute($query['params'])) {
                                if ($type == 'insert') $id = $this->conexion->lastInsertId(); // Obtener el último id insertado
                                if ($type == 'update') $affected = $pdo_Statement->rowCount(); // Obtener el número de filas afectadas
                            } else {
                                throw new Exception("Query not executed correctly, verify statement.", 1);
                            }
                        } catch (PDOException $th) {
                            $errors = [
                                'code' => "00500",
                                'message' => "Error:&nbsp;&nbsp;&nbsp;" . $th->getMessage()
                                    . "\nCode: " . $th->getCode()
                            ];
                        }
                    } elseif ($type == 'select') {
                        try {
                            $pdo_Statement->execute($query['params']); // Ejecutar consulta
                            $pdo_Statement->setFetchMode(PDO::FETCH_ASSOC); // Establecer modo de obtención de datos
                            $rows = $pdo_Statement->fetchAll(); // Obtener datos
                        } catch (PDOException $th) {
                            $errors = [
                                'code' => "00500",
                                'message' => "Error:&nbsp;&nbsp;&nbsp;" . $th->getMessage()
                                    . "\nCode: " . $th->getCode()
                            ];
                        }
                    } else {
                        $errors = [
                            'code' => "00500",
                            'message' => "Falta un tipo de consulta.",
                        ];
                    }
                    $errors['extra'] = $pdo_Statement->errorInfo(); // Obtener información del error
                    $pdo_Statement->closeCursor(); // Cerrar cursor
                } else {
                    $errors = [
                        'code' => "00500",
                        'message' => "Falta un tipo de consulta.",
                    ];
                }
            }
        } else {
            $errors = [
                'code' => "00500",
                'message' => "La configuración de conexión a la base de datos, es erronea o tiene inconsistencias.<br>Favor revisar y reintentar."
            ];
        }
        $retorna = [
            'error'  => $errors,
            'affected' => $affected,
            'lastid'  => $id,
            'rows'    => $rows
        ];
        return $retorna;
    }

    /**
     * Función para obtener la configuración de conexión a la base de datos
     * @param string|null $database default|null|nombre de la base de datos
     * @return void
     */
    private function getConfig(string|null $database) :void
    {
        $this->globalConf = new Config;
        if ($database == null || $database == "default") {
            //$__CONF = $this->globalConf->get('config');
            $this->host     = $_ENV['APP_DB_HOST'];
            $this->port     = $_ENV['APP_DB_PORT'];
            $this->user     = $_ENV['APP_DB_USER'];
            $this->password = $_ENV['APP_DB_PASS'];
            $this->dbName   = $_ENV['APP_DB_NAME'];
        } else {
            $__CONF = $this->globalConf->get($database);
            $this->host     = $__CONF['dbhost'];
            $this->port     = $__CONF['dbport'];
            $this->user     = $__CONF['dbuser'];
            $this->password = $__CONF['dbpass'];
            $this->dbName   = $__CONF['dbname'];
        }
    }

    /**
     * Función para obtener la respuesta de la base de datos
     * @param string $type insert|update|delete|select
     * @param array $request ['prepare_string' => 'string', 'params' => []]
     * @param string $base default|null|nombre de la base de datos
     * @return array
     */
    public function getResponse(string $type, array $request, string $base): array
    {
        return $this->getDBResponse($request, $type, $base);
    }
}
