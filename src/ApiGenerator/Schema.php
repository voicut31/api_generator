<?php
/**
 * Created by PhpStorm.
 * User: Voicu Tibea
 * Date: 2019-01-21
 * Time: 17:22
 */

namespace ApiGenerator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ForwardCompatibility\DriverStatement;
use Doctrine\DBAL\Schema\Column;

/**
 * Class Schema
 * @package ApiGenerator
 */
class Schema
{
    /**
     * @var Connection
     */
    private Connection $conn;

    /**
     * Schema constructor.
     *
     * @param Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Get tables
     *
     * @return string[]
     */
    public function getTables(): array
    {
        return $this->conn->getSchemaManager()->listTableNames();
    }

    /**
     * Get table columns
     *
     * @param $table
     * @return Column[]
     */
    public function getTableColumns($table): array
    {
        return $this->conn->getSchemaManager()->listTableColumns($table);
    }

    /**
     * Get the results
     *
     * @param $module
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getResults($module): array
    {
        return $this->conn
            ->createQueryBuilder()
            ->select('*')
            ->from($module)
            ->execute()
            ->fetchAllAssociative();
    }

    /**
     * Get the result based on id
     *
     * @param $module
     * @param $id
     * @return array[]
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getResult($module, $id):array
    {
        return $this->conn
            ->createQueryBuilder()
            ->select('*')
            ->from($module)
            ->where('id = :id')
            ->setParameter(':id', $id)
            ->execute()
            ->fetchAllAssociative();
    }

    /**
     * Insert a new record
     *
     * @param $module
     * @param $params
     * @return DriverStatement|int
     * @throws Exception
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
     * @return int
     * @throws Exception
     */
    public function update($module, $id, $params): int
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
     * @throws Exception
     */
    public function delete($module, $id): array
    {
        return $this->conn
            ->createQueryBuilder()
            ->delete($module)
            ->where('id = :id')
            ->setParameter(':id', $id)
            ->execute();
    }
}
