<?php
    namespace app\core\classes\context;
    /**
     * Clase para las transacciones entre los modelos y la base de datos.
     * @author Jorge Echeverria <jecheverria@bytes4run.com>
     * @version 1.0.0
     */
    class ContextClass extends ConnectionClass{
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
        public function select(string $tableName, array $data, int $limit = 1000, string $sort = '', string $sortBy = '') :array {
            if (empty($tableName)) {
                $response = ['error'=>['code'=>404,'message' => "No hay tabla para consultar."], 'data' => array()];
            } else {
                $response = $this->getDBData($tableName,$data,$limit,$sort,$sortBy);
            }
            return $response;
        }
        /**
         * Función que devuelve la cuenta de registros en la tabla sugerida, respetando la condición dada.
         *
         * @param string $table Nombre de la tabla a ser consultada
         * @param string $field Campo por el cual se desea contar los registros, generalmente se usa el id del registro.
         * @param string $cond Los parametros deben estar delimitados por [',',';','~']; 
         * El signo ','(coma) se utiliza para hacer referencia a 'AND', el signo ';'(punto y coma) se utiliza para hacer referencia a 'OR' y el signo '~'(viñeta) se utiliza como referencia para 'LIKE'
         * cada condición debe ser separada por ':' en cada parametro "a=2, b>10" será "a=:2, b>:10" esto se comvertirá en dos datos uno la condición "a=? AND b>?" y el segundo el parametro "2,10".
         * 
         * @link /docs/develop/queryStringCondition
         * @return array
         */
        /* public function getElementsCount(string $table, string $field, string $cond = null):array {
            if (empty($table)) {
                return ['type' => "error", 'message' => "No hay tabla para consultar."];
            } else {
                return $this->getDBTableDataCount($table,$field,$cond);
            }
        } */
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
        public function insert ( string $tableName, array $data ):array {
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
        /* public function deleteData ( string $tableName, array $data ):array {
            if ( is_array($data) ) {
                $fields = $data['fields'];
                $values = $data['values'];
                $params = $data['params'];
                return $this->setDBData( 'update', $tableName, $fields, $values, $params );
            } else {
                return ['type' => "error", 'message' => "La información proporcionada tiene un formato no soportado.", 'data' => array()];
            }
        } */
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
        /* public function editData ( string $tableName, array $data ):array {
            if ( is_array($data) ) {
                $fields = $data['fields'];
                $values = $data['values'];
                $params = $data['params'];
                return $this->setDBData( 'update', $tableName, $fields, $values, $params );
            } else {
                return ['type' => "error", 'message' => "La información proporcionada tiene un formato no soportado.", 'data' => array()];
            }
        } */

        /* public function massDataInsertion (string $table,array $data) {
            return $this->setMassInsertionData("insert",$data);
        } */
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
        protected function setDBData(string $type, string $table, array $fields, array $values, string $params = '' ) {
            $query_Values = array();
            $query_request = "";
            if ($type == 'insert') {
                $query_request = "INSERT INTO $table (";
                $c1 = count($fields) - 1;
                $c2 = count($values) - 1;
                for ($x = 0; $x < count($fields); $x++ ) {
                    if ($x < $c1) {
                        $query_request .= "`$fields[$x]`, ";
                    } else {
                        $query_request .= "`$fields[$x]`";
                    }
                }
                $query_request .= ") VALUES (";
                for ($x = 0; $x < count($values); $x++ ) {
                    if ($x < $c2 ) {
                        $query_request .= "?, ";
                    } else {
                        $query_request .= "?";
                    }
                    array_push($query_Values,$values[$x]);
                }
                $query_request .= ");";
            } else {
                $query_request = "UPDATE $table SET ";
                $c1 = count($fields) - 1;
                for ($x = 0; $x < count($fields); $x++ ) {
                    if( $x < $c1 ) {
                        $query_request .= "`$fields[$x]` = ?, ";
                    } else {
                        $query_request .= "`$fields[$x]` = ?";
                    }
                    array_push($query_Values,$values[$x]);
                }
                $query_request .= " WHERE ";
                $pspt = preg_split('/([,|;|~])/',$params,-1,PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                foreach ( $pspt as $ps ) {
                    if ( $ps == "," ) {
                        $query_request .= " AND ";
                    } elseif ( $ps == ";" ) {
                        $query_request .= " OR ";
                    } elseif ( $ps == "~" ) {
                        $query_request .= " LIKE ";
                    } else {
                        $pair = explode(":",$ps);
                        $query_request .= "$pair[0] ?";
                        array_push($query_Values,$pair[1]);
                    }
                }
                $query_request .= " ;";
            }
            $result = $this->getResponse($type,['prepare_string'=>$query_request,'params'=>$query_Values]);
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
        protected function getDBData($table, $query, $limit, $order,$orderby):array {
            $values = [];
            $string = "SELECT ";
            if (is_array($query) && !empty($query)) {
                $t = count($query['fields']) - 1;
                $y = 0;
                foreach ($query['fields'] as $tabla => $fields) {
                    $fc = count($fields);
                    for ($x = 0; $x < ($fc - 1); $x++ ) {
                        $asignado = explode("=", $fields[$x]);
                        if (count($asignado) > 1) {
                            $string .= "`$tabla`.`$asignado[0]` AS '$asignado[1]'";
                        } else {
                            $string .= "`$tabla`.`$fields[$x]`";
                        }
                        if ($x < $fc) $string .= ", ";
                    }
                    if ($y < $t) $string .= ", ";
                    $y++;
                }
            } else {
                $string .= ($query == "all") ? "* " : $query;
            }
            $string .= " FROM `$table`";
            if ( !empty($query['joins']) ) {
                foreach ( $query['joins'] as $join ) {
                    $string .= " $join[type] JOIN `$join[table]` ON `$join[main_table]`.`$join[main_filter]` = `$join[compare_table]`.`$join[compare_filter]`";
                }
            }
            if (!is_null($query['params'])) {
                $string .= " WHERE ";
                $params = $query['params'];
                $condiciones = preg_split('/([,|;|~|#])/',$params,-1,PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                foreach ( $condiciones as $cond ) {
                    switch ($cond) {
                        case ",":
                            $string .= " AND ";
                            break;
                        case ";":
                            $string .= " OR ";
                            break;
                        case "~":
                            $string .= " LIKE ";
                            break;
                        case "#":
                            $string .= " BETWEEN ";
                            break;
                        default:
                            $pair = explode(":", $cond);
                            if (count($pair) > 1) {
                                $string .= "$pair[0] ?";
                                array_push($values, $pair[1]);
                            } else {
                                $string .= "$pair[0]";
                            }
                            break;
                    }
                }
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
            return $this->interpreter('select',$this->getResponse('select', ['prepare_string' => $string, 'params' => $values]));
        }
        /**
         * Get a count of rows in a table from database.
         *
         * @param string $table Table to be query for data
         * @param string $campo Field to use to filter data
         * @param string $condicion Condition that have to be perform before data is retrieve
         * @return array
        */
        /* protected function getDBTableDataCount($table,$campo,$condicion):array {
            $dbConnection = new ConnectionClass;
            $string = "SELECT COUNT(?) AS 'qnt' FROM $table";
            $values[] = $campo;
            if (!is_null($condicion)) {
                $string .= " WHERE ";
                $pspt = preg_split('/([,|;|~|#])/',$condicion,-1,PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                //var_dump($pspt);
                foreach ( $pspt as $ps ) {
                    if ( $ps == "," ) {
                        $string .= " AND ";
                    } elseif ( $ps == ";" ) {
                        $string .= " OR ";
                    } elseif ( $ps == "~" ) {
                        $string .= " LIKE ";
                    } elseif ( $ps == "#" ) {
                        $string .= " ORDER ";
                    } else {
                        $pair = explode(":",$ps);
                        if (count($pair) > 1) {
                            $string .= "$pair[0] ?";
                            array_push($values,$pair[1]);
                        } else {
                            $string .= "$pair[0]";
                        }
                    }
                }
            }
            $string .= ";";
            //var_dump($string);
            //var_dump($values);
            $consulta = $dbConnection->consulta(['prepare_string'=>$string,'params'=>$values], 'select');
            //var_dump($consulta);
            if (isset($consulta['errors']['code']) && !empty($consulta['errors']['code'])) {
                return ['type' => "error", 'message' => $consulta['errors'], 'data' => array()];
            } else {
                return (count($consulta['rows']) > 0) ? ['type' => "success", 'message' => null, 'data' => $consulta['rows']] : ['type' => "error", 'message' => "No hay registros en la tabla.", 'data' => []];
            }
        } */
        /* protected function setMassInsertionData ($type,$values) {
            $dbConnection = new ConnectionClass;
            $result = $dbConnection->consulta(['prepare_string'=>$values['string'],'params'=>$values['params']], $type);
            if (isset($result['errors']['code']) && !empty($result['errors']['code'])) {
                $response = ['type' => "error",'message'=> $result['errors'],'data'=>['rows' => [],'affrows' => null,'lastid' => null,]];
            } else {
                $response = ['type' => "success",'message' => null,'data' => ['rows' => $result['rows'],'affrows' => $result['row_aff'],'lastid' => $result['id_row'],]];
            }
            return $response;
        } */
        /**
         * Agrouping and sorting for data to return
         * @param string $type Type of wuery done
         * @param array $result Array of results
         * @return array
         */
        private function interpreter(string $type, array $result) :array
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
    }
?>