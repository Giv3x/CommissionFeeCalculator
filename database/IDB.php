<?php

namespace Ggiv3x\Database;

interface IDB
{
    function connect();
    function get($table_name, $fields, $where);
    function close();
}
