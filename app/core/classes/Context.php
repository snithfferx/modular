<?php

/**
 * Clase para las transacciones entre los modelos y la base de datos.
 * @description This file is used for transactions between models and the database.
 * @category Class
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @package app\core\classes\Context
 * @version 1.2.8 rev. 1
 * @date 19/01/23 - 01/05/23
 * @time 12:58 - 16:20
 */

declare(strict_types=1);

namespace app\core\classes;

use app\core\classes\Connection;

class Context extends Connection
{
    protected $base;
    /**
     * Esta función se encarga de extraer todos los registros solicitados a una tabla, tomando en cuenta las condiciones proporcionadas.
     * Puede devolver un arreglo con los registros solicitados o un arreglo con un mensaje de error.
     * 
     * @param array $data Campos a ser extraidos de la tabla sugerida.
     * 
     * El arreglo deberá contener:
     * 
     * "fields" Agrupa los campos a extraerse de las tablas,
     * anexando el nombre de la tabla como indice y como valor del indice el arreglo de los campos a extraer. 
     * Ej. ['table1'=>['field1','field2',...],'table2'=>['field1','field2',...],...]
     * 
     * "joins" De haber más de una tabla, se declaran los tipos de uniones en un arreglo, colocando el tipo de unión como indice y como valor del indice 
     * el arreglo de los campos en relacion, declarando como indice valor, siendo valor el nombre de la tabla y el valor el campo a relacionar.
     * Ej. ['inner'=>['users'=>"id",'persons'=>"user_id"],...]
     * 
     * Tambien se puede declarar un arreglo de uniones, colocando el tipo de unión como indice y como valor del indice el arreglo o areglos de los campos en relacion. 
     * Tomar en cuenta que en este caso, el primer indice del arreglo sera la tabla y el segundo sera el campo.
     * Ej.: ['inner'=>['users'=>['id','name'],'persons'=>['user_id','name']],...]
     * 
     * "params" Condición a ser cumplida para ser devuelta la información.
     * El arreglo deberá contener:
     * "condition" Que debe contener el arreglo ['type'=>['table'=>['field','value']],...] o ['type'=>['table'=>['field'=>'value','field2'=>'value2']],...]
     * Donde: Table es la tabla a la que pertenece el campo, type es el tipo de comparación a realizar, field es el campo a comparar y value es el valor a comparar; 
     * el valor puede ser un arreglo si la condicion ha de repetirse.
     * Los valores de "type" pueden ser:
     * 
     * *'COMPARATIVE':* Para comparar valores. Representado en la query como: ' = ? '
     * 
     * *'SIMILAR':* Para comparar valores similares. Representado en la query como: ' LIKE CONCAT('%', ?, '%') '
     * 
     * *'START_WITH':* Para comparar valores que inicien con el valor. Representado en la query como: ' LIKE CONCAT(?, '%') '
     * 
     * *'END_WITH':* Para comparar valores que terminen con el valor. Representado en la query como: ' LIKE CONCAT('%', ?) '
     * 
     * *'RANGE':* Para comparar valores que esten entre dos valores. Representado en la query como: ' BETWEEN ? AND ? '
     * 
     * *'NEGATIVE':* Para comparar valores que no sean iguales. Representado en la query como: ' != ? '
     * 
     * *'LESS_THAN':* Para comparar valores que sean menores. Representado en la query como: ' < ? '
     * 
     * *'MORE_THAN':* Para comparar valores que sean mayores. Representado en la query como: ' > ? '
     * 
     * *'LESS_EQ_TO':* Para comparar valores que sean menores o iguales a. Representado en la query como: ' <= ? '
     * 
     * *'MORE_EQ_TO':* Para comparar valores que sean mayores o iguales a. Representado en la query como: ' >= ? '
     * 
     * *'NOT_IN':* Para valores no incluidos en un arreglo. 
     * Representado en la query como: ' NOT IN (?) ' o ' NOT IN (?,?,...) ' dependiendo de la cantidad de valores en el arreglo.
     * 
     * *'IS_IN':* Para valores incluidos en un arreglo.
     * Representado en la query como: ' IN (?) ' o ' IN (?,?,...) ' dependiendo de la cantidad de valores en el arreglo.
     * 
     * "separator" Será un arreglo de operadores de comparación, tales como: [Y,O] para AND y OR respectivamente.
     * @param int $limit Límite de registros a ser devueltos. Por defecto 1000. Puede usar 0 para no limitar.
     * @param string $sorting Orden o agrupacion de los registros a ser devueltos, ASC(ascendente), DESC(descendente), GROUP(agrupar).
     * @param string $sortBy Indice para realizar el orden o agrupación. Por defecto el id del registro.
     * 
     * @link /docs/sima/develop/querybuilder
     * @return array
     */
    protected function select(array $data, int $limit = 1000, string $sorting = '', string $sortBy = ''): array
    {
        if (empty($data)) {
            $response = ['error' => ['code' => 404, 'message' => "No hay tabla para consultar."], 'data' => array()];
        } else {
            $response = $this->getDBData($data, $limit, $sorting, $sortBy);
        }
        return $response;
    }
    /**
     * Función para la inserción de datos a la base de datos.
     * Devuelve un arreglo con el resultado de la transacción.
     *
     * @param string $tableName Nombre de la tabla a ser afectada
     * @param array $data Arreglo de datos a insertar, en los indices 'fields' y 'values'.
     * *'fields'*: Debe ser un arreglo de los campos a ser afectados. ['field1','field2',...]
     * 
     * *'values'*: Debe ser un arreglo de valores a ser introducidos, teniendo en cuenta la cantidad de campos. ['value1','value2',...]
     * 
     * @return array
     */
    protected function insert(string $tableName, array $data): array
    {
        if (is_array($data)) {
            return $this->setDBData('insert', $tableName, $data['fields'], $data['values']);
        } else {
            return ['error' => ['code' => 404, 'message' => "La información proporcionada tiene un formato no soportado."], 'data' => array()];
        }
    }
    /**
     * Función para realizar la eliminación de datos en la base de datos.
     * Devuelve un arreglo con el resultado de la transacción.
     *
     * @param string $tableName Nombre de la tabla a ser afacetada.
     * @param array $data Arrelo de valores a ser eliminados, ['fields','values','params'];
     * 
     * *'fields'*: Debe ser un arreglo de los campos a ser afectados. ['field1','field2',...]
     * 
     * *'values'*: Debe ser un arreglo de valores a ser introducidos, teniendo en cuenta la cantidad de campos. ['value1','value2',...]
     * 
     * *'params'*: Debe ser un arreglo de parametros a ser utilizados en la condición de eliminación. ['condition','separator']
     * 
     * "condition" Que debe contener el arreglo ['table','type','field','value']
     * Donde: Table es la tabla a la que pertenece el campo, type es el tipo de comparación a realizar, field es el campo a comparar y value es el valor a comparar.
     * Los valores de "type" pueden ser:
     * 
     * *'COMPARE':* Para comparar valores. Representado en la query como: ' = ? '
     * 
     * *'SIMILAR':* Para comparar valores similares. Representado en la query como: ' LIKE CONCAT('%', ?, '%') '
     * 
     * *'START':* Para comparar valores que inicien con el valor. Representado en la query como: ' LIKE CONCAT(?, '%') '
     * 
     * *'END':* Para comparar valores que terminen con el valor. Representado en la query como: ' LIKE CONCAT('%', ?) '
     * 
     * *'RANGO':* Para comparar valores que esten entre dos valores. Representado en la query como: ' BETWEEN ? AND ? '
     * 
     * *'NEGATIVA':* Para comparar valores que no sean iguales. Representado en la query como: ' != ? '
     * 
     * *'COMPARE_ME':* Para comparar valores que sean menores. Representado en la query como: ' < ? '
     * 
     * *'COMPARE_MA':* Para comparar valores que sean mayores. Representado en la query como: ' > ? '
     * 
     * *'COMPARE_ME_I':* Para comparar valores que sean menores o iguales a. Representado en la query como: ' <= ? '
     * 
     * *'COMPARE_MA_I':* Para comparar valores que sean mayores o iguales a. Representado en la query como: ' >= ? '
     * 
     * *'NOT_IN':* Para valores no incluidos en un arreglo. 
     * Representado en la query como: ' NOT IN (?) ' o ' NOT IN (?,?,...) ' dependiendo de la cantidad de valores en el arreglo.
     * 
     * *'IN':* Para valores incluidos en un arreglo.
     * Representado en la query como: ' IN (?) ' o ' IN (?,?,...) ' dependiendo de la cantidad de valores en el arreglo.
     * 
     * "separator" Será un arreglo de operadores de comparación, tales como: [Y,O] para AND y OR respectivamente.
     * 
     * @link /docs/develop/queryStringCondition
     * @return array
     */
    protected function delete(string $tableName, array $params): array
    {
        if (empty($table)) {
            return ['error' => ['code' => 400, 'message' => "A table name is needing."], 'data' => array()];
        } elseif (empty($table)) {
            return ['error' => ['code' => 400, 'message' => "Parameters are needing."], 'data' => array()];
        }
        return $this->setDBData('delete', $tableName, [], [], $params);
    }
    /**
     * Función para realizar edición en registros de la base de datos.
     * Devuelve un arreglo con el resultado de la transacción.
     *
     * @param string $tableName Nombre de la tabla a ser afacetada.
     * @param array $data Arreglo de valores a ser actualizados, valores y condiciones a ser cumplidas.
     * 
     * *'fields'*: Debe ser un arreglo de los campos a ser afectados. ['field1','field2',...]
     * 
     * *'values'*: Debe ser un arreglo de valores a ser introducidos, teniendo en cuenta la cantidad de campos. ['value1','value2',...]
     * 
     * "params" Condición a ser cumplida para ser devuelta la información.
     * El arreglo deberá contener:
     * "condition" Que debe contener el arreglo ['table','type','field','value']
     * Donde: Table es la tabla a la que pertenece el campo, type es el tipo de comparación a realizar, field es el campo a comparar y value es el valor a comparar.
     * Los valores de "type" pueden ser:
     * 
     * *'COMPARE':* Para comparar valores. Representado en la query como: ' = ? '
     * 
     * *'SIMILAR':* Para comparar valores similares. Representado en la query como: ' LIKE CONCAT('%', ?, '%') '
     * 
     * *'START':* Para comparar valores que inicien con el valor. Representado en la query como: ' LIKE CONCAT(?, '%') '
     * 
     * *'END':* Para comparar valores que terminen con el valor. Representado en la query como: ' LIKE CONCAT('%', ?) '
     * 
     * *'RANGO':* Para comparar valores que esten entre dos valores. Representado en la query como: ' BETWEEN ? AND ? '
     * 
     * *'NEGATIVA':* Para comparar valores que no sean iguales. Representado en la query como: ' != ? '
     * 
     * *'COMPARE_ME':* Para comparar valores que sean menores. Representado en la query como: ' < ? '
     * 
     * *'COMPARE_MA':* Para comparar valores que sean mayores. Representado en la query como: ' > ? '
     * 
     * *'COMPARE_ME_I':* Para comparar valores que sean menores o iguales a. Representado en la query como: ' <= ? '
     * 
     * *'COMPARE_MA_I':* Para comparar valores que sean mayores o iguales a. Representado en la query como: ' >= ? '
     * 
     * *'NOT_IN':* Para valores no incluidos en un arreglo. 
     * Representado en la query como: ' NOT IN (?) ' o ' NOT IN (?,?,...) ' dependiendo de la cantidad de valores en el arreglo.
     * 
     * *'IN':* Para valores incluidos en un arreglo.
     * Representado en la query como: ' IN (?) ' o ' IN (?,?,...) ' dependiendo de la cantidad de valores en el arreglo.
     * 
     * "separator" Será un arreglo de operadores de comparación, tales como: [Y,O] para AND y OR respectivamente.
     * 
     * @return array
     */
    protected function update(string $tableName, array $data): array
    {
        if (empty($tableName)) {
            return ['error' => ['code' => 400, 'message' => "A table name is needing."], 'data' => array()];
        } elseif (empty($data)) {
            return ['error' => ['code' => 400, 'message' => "Changes are needing."], 'data' => array()];
        }
        return $this->setDBData('update', $tableName, $data['fields'], $data['values'], $data['params']);
    }
    /**
     * Función que devuelve el cálculo de registros en la tabla sugerida, respetando la condición dada.
     *
     * @param string $table Nombre de la tabla a ser consultada
     * @param string $function Cálculo a ser realizado en la tabla (cuenta, suma, máximo, mínimo, promedio)
     * @param string $field Campo por el cual se desea contar los registros, generalmente se usa el id del registro.
     * @param array $cond Condición a ser cumplida para ser devuelta la información.
     * 
     * El arreglo deberá contener:
     * "condition" Que debe contener el arreglo ['table','type','field','value']
     * Donde: Table es la tabla a la que pertenece el campo, type es el tipo de comparación a realizar, field es el campo a comparar y value es el valor a comparar.
     * Los valores de "type" pueden ser:
     * 
     * *'COMPARE':* Para comparar valores. Representado en la query como: ' = ? '
     * 
     * *'SIMILAR':* Para comparar valores similares. Representado en la query como: ' LIKE CONCAT('%', ?, '%') '
     * 
     * *'START':* Para comparar valores que inicien con el valor. Representado en la query como: ' LIKE CONCAT(?, '%') '
     * 
     * *'END':* Para comparar valores que terminen con el valor. Representado en la query como: ' LIKE CONCAT('%', ?) '
     * 
     * *'RANGO':* Para comparar valores que esten entre dos valores. Representado en la query como: ' BETWEEN ? AND ? '
     * 
     * *'NEGATIVA':* Para comparar valores que no sean iguales. Representado en la query como: ' != ? '
     * 
     * *'COMPARE_ME':* Para comparar valores que sean menores. Representado en la query como: ' < ? '
     * 
     * *'COMPARE_MA':* Para comparar valores que sean mayores. Representado en la query como: ' > ? '
     * 
     * *'COMPARE_ME_I':* Para comparar valores que sean menores o iguales a. Representado en la query como: ' <= ? '
     * 
     * *'COMPARE_MA_I':* Para comparar valores que sean mayores o iguales a. Representado en la query como: ' >= ? '
     * 
     * *'NOT_IN':* Para valores no incluidos en un arreglo. 
     * Representado en la query como: ' NOT IN (?) ' o ' NOT IN (?,?,...) ' dependiendo de la cantidad de valores en el arreglo.
     * 
     * *'IN':* Para valores incluidos en un arreglo.
     * Representado en la query como: ' IN (?) ' o ' IN (?,?,...) ' dependiendo de la cantidad de valores en el arreglo.
     * 
     * "separator" Será un arreglo de operadores de comparación, tales como: [Y,O] para AND y OR respectivamente.
     * 
     * @link /docs/develop/queryStringCondition
     * @return array
     */
    protected function calculate(string $table, string $function = 'count', string $field = 'id', array $cond = null): array
    {
        if (empty($table)) {
            return ['error' => ['code' => '00400', 'message' => "A table name is need it."], 'data' => array()];
        } else {
            return $this->getDBDataFunction($function, $table, $field, $cond);
        }
    }


