<?php
/**
 * Created by PhpStorm.
 * User: Voicu Tibea
 * Date: 2019-01-21
 * Time: 17:22
 */

namespace ApiGenerator;

class Generator
{
    private $schema;

    private $apiStructure = [];

    /**
     * Generator constructor.
     * @param $conn
     */
    public function __construct($conn)
    {
        $this->schema = new Schema($conn);
    }

    /**
     *
     */
    public function api($module, $id = null, $params = [])
    {
        $this->generate();
        $this->response($module, $id, $params);
    }

    private function response($module, $id = null, $params = [])
    {
        $api = new Api($this->schema, $this->apiStructure);
        $api->response($module, $id, $params);
    }

    /**
     *
     */
    public function generate()
    {
        $tables = $this->schema->getTables();
        $this->createApiStructure($tables);
    }

    /**
     * @param $tables
     */
    private function createApiStructure($tables)
    {
        if (count($tables) > 0) {
            foreach ($tables as $table) {
                $columns = $this->schema->getTableColumns($table);
                foreach ($columns as $i => $v) {
                    $this->apiStructure[$table][$i] = (string)$v->getType();
                }
            }
        }
    }
}
