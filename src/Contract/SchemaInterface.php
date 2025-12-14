<?php

declare(strict_types=1);

namespace ApiGenerator\Contract;

use Doctrine\DBAL\Schema\Column;

/**
 * Interface SchemaInterface
 * @package ApiGenerator\Contract
 */
interface SchemaInterface
{
    /**
     * Get all tables from the database
     *
     * @return string[]
     */
    public function getTables(): array;

    /**
     * Get columns for a specific table
     *
     * @param string $table
     * @return Column[]
     */
    public function getTableColumns(string $table): array;

    /**
     * Get all results from a module/table
     *
     * @param string $module
     * @return array
     */
    public function getResults(string $module): array;

    /**
     * Get a single result by ID
     *
     * @param string $module
     * @param int|string $id
     * @return array
     */
    public function getResult(string $module, int|string $id): array;

    /**
     * Insert a new record
     *
     * @param string $module
     * @param array $params
     * @return mixed
     */
    public function insert(string $module, array $params): mixed;

    /**
     * Update a record
     *
     * @param string $module
     * @param int|string $id
     * @param array $params
     * @return int
     */
    public function update(string $module, int|string $id, array $params): int;

    /**
     * Delete a record
     *
     * @param string $module
     * @param int|string $id
     * @return mixed
     */
    public function delete(string $module, int|string $id): mixed;
}

