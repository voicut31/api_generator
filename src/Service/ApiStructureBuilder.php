<?php

declare(strict_types=1);

namespace ApiGenerator\Service;

use ApiGenerator\Contract\SchemaInterface;

/**
 * Class ApiStructureBuilder
 * Builds API structure from database schema
 *
 * @package ApiGenerator\Service
 */
class ApiStructureBuilder
{
    private SchemaInterface $schema;

    public function __construct(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Build API structure from database tables
     *
     * @return array
     */
    public function build(): array
    {
        $tables = $this->schema->getTables();
        return $this->createApiStructure($tables);
    }

    /**
     * Create API structure from tables
     *
     * @param array $tables
     * @return array
     */
    private function createApiStructure(array $tables): array
    {
        $apiStructure = [];

        if (count($tables) > 0) {
            foreach ($tables as $table) {
                $columns = $this->schema->getTableColumns($table);
                foreach ($columns as $columnName => $column) {
                    $apiStructure[$table][$columnName] = (string)$column->getType();
                }
            }
        }

        return $apiStructure;
    }
}

