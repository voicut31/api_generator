<?php
/**
 * Created by PhpStorm.
 * User: VoicuTibea
 * Date: 2019-01-21
 * Time: 17:22
 */

namespace ApiGenerator;


class Schema
{
    public function getTables($conn)
    {
        return $conn->getSchemaManager()->listTableNames();
    }

    public function getTableColumns($conn, $table)
    {
        return $conn->getSchemaManager()->listTableColumns($table);
    }
}
