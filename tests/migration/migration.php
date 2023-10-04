<?php

namespace Migration;

defined('FCPATH') or die("Direct script access denied");

use \Core\Database;

/**
 * Migration class
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

            $query .= ") ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4";

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



/**
 * Usage
 */

 $migration = new \Migration\Migration();

 // Create a table with columns and indexes
 $migration->createTable('users')
     ->int('id')->autoIncrement()->addPrimaryKey()
     ->varchar('username', 50)->nullable()
     ->varchar('email', 100)->addUniqueIndex('email')
     ->insert([
         ['username' => 'john_doe', 'email' => 'john@example.com'],
         ['username' => 'jane_doe', 'email' => 'jane@example.com'],
     ]);
 
 // Drop a table
 $migration->dropTable('temp_table');
 