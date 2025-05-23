<?php

namespace Ggiv3x\Mock;

use Ggiv3x\Database\IDB;

class CommissionDbMock implements IDB
{
    var $dbName;
    var $connection;

    function __construct($dbName)
    {
        $this->dbName = $dbName;
    }

    function connect()
    {
        $this->connection = @fopen($this->dbName, "r");
    }

    function get($table_name, $fields, $where = "")
    {
        if ($this->connection) {
            while (($buffer = fgets($this->connection)) !== false) {
                $data = explode(',', $buffer);
                $data = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data);
                $array[] = array(
                    'operation_date' => $data[0],
                    'id' => $data[1],
                    'user_type' => $data[2],
                    'type' => $data[3],
                    'amount' => $data[4],
                    'currency' => $data[5]
                );
            }

            if (!feof($this->connection)) {
                throw new \Exception("Error: unexpected fgets() fail\n");
            }
        } else {
            throw new \Exception("Error: connection closed\n");
        }

        return $array;
    }

    function close()
    {
        fclose($this->connection);
    }
}
