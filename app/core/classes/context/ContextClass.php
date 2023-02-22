<?php

namespace app\core\classes\context;

/**
 * Clase para las transacciones entre los modelos y la base de datos.
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @version 1.2.3
 * 19/01/23
 */
class ContextClass extends ConnectionClass
{
    protected $base;
    /**
     * Esta función se encarga de traer todos los registros solicitados a una tabla
     *
     * @param string $tableName será el nombre de la tabla de donde se obtendran los registros
     * @param string|array $data Campos a ser extraidos de la tabla sugerida.
     * si es un array, deberá contener "fields" para agrupar los campos a extraerse por tablas, anexando el nombre de la tabla como indice y como valor del indice el arreglo de los campos,
     * "joins" sí es necesaria una tabla adicional con el arreglo ['type', 'table', 'filter','compare_table', 'compare_filter']
     * "params" condición a ser cumplida para ser devuelta la información.
     * Los parametros deben estar delimitados por ['coma'|'punto y coma'|'tilde'|'numeral'].
     * El signo (coma) se utiliza para hacer referencia a 'AND'.
     * El signo (punto y coma) se utiliza para hacer referencia a 'OR'.
     * El signo (tilde o viñeta) se utiliza como referencia para 'LIKE'.
     * El signo (numeral) hace referencia a 'BETWEEN'
     * Cada condición debe ser separada por ':'(dos puntos) en cada parametro "a=2, b>c" será "a=:2, b>:10" esto se comvertirá en dos datos uno la condición "a=? AND b>?" y el segundo el parametro "2,10".
     * @param int $limit Límite de registros a ser devueltos.
     * @param string $sort Orden de los registros a ser devueltos, ASC, DESC, GROUP.
     * @param string $sortBy Indice para realizar el orden o agrupación.
     *
     * @link /docs/develop/queryStringCondition
     * @return array
     */
    protected function select(string $tableName, array $data, int $limit = 1000, string $sort = '', string $sortBy = ''): array
    {
        if (empty($tableName)) {
            $response = ['error' => ['code' => 404, 'message' => "No hay tabla para consultar."], 'data' => array()];
        } else {
            $response = $this->getDBData($tableName, $data, $limit, $sort, $sortBy);
        }
        return $response;
    }
    /**
     * Función para la inserción de datos a la base de datos.
     *
     * @param string $tableName Nombre de la tabla a ser afectada
     * @param array $data Arreglo de datos a insertar, en los indices 'fields' y 'values'.
     * fields, debe ser un arreglo de campos.
     * values, debe ser un arreglo de valores.
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
     *
     * @param string $tableName Nombre de la tabla a ser afacetada.
     * @param array $data Arrelo de valores a ser eliminados, ['fields','values','params']; los parametros deben estar delimitados por [',',';','~'];
     * cada condición debe ser separada por ':' en cada parametro "a=2, b>c" será "a=:2, b>:c" esto se comvertirá en dos datos uno la condición "a=" y el segundo el parametro "2".
     * El signo ','(coma) se utiliza para hacer referencia a 'AND', el signo ';'(punto y coma) se utiliza para hacer referencia a 'OR' y el signo '~'(viñeta) se utiliza como referencia para 'LIKE'
     * cada condición debe ser separada por ':' en cada parametro "a=2, b>10" será "a=:2, b>:10" esto se comvertirá en dos datos uno la condición "a=? AND b>?" y el segundo el parametro "2,10".
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
     * Función para realizar edición en registros en la base de datos.
     *
     * @param string $tableName Nombre de la tabla a ser afacetada.
     * @param array $data Arreglo de valores a ser actualizados en los indices 'fields', 'values' y 'params'; los parametros deben estar delimitados por [','|';'|'~']
     * El signo ','(coma) se utiliza para hacer referencia a 'AND', el signo ';'(punto y coma) se utiliza para hacer referencia a 'OR' y el signo '~'(viñeta) se utiliza como referencia para 'LIKE'
     * cada condición debe ser separada por ':' en cada parametro "a=2, b>10" será "a=:2, b>:10" esto se comvertirá en dos datos uno la condición "a=? AND b>?" y el segundo el parametro "2,10".
     *
     * @return array
     */
    protected function update(string $tableName, array $data): array
    {
        if (empty($table)) {
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
     * @param array $cond Los parametros deben estar delimitados por [',',';','~'];
     * El signo ','(coma) se utiliza para hacer referencia a 'AND', el signo ';'(punto y coma) se utiliza para hacer referencia a 'OR' y el signo '~'(viñeta) se utiliza como referencia para 'LIKE'
     * cada condición debe ser separada por ':' en cada parametro "a=2, b>10" será "a=:2, b>:10" esto se comvertirá en dos datos uno la condición "a=? AND b>?" y el segundo el parametro "2,10".
     *
     * @link /docs/develop/queryStringCondition
     * @return array
     */
    protected function calculate(string $table, string $function = 'count', string $field = 'id', array $cond = null): array
    {
        if (empty($table)) {
            return ['error' => ['code' => 400, 'message' => "A table name is need it."], 'data' => array()];
        } else {
            return $this->getDBDataFunction($function, $table, $field, $cond);
        }
    }


    /**
     * Esta función sirve para insertar registros en la base de datos según la infromacion entregada por el usuario.
     *
     * @param string $type [$type = "insert"] Tipo de consulta a realizarse
     * @param string $table [$table = "db_table"] Tabla donde se realizarán la operaciones.
     * @param array $fields [$fields = "campo1, campo2"] Campos a insertarse.
     * @param array $values [$values = "value1,value2"] Valores a insertarse en la lista de campos proporcionada,
     * estos deben ser la misma cantidad de campos en la lista.
     * @param string $p
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
                $query_Values = $conditions['valores'];
            }
            /* $pspt = preg_split('/([,|;|~])/', $params, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            foreach ($pspt as $ps) {
                if ($ps == ",") {
                    $query_request .= " AND ";
                } elseif ($ps == ";") {
                    $query_request .= " OR ";
                } elseif ($ps == "~") {
                    $query_request .= " LIKE ";
                } else {
                    $pair = explode(":", $ps);
                    $query_request .= "$pair[0] ?";
                    array_push($query_Values, $pair[1]);
                }
            } */
            $query_request .= " ;";
        } elseif ($type == 'delete') {
            $query_request = "DELETE FROM `$table`";
            if (!is_null($params) && !empty($params)) {
                $query_request .= " WHERE ";
                $conditions = $this->conditions($params);
                $query_request .= $conditions['cadena'];
                $query_Values = $conditions['valores'];
            }
            /* $pspt = preg_split('/([,|;|~])/', $params, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            foreach ($pspt as $ps) {
                if ($ps == ",") {
                    $query_request .= " AND ";
                } elseif ($ps == ";") {
                    $query_request .= " OR ";
                } elseif ($ps == "~") {
                    $query_request .= " LIKE ";
                } else {
                    $pair = explode(":", $ps);
                    $query_request .= "$pair[0] ?";
                    array_push($query_Values, $pair[1]);
                }
            } */
            $query_request .= " ;";
        } else {
            return ['data'  => array(), 'error' => ['code' => 400, 'message' => "The statement is not admited"]];
        }
        $result = $this->getResponse($type, ['prepare_string' => $query_request, 'params' => $query_Values], $this->base);
        return $this->interpreter($type, $result);
    }
    /**
     * Get registres from database using a table and fields given from user.
     *
     * @param string $table Table to query for data
     * @param array|string $query Request to query at the database
     * @param array $joins Join for table
     * @param string $params Params to use to filter the data from the table given
     * @param int $limit Limit of register to return
     * @param string $order Ordering for register returned
     * @param string $orderby Filter to order the register given
     * @return array
     */
    private function getDBData($table, $query, $limit, $order, $orderby): array
    {
        $values = [];
        $string = "SELECT ";
        if (is_array($query) && !empty($query)) {
            if (is_string($query['fields'])) {
                $string .= ($query['fields'] == "all") ? " * " : $query;
            } else {
                $t = count($query['fields']) - 1;
                $y = 0;
                foreach ($query['fields'] as $tabla => $fields) {
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
                    if ($y < $t)
                        $string .= ", ";
                    $y++;
                }
            }
        } else {
            $string .= ($query == "all") ? " * " : $query;
        }
        $string .= " FROM `$table`";
        if (!empty($query['joins'])) {
            foreach ($query['joins'] as $join) {
                $string .= " $join[type] JOIN `$join[table]` ON `$join[table]`.`$join[filter]` = `$join[compare_table]`.`$join[compare_filter]`";
            }
        }
        if (!is_null($query['params']) && !empty($query['params'])) {
            $string .= " WHERE ";
            $conditions = $this->conditions($query['params']);
            $string .= $conditions['cadena'];
            $values = $conditions['valores'];
            /* $condiciones = $query['params']['condition'];
            //$condiciones = preg_split('/([,|;|~|#])/', $params, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            $isLike = false;
            foreach ($condiciones as $indice => $condicion) {
                if ($indice > 0) {
                    $separador = ($query['params']['separator'][($indice - 1)]) ?? null;
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
                }
                $string .= $condicion['table'] . '.' . $condicion['field'];
                if ($condicion['type'] == 'COMPARE') {
                    $string .= ' = ? ';
                } elseif ($condicion['type'] == 'SIMILAR') {
                    $string .= " LIKE CONCAT('%', ?, '%') ";
                } elseif ($condicion['type'] == 'RANGO') {
                    $string .= ' BETWEEN ? AND ? ';
                }
                array_push($values, $condicion['value']);
                /* default:
                        if ($isLike === true) {
                            //$pair = explode(":", $cond);
                            //if (count($pair) > 1) {
                                $string .= "CONCAT('%', ?, '%')";
                                array_push($values, $pair[1]);
                            } else {
                                $string .= "$pair[0]";
                            }
                        } else {
                            $pairCond = explode(".", $cond);
                            $string .= "`$pairCond[0]`.";
                            $oprtCond = preg_split('/([\=<>])|(\'<=\')|(\'>=\')|(\'!=\')/', $pairCond[1], -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                            if (count($oprtCond) > 1) {
                                foreach ($oprtCond as $value) {
                                    if ($value == "=" || $value == "<" || $value == ">" || $value == "<=" || $value == ">=" || $value == "!=") {
                                        $string .= $value;
                                    } else {
                                        $pair = explode(":", $value);
                                        if (count($pair) > 1) {
                                            $string .= " ?";
                                            array_push($values, $pair[1]);
                                        } else {
                                            $string .= "`$pair[0]`";
                                        }
                                    }
                                }
                            } else {
                                $string .= "`$pairCond[1]`";
                            }
                        }
                    }
            } */
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
        if ($limit != NULL) {
            if ($limit > 0) $string .= " LIMIT " . $limit . ";";
        } else {
            $string .= ";";
        }
        return $this->interpreter('select', $this->getResponse('select', ['prepare_string' => $string, 'params' => $values], $this->base));
    }
    /**
     * Get a count of rows in a table from database.
     *
     * @param string $table Table to be query for data
     * @param string $campo Field to use to filter data
     * @param array $condicion Condition that have to be perform before data is retrieve
     * @return array
     */
    private function getDBDataFunction($function, $table, $campo, $condicion): array
    {
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
            default:
                $string .= "COUNT";
                break;
        }
        $string .= "(?) AS 'res' FROM `$table`";
        $values[] = "`$table`.`$campo`";
        if (!is_null($condicion)) {
            $string .= " WHERE ";
            $conditions = $this->conditions($condicion);
            $string .= $conditions['cadena'];
            foreach ($conditions['valores'] as $item) {
                array_push($values, $item);
            }
            /* $pspt = preg_split('/([,|;|~|#])/', $condicion, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            $isLike = false;
            foreach ($pspt as $ps) {
                switch ($ps) {
                    case ",":
                        $string .= " AND ";
                        break;
                    case ";":
                        $string .= " OR ";
                        break;
                    case "~":
                        $string .= " LIKE ";
                        $isLike = true;
                        break;
                    case "#":
                        $string .= " BETWEEN ";
                        break;
                    default:
                        if ($isLike === true) {
                            $pair = explode(":", $ps);
                            if (count($pair) > 1) {
                                $string .= "CONCAT('%', ?, '%')";
                                array_push($values, $pair[1]);
                            } else {
                                $string .= "$pair[0]";
                            }
                        } else {
                            $pairCond = explode(".", $ps);
                            $string .= "`$pairCond[0]`.";
                            $oprtCond = preg_split('/([\=<>])|(\'<=\')|(\'>=\')|(\'!=\')/', $pairCond[1], -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                            if (count($oprtCond) > 1) {
                                foreach ($oprtCond as $value) {
                                    if ($value == "=" || $value == "<" || $value == ">" || $value == "<=" || $value == ">=" || $value == "!=") {
                                        $string .= $value;
                                    } else {
                                        $pair = explode(":", $value);
                                        if (count($pair) > 1) {
                                            $string .= " ?";
                                            array_push($values, $pair[1]);
                                        } else {
                                            $string .= "`$pair[0]`";
                                        }
                                    }
                                }
                            }
                        }
                }
            } */
            /* foreach ($condicion['condition'] as $indice => $cond) {
                if ($indice > 0) {
                    $separador = ($condicion['separator'][($indice - 1)]) ?? null;
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
                }
                $string .= $cond['table'] . '.' . $cond['field'];
                if ($cond['type'] == 'COMPARE') {
                    $string .= ' = ? ';
                } elseif ($cond['type'] == 'SIMILAR') {
                    $string .= " LIKE CONCAT('%', ?, '%') ";
                } elseif ($cond['type'] == 'RANGO') {
                    $string .= ' BETWEEN ? AND ? ';
                }
                if ($cond['type'] != 'RANGO') {
                    array_push($values, $condicion['value']);
                } else {
                    foreach ($condicion['value'] as $item) {
                        array_push($values, $item);
                    }
                }
            } */
        }
        $string .= ";";
        return $this->interpreter('select', $this->getResponse('select', ['prepare_string' => $string, 'params' => $values], $this->base));
    }
    /**
     * Agrouping and sorting for data to return
     * @param string $type Type of wuery done
     * @param array $result Array of results
     * @return array
     */
    private function interpreter(string $type, array $result): array
    {
        if (isset($result['error'])) {
            // && $result['error']['code'] != 0
            return ['data' => [], 'error' => $result['error']];
        } else {
            if ($type == "select") {
                return ['data' => $result['rows'], 'error' => []];
            } elseif ($type == "insert") {
                return ['data' => $result['id_row'], 'error' => []];
            } else {
                return ['data'  => $result['row_aff'], 'error' => $result['error']];
            }
        }
    }
    private function conditions(array $arreglo): array
    {
        $string = '';
        $values = array();
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
            }
            $string .= '`' . $cond['table'] . '`.`' . $cond['field'] . '`';
            switch ($cond['type']) {
                case 'COMPARE':
                    $string .= ' = ? ';
                    break;
                case 'SIMILAR':
                    $string .= " LIKE CONCAT('%', ?, '%') ";
                    break;
                case 'START':
                    $string .= " LIKE CONCAT(?, '%') ";
                    break;
                case 'END':
                    $string .= " LIKE CONCAT('%', ?) ";
                    break;
                case 'RANGO':
                    $string .= ' BETWEEN ? AND ? ';
                    break;
                case 'NEGATIVA':
                    $string .= ' != ? ';
                    break;
                case 'COMPARE_ME':
                    $string .= ' < ? ';
                    break;
                case 'COMPARE_MA':
                    $string .= ' > ? ';
                    break;
                case 'COMPARE_ME_I':
                    $string .= ' <= ? ';
                    break;
                case 'COMPARE_MA_I':
                    $string .= ' >= ? ';
                    break;
            }
            if ($cond['type'] != 'RANGO') {
                array_push($values, $cond['value']);
            } else {
                foreach ($cond['value'] as $item) {
                    array_push($values, $item);
                }
            }
        }
        return ['cadena' => $string, 'valores' => $values];
    }
}
