<?php
namespace ApiGenerator;

class Generator
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function generate()
    {
        $schema = new Schema();
        $tables = $schema->getTables($this->conn);

        foreach ($tables as $table) {
            $columns = $schema->getTableColumns($this->conn, $table);
            print_r($columns);
        }
    }
}
