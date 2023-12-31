<?php

declare(strict_types=1);

namespace NattiPress\NattiCore\Database\Migration;

use NattiPress\NattiCore\Database\Database;

/**
 * Migration Class
 */

class Migration extends Database
{
    private $columns = [];
    private $keys = [];
    private $data = [];
    private $primaryKeys = [];
    private $uniqueKeys = [];
    private $fullTextKeys = [];
    private $currentTable;

    public function createTable(string $table)
    {
        if (!empty($this->columns)) {

            $query = "CREATE TABLE IF NOT EXISTS $table (";

            $query .= implode(",", $this->columns) . ',';

            foreach ($this->primaryKeys as $key) {
                $query .= "primary key ($key),";
            }

            $query = trim($query, ",");

            $query .= ") ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4";

            $this->query($query);

            $this->columns = [];
            $this->keys = [];
            $this->data = [];
            $this->primaryKeys = [];
            $this->uniqueKeys = [];
            $this->fullTextKeys = [];

            echo "\n\rTable $table created successfully!";
        } else {

            echo "\n\rColumn data not found! Could not create table: $table";
        }

        $this->currentTable = $table;
        return $this; // Return $this to enable method chaining
    }

    public function addColumn(string $column)
    {
        $this->columns[] = $column;
        return $this; // Return $this to enable method chaining
    }

    public function int(string $columnName)
    {
        $this->addColumn("$columnName INT");
        return $this; // Return $this to enable method chaining
    }

    public function varchar(string $columnName, int $length)
    {
        $this->addColumn("$columnName VARCHAR($length)");
        return $this; // Return $this to enable method chaining
    }

    public function bigint(string $columnName)
    {
        $this->addColumn("$columnName BIGINT");
        return $this; // Return $this to enable method chaining
    }

    public function enum(string $columnName, array $enumValues)
    {
        // Validate enum values to prevent SQL injection
        $enumValuesStr = implode(',', array_map(function ($value) {
            return "'" . addslashes($value) . "'";
        }, $enumValues));

        $this->addColumn("$columnName ENUM($enumValuesStr)");
        return $this; // Return $this to enable method chaining
    }

    public function tinyint(string $columnName)
    {
        // Add a TINYINT column
        $this->addColumn("$columnName TINYINT");
        return $this; // Return $this to enable method chaining
    }

    public function autoIncrement()
    {
        // Set the auto-increment attribute for the last added column
        if (!empty($this->columns)) {
            $lastColumnIndex = count($this->columns) - 1;
            $this->columns[$lastColumnIndex] .= ' AUTO_INCREMENT';
        }
        return $this; // Return $this to enable method chaining
    }

    public function nullable()
    {
        // Set the nullable attribute for the last added column
        if (!empty($this->columns)) {
            $lastColumnIndex = count($this->columns) - 1;
            $this->columns[$lastColumnIndex] .= ' NULL';
        }
        return $this; // Return $this to enable method chaining
    }

    public function addPrimaryKey(string $columnName)
    {
        $query = "ALTER TABLE $this->currentTable ADD PRIMARY KEY ($columnName)";
        $this->query($query);
        return $this; // Return $this to enable method chaining
    }

    public function addUniqueIndex(string $columnName)
    {
        $query = "CREATE UNIQUE INDEX idx_unique_$columnName ON $this->currentTable ($columnName)";
        $this->query($query);
        return $this; // Return $this to enable method chaining
    }

    public function addIndex(string $columnName)
    {
        $query = "CREATE INDEX idx_$columnName ON $this->currentTable ($columnName)";
        $this->query($query);
        return $this; // Return $this to enable method chaining
    }

    public function addData(array $data)
    {
        $this->data[] = $data;
        return $this; // Return $this to enable method chaining
    }

    public function insert()
    {
        if (!empty($this->data) && is_array($this->data)) {

            foreach ($this->data as $row) {

                $keys = array_keys($row);
                $columns_string = implode(",", $keys);
                $values_string = ':' . implode(",:", $keys);

                $query = "INSERT INTO $this->currentTable ($columns_string) VALUES ($values_string)";
                $this->query($query, $row);
            }

            $this->data = [];
            echo "\n\rData inserted successfully in table: $this->currentTable";
        } else {
            echo "\n\rRow data not found! No data inserted in table: $this->currentTable";
        }

        return $this; // Return $this to enable method chaining
    }

    public function dropTable(string $table)
    {
        $query = "DROP TABLE IF EXISTS $table ";
        $this->query($query);

        echo "\n\rTable $table deleted successfully!";
        return $this; // Return $this to enable method chaining
    }
}
