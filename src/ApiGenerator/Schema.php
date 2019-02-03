<?php
/**
 * Created by PhpStorm.
 * User: Voicu Tibea
 * Date: 2019-01-21
 * Time: 17:22
 */

namespace ApiGenerator;


class Schema
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getTables()
    {
        return $this->conn->getSchemaManager()->listTableNames();
    }

    public function getTableColumns($table)
    {
        return $this->conn->getSchemaManager()->listTableColumns($table);
    }

    public function getResults($module)
    {
        return $this->conn
            ->createQueryBuilder()
            ->select('*')
            ->from($module)
            ->execute()
            ->fetchAll();
    }

    public function getResult($module, $id)
    {
        return $this->conn
            ->createQueryBuilder()
            ->select('*')
            ->from($module)
            ->where('id = :id')
            ->setParameter(':id', $id)
            ->execute()
            ->fetchAll();
    }

    public function insert($module, $params)
    {
        return $this->conn
            ->createQueryBuilder()
            ->insert($module)
            ->values($params)
            ->execute();
    }

    public function update($module, $id, $params)
    {
        $setQuery = '';
        foreach ($params as $i => $v) {
            $setQuery = $setQuery !== '' ? ', ' . $i . '=' . $v : $i . '=' . $v;
        }

        return $this->conn
            ->createQueryBuilder()
            ->update($module)
            ->set($setQuery)
            ->where('id = :id')
            ->setParameter(':id', $id)
            ->execute();
    }

    public function delete($module, $id)
    {
        return $this->conn
            ->createQueryBuilder()
            ->delete($module)
            ->where('id = :id')
            ->setParameter(':id', $id)
            ->execute();
    }
}
