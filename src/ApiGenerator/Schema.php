<?php
/**
 * Created by PhpStorm.
 * User: Voicu Tibea
 * Date: 2019-01-21
 * Time: 17:22
 */

namespace ApiGenerator;

/**
 * Class Schema
 * @package ApiGenerator
 */
class Schema
{
    /**
     * @var
     */
    private $conn;

    /**
     * Schema constructor.
     *
     * @param $conn
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Get tables
     *
     * @return mixed
     */
    public function getTables()
    {
        return $this->conn->getSchemaManager()->listTableNames();
    }

    /**
     * Get table columns
     *
     * @param $table
     * @return mixed
     */
    public function getTableColumns($table)
    {
        return $this->conn->getSchemaManager()->listTableColumns($table);
    }

    /**
     * Get the results
     *
     * @param $module
     * @return mixed
     */
    public function getResults($module)
    {
        return $this->conn
            ->createQueryBuilder()
            ->select('*')
            ->from($module)
            ->execute()
            ->fetchAll();
    }

    /**
     * Get the result based on id
     *
     * @param $module
     * @param $id
     * @return mixed
     */
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

    /**
     * Insert a new record
     *
     * @param $module
     * @param $params
     * @return mixed
     */
    public function insert($module, $params)
    {
        $keys = [];
        foreach($params as $i => $v) {
            $keys[$i] = '?';
        }

        $query = $this->conn
            ->createQueryBuilder()
            ->insert($module)
            ->values($keys);
        $query->setParameters(array_values($params));

        return $query->execute();
    }

    /**
     * Update a record
     *
     * @param $module
     * @param $id
     * @param $params
     * @return mixed
     */
    public function update($module, $id, $params)
    {
        $queryBuilder = $this->conn->createQueryBuilder();

        $query = $queryBuilder
            ->update($module);
        foreach ($params as $i => $v) {
            $query->set($i, $queryBuilder->expr()->literal($v));
        }

        $q = $query->where('id = :id')
            ->setParameter(':id', (int)$id);

        return $q->execute();
    }

    /**
     * Delete a record
     *
     * @param $module
     * @param $id
     * @return mixed
     */
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
