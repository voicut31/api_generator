<?php

declare(strict_types=1);

namespace ApiGenerator\Service;

use ApiGenerator\Contract\SchemaInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Column;

/**
 * Class Schema
 * Handles database schema operations
 *
 * @package ApiGenerator\Service
 */
class Schema implements SchemaInterface
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
     * Get all tables from the database
     *
     * @return string[]
     */
    public function getTables(): array
    {
        return $this->conn->getSchemaManager()->listTableNames();
    }

    /**
     * Get columns for a specific table
     *
     * @param string $table
     * @return Column[]
     */
    public function getTableColumns(string $table): array
    {
        return $this->conn->getSchemaManager()->listTableColumns($table);
    }

    /**
     * Get all results from a module/table
     *
     * @param string $module
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getResults(string $module): array
    {
        return $this->conn
            ->createQueryBuilder()
            ->select('*')
            ->from($module)
            ->execute()
            ->fetchAllAssociative();
    }

    /**
     * Get a single result by ID
     *
     * @param string $module
     * @param int|string $id
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getResult(string $module, int|string $id): array
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
     * @param string $module
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function insert(string $module, array $params): mixed
    {
        $keys = [];
        foreach ($params as $i => $v) {
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
     * @param string $module
     * @param int|string $id
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function update(string $module, int|string $id, array $params): int
    {
        $queryBuilder = $this->conn->createQueryBuilder();

        $query = $queryBuilder->update($module);
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
     * @param string $module
     * @param int|string $id
     * @return mixed
     * @throws Exception
     */
    public function delete(string $module, int|string $id): mixed
    {
        return $this->conn
            ->createQueryBuilder()
            ->delete($module)
            ->where('id = :id')
            ->setParameter(':id', $id)
            ->execute();
    }
}