    /**
     * Inserción de datos.
     *
     * @param string $type [$type = "insert"] Tipo de consulta a realizarse
     * @param string $table [$table = "db_table"] Tabla donde se realizarán la operaciones.
     * @param array $fields [$fields = "campo1, campo2"] Campos a insertarse.
     * @param array $values [$values = "value1,value2"] Valores a insertarse en la lista de campos proporcionada, 
     * estos deben ser la misma cantidad de campos en la lista.
     * @param array $params [$params => [condicion=[['table','type','field','value']], separador=[Y]]] Condición y separador para la consulta.
     * @return array
     */
    private function setDBData(string $type, string $table, array $fields, array $values, array $params = null)
    {
        $query_Values = array();
        $query_request = "";
        if ($type == 'insert') {
            $query_request = "INSERT INTO $table (";
            $c1 = count($fields) - 1;
            $c2 = count($values) - 1;
            for ($x = 0; $x < count($fields); $x++) {
                if ($x < $c1) {
                    $query_request .= "`$fields[$x]`, ";
                } else {
                    $query_request .= "`$fields[$x]`";
                }
            }
            $query_request .= ") VALUES (";
            for ($x = 0; $x < count($values); $x++) {
                if ($x < $c2) {
                    $query_request .= "?, ";
                } else {
                    $query_request .= "?";
                }
                array_push($query_Values, $values[$x]);
            }
            $query_request .= ");";
        } elseif ($type == 'update') {
            $query_request = "UPDATE $table SET ";
            $c1 = count($fields) - 1;
            for ($x = 0; $x < count($fields); $x++) {
                if ($x < $c1) {
                    $query_request .= "`$fields[$x]` = ?, ";
                } else {
                    $query_request .= "`$fields[$x]` = ?";
                }
                array_push($query_Values, $values[$x]);
            }
            if (!is_null($params) && !empty($params)) {
                $query_request .= " WHERE ";
                $conditions = $this->conditions($params);
                $query_request .= $conditions['cadena'];
                foreach ($conditions['valores'] as $vals) {
                    array_push($query_Values, $vals);
                }
            }
            $query_request .= " ;";
        } elseif ($type == 'delete') {
            $query_request = "DELETE FROM `$table`";
            if (!is_null($params) && !empty($params)) {
                $query_request .= " WHERE ";
                $conditions = $this->conditions($params);
                $query_request .= $conditions['cadena'];
                $query_Values = $conditions['valores'];
            }
            $query_request .= " ;";
        } else {
            return ['data'  => array(), 'error' => ['code' => '00400', 'message' => "The statement is not admited"]];
        }
        $result = $this->getResponse($type, ['prepare_string' => $query_request, 'params' => $query_Values], $this->base);
        return $this->interpreter($type, $result);
    }
    /**
     * Extrae datos de la base de datos.
     *
     * @param array|string $query Campos a extraer de la tabla, con el nombre de la tabla como indice principal
     * Puede contener un array "joins" Uniones de tablas a realizar. Puede ser null o vacio.
     * Puede contener un array "params" dentro vendra, conditions y separators. Ambos pueden ser null o vacios.
     * @param int $limit Límite de datos a extraer.
     * @param string $order Orden de los datos extraidos.
     * @param string $orderby Filtro para ordenar los registros.
     * @return array
     */
    private function getDBData($data, $limit, $order, $orderby): array
    {
        $values = [];
        if (is_array($data) && !empty($data)) {
            $string = "SELECT ";
            if (is_string($data['fields'])) {
                $string .= ($data['fields'] == "all") ? " * " : $data['fields'];
            } else {
                $t = count($data['fields']) - 1;
                $y = 0;
                foreach ($data['fields'] as $tabla => $fields) {
                    $fc = count($fields);
                    for ($x = 0; $x < $fc; $x++) {
                        $asignado = explode("=", $fields[$x]);
                        if (count($asignado) > 1) {
                            $string .= "`$tabla`.`$asignado[0]` AS '$asignado[1]'";
                        } else {
                            $string .= "`$tabla`.`$fields[$x]`";
                        }
                        if ($x < ($fc - 1))
                            $string .= ", ";
                    }
                    if ($y < $t) $string .= ", ";
                    $y++;
                }
                unset($tabla, $fields, $fc, $x, $y, $t);
            }
            if (isset($data['main'])) {
                $string .= " FROM `$data[main]`";
            } else {
                $string .= " FROM `$data[fields][0]`";
            }
            if (isset($data['joins']) && !is_null($data['joins']) && !empty($data['joins'])) {
                foreach ($data['joins'] as $type => $parameters) {
                    $a = 0;
                        /* foreach ($parameters as $clave => $value) {
                            if (!is_string($clave)) {
                                $string .= " $type JOIN ";
                                $table1 = key($value[0]);
                                $table2 = key($value[1]);
                                $string .= "`$table1` ON `$table1`.`$value[0]` = `$table2`.`$value[1]`";
                            }
                        } */
                    if (isset($parameters)) {
                        if (is_array($parameters[$a])) {   
                            if (!is_string(key($parameters[$a]))) {
                                $table1 = key($parameters[$a][0]);
                                $table2 = key($parameters[$a][1]);
                                $string .= " $type JOIN `$table1` ON `$table1`.`$parameters[$a][0]` = `$table2`.`$parameters[$a][1]`";
                            } else {
                                $table1 = key($parameters[$a]);
                                foreach ($parameters[$a][2] as $value) {
                                    $string .= " $type JOIN `$value` ON `$value`.`$table1[1]` = `$table1`.`$table1[0]`";
                                }
                            }
                        } else {
                            $string .= " $type JOIN " . $parameters;
                        }
                    }
                    $a++;
                    //$string .= " $type JOIN `$join[table]` ON `$join[table]`.`$join[filter]` = `$join[compare_table]`.`$join[compare_filter]`";
                }
            }
            if (isset($data['params']) && !is_null($data['params']) && !empty($data['params'])) {
                $string .= " WHERE ";
                if (is_array($data['params'])) {
                    $conditions = $this->conditions($data['params']);
                    $string .= $conditions['cadena'];
                    $values = $conditions['valores'];
                } else {
                    $string .= $data['params'];
                }
            }
        } else {
            $array = explode('from',$data);
            $fields = $array[0];
            $array = explode('join',$array[1]);
            $table = $array[0];
            $array = explode('where',$array[1]);
            $joins = $array[0];
            $where = $array[1];
            $string = "SELECT $fields FROM $table $joins WHERE $where";
        }
        if ($order != '' and $orderby != '') {
            if ($order != NULL and $orderby != NULL) {
                $order = strtoupper($order);
                switch ($order) {
                    case 'ASC':
                        $string .= " ORDER BY $orderby ASC ";
                        break;
                    case 'DES':
                        $string .= " ORDER BY $orderby DESC ";
                        break;
                    case 'GROUP':
                        $string .= " GROUP BY $orderby ";
                        break;
                    default:
                        $string .= "";
                        break;
                }
            }
        }
        if ($limit > 0) {
            $string .= " LIMIT " . $limit . ";";
        } else {
            $string .= ";";
        }
        return $this->interpreter('select', $this->getResponse('select', ['prepare_string' => $string, 'params' => $values], $this->base));
    }
    /**
     * Obtiene la cuenta, suma, promedio, mínimo o máximo de un campo de una tabla.
     *
     * @param string $table Tabla a realizarle la consulta.
     * @param string $campo Campo por el cual se realizará la consulta.
     * @param array $condicion [$params => [condicion=[['table','type','field','value']], separador=[Y]]] Condición y separador para la consulta.
     * @return array
     */
    private function getDBDataFunction($function, $table, $campo, $condicion): array
    {
        $values = [];
        $string = "SELECT ";
        switch ($function) {
            case "min":
                $string .= "MIN";
                break;
            case "max":
                $string .= "MAX";
                break;
            case "avg":
                $string .= "AVG";
                break;
            case "sum":
                $string .= "SUM";
                break;
            case "dist":
                $string .= "DISTINCT";
                break;
            default:
                $string .= "COUNT";
                break;
        }
        if ($function != "dist") {
            $string .= "(?) AS 'res' FROM `$table`";
            $values[] = "`$table`.`$campo`";
        } else {
            $string .= "(`$campo`) FROM `$table`";
        }
        if (!is_null($condicion)) {
            $string .= " WHERE ";
            $conditions = $this->conditions($condicion);
            $string .= $conditions['cadena'];
            foreach ($conditions['valores'] as $item) {
                array_push($values, $item);
            }
        }
        $string .= ";";
        return $this->interpreter('select', $this->getResponse('select', ['prepare_string' => $string, 'params' => $values], $this->base));
    }
    /**
     * Devuelve el resultado de la consulta, dependiendo sí hay o no un error.
     * @param string $type Tipo de consulta.
     * @param array $result Arreglo de resultados.
     * @return array
     */
    private function interpreter(string $type, array $result): array
    {
        $error = array();
        $data =  array();
        if (isset($result['error']['code']) && !empty($result['error']['code'])) {
            return ['data' => [], 'error' => $result['error']];
        } else {
            if ($type == "select") {
                $data = $result['rows'];
            } elseif ($type == "insert") {
                $data = $result['lastid'];
            } else {
                $data = $result['affected'];
            }
            if (isset($result['error']['extra'])) {
                if ($result['error']['extra'][0] != "00000") {
                    $error = $result['error']['extra'];
                }
            }
        }
        return ['data' => $data, 'error' => $error];
    }
    /**
     * Genera una cadena de condiciones para la consulta.
     * @param array $arreglo Arreglo de condiciones.
     * @return array
     */
    private function conditions(array $arreglo): array
    {
        $string = '';
        $values = array();
        if (isset($arreglo['condition']) && !empty($arreglo['condition'])) {
            foreach ($arreglo['condition'] as $indice => $cond) {
                if ($indice > 0) {
                    $separador = ($arreglo['separator'][($indice - 1)]) ?? null;
                    if (isset($separador) && !is_null($separador)) {
                        switch ($separador) {
                            case "Y":
                                $string .= " AND ";
                                break;
                            case "O":
                                $string .= " OR ";
                                break;
                        }
                    }
                    $string .= '`' . $cond['table'] . '`.`' . $cond['field'] . '`';
                    switch ($cond['type']) {
                        case 'COMPARATIVE':
                            $string .= ' = ? ';
                            break;
                        case 'SIMILAR':
                            $string .= " LIKE CONCAT('%', ?, '%') ";
                            break;
                        case 'START_WITH':
                            $string .= " LIKE CONCAT(?, '%') ";
                            break;
                        case 'END_WITH':
                            $string .= " LIKE CONCAT('%', ?) ";
                            break;
                        case 'RANGE':
                            $string .= ' BETWEEN ? AND ? ';
                            break;
                        case 'NEGATIVE':
                            $string .= ' != ? ';
                            break;
                        case 'LESS_THAN':
                            $string .= ' < ? ';
                            break;
                        case 'MORE_THAN':
                            $string .= ' > ? ';
                            break;
                        case 'LESS_EQ_TO':
                            $string .= ' <= ? ';
                            break;
                        case 'MORE_EQ_TO':
                            $string .= ' >= ? ';
                            break;
                        case 'NOT_IN';
                            $string .= ' NOT IN (';
                            for ($ind = 0; $ind < count($cond['value']); $ind++) {
                                if (($ind + 1) < count($cond['value'])) {
                                    $string .= '?,';
                                } else {
                                    $string .= '?';
                                }
                            }
                            $string .= ')';
                            break;
                        case 'IS_IN';
                            $string .= ' IN (';
                            for ($ind = 0; $ind < count($cond['value']); $ind++) {
                                if (($ind + 1) < count($cond['value'])) {
                                    $string .= '?,';
                                } else {
                                    $string .= '?';
                                }
                            }
                            $string .= ')';
                            break;
                    }
                    if ($cond['type'] != 'RANGE' && $cond['type'] != 'NOT_IN') {
                        array_push($values, $cond['value']);
                    } else {
                        foreach ($cond['value'] as $item) {
                            array_push($values, $item);
                        }
                    }
                }
            }
        }
        return ['cadena' => $string, 'valores' => $values];
    }
}
