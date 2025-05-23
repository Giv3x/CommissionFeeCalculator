<?php

namespace Ggiv3x\CommissionFee\Model;

use Ggiv3x\CommissionFee\Model\Model;
use Ggiv3x\Database\IDB;

class UserOperation implements Model
{
    var $table_name = "";
    protected $db;

    function __construct(IDB $db, $data = array())
    {
        $this->db = $db;
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    function all()
    {
        $this->db->connect();
        $operations = $this->db->get($this->table_name, "", "");
        $this->db->close();

        return $operations;
    }
}
