<?php

declare(strict_types=1);

namespace NattiPress\NattiCore\Database\Migration;

use NattiPress\NattiCore\Database\Database;

/**
 * FMigration Class
 */

class FMigration extends Database
{
    private $columns = [];
    private $keys = [];
    private $data = [];
    private $primaryKeys = [];
    private $foreignKeys = [];
    private $uniqueKeys = [];
    private $fullTextKeys = [];

    public function createTable(string $table)
    {
        $query = "CREATE TABLE IF NOT EXISTS $table (";
        $query .= implode(",", $this->columns) . ',';

        foreach ($this->primaryKeys as $key) {
            $query .= "PRIMARY KEY ($key),";
        }

        foreach ($this->keys as $key) {
            $query .= "KEY ($key),";
        }

        foreach ($this->uniqueKeys as $key) {
            $query .= "UNIQUE KEY ($key),";
        }

        foreach ($this->fullTextKeys as $key) {
            $query .= "FULLTEXT KEY ($key),";
        }

        $query = rtrim($query, ",") . ") ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4";

        $this->query($query);

        echo "\n\rTable $table created successfully!";
        return $this;
    }

    public function insert(string $table)
    {
        foreach ($this->data as $row) {
            $keys = array_keys($row);
            $columns_string = implode(",", $keys);
            $values_string = ':' . implode(",:", $keys);

            $query = "INSERT INTO $table ($columns_string) VALUES ($values_string)";
            $this->query($query, $row);
        }

        $this->data = [];
        echo "\n\rData inserted successfully in table: $table";
        return $this;
    }

    public function addColumn(string $column)
    {
        $this->columns[] = $column;
        return $this;
    }

    public function addKey(string $key)
    {
        $this->keys[] = $key;
        return $this;
    }

    public function addPrimaryKey(string $primaryKey)
    {
        $this->primaryKeys[] = $primaryKey;
        return $this;
    }

    public function addUniqueKey(string $key)
    {
        $this->uniqueKeys[] = $key;
        return $this;
    }

    public function addFullTextKey(string $key)
    {
        $this->fullTextKeys[] = $key;
        return $this;
    }

    public function addData(array $data)
    {
        $this->data[] = $data;
        return $this;
    }

    public function dropTable(string $table)
    {
        $query = "DROP TABLE IF EXISTS $table";
        $this->query($query);

        echo "\n\rTable $table deleted successfully!";
        return $this;
    }

    public function renameTable(string $oldTableName, string $newTableName)
    {
        $query = "ALTER TABLE $oldTableName RENAME TO $newTableName";
        $this->query($query);

        echo "\n\rTable $oldTableName renamed to $newTableName";
        return $this;
    }

    public function addForeignKey(string $table, string $foreignColumn, string $referencedTable, string $referencedColumn)
    {
        $constraintName = "fk_{$table}_{$foreignColumn}_{$referencedTable}_{$referencedColumn}";
        $query = "ALTER TABLE $table ADD CONSTRAINT $constraintName FOREIGN KEY ($foreignColumn) REFERENCES $referencedTable($referencedColumn)";
        $this->query($query);

        echo "\n\rForeign key constraint added to $table referencing $referencedTable";
        return $this;
    }

    public function removeColumn(string $table, string $column)
    {
        $query = "ALTER TABLE $table DROP COLUMN $column";
        $this->query($query);

        echo "\n\rColumn $column removed from table $table";
        return $this;
    }

    public function dropTableIfExists(string $table)
    {
        if ($this->tableExists($table)) {
            $query = "DROP TABLE $table";
            $this->query($query);

            echo "\n\rTable $table deleted successfully!";
        } else {
            echo "\n\rTable $table does not exist. Nothing to drop.";
        }

        return $this;
    }

    private function tableExists(string $table)
    {
        $query = "SHOW TABLES LIKE '$table'";
        $result = $this->query($query);

        return !empty($result);
    }

    public function schemaDiff(array $expectedSchema)
    {
        $currentSchema = $this->getCurrentDatabaseSchema(); // Implement this method

        // Compare tables
        $tablesToAdd = array_diff(array_keys($expectedSchema), array_keys($currentSchema));
        $tablesToDrop = array_diff(array_keys($currentSchema), array_keys($expectedSchema));

        // Generate SQL for tables to add
        foreach ($tablesToAdd as $table) {
            $this->createTable($table);
        }

        // Generate SQL for tables to drop
        foreach ($tablesToDrop as $table) {
            $this->dropTable($table);
        }

        // Compare columns for existing tables
        foreach ($expectedSchema as $table => $columns) {
            if (isset($currentSchema[$table])) {
                $columnsToAdd = array_diff_assoc($columns, $currentSchema[$table]);
                $columnsToDrop = array_diff_assoc($currentSchema[$table], $columns);

                // Generate SQL for columns to add
                foreach ($columnsToAdd as $columnName => $columnDefinition) {
                    $this->addColumnToTable($table, $columnName, $columnDefinition);
                }

                // Generate SQL for columns to drop
                foreach ($columnsToDrop as $columnName => $columnDefinition) {
                    $this->dropColumnFromTable($table, $columnName);
                }
            }
        }

        echo "\n\rSchema differences applied successfully!";
        return $this;
    }

    public function getCurrentDatabaseSchema()
    {
        // Initialize an array to store the schema information
        $schema = [];

        // Query to retrieve table names
        $tableQuery = "SHOW TABLES";
        $tables = $this->query($tableQuery); // Implement the query method

        // Loop through the tables and retrieve column information
        foreach ($tables as $tableRow) {
            $tableName = $tableRow['Tables_in_your_database_name']; // Replace with your database name
            $columns = $this->getTableColumns($tableName); // Implement the getTableColumns method

            // Add table columns to the schema
            $schema[$tableName] = $columns;
        }

        return $schema;
    }

    private function getTableColumns(string $tableName)
    {
        // Query to retrieve column information for a specific table
        $columnQuery = "SHOW COLUMNS FROM $tableName";
        return $this->query($columnQuery); // Implement the query method
    }

    public function addColumnToTable(string $tableName, string $columnName, string $columnDefinition)
    {
        // Construct the SQL query to add the column to the table
        $query = "ALTER TABLE $tableName ADD COLUMN $columnName $columnDefinition";

        // Execute the SQL query
        $this->query($query);

        echo "\n\rColumn $columnName added to table $tableName";
        return $this;
    }

    public function dropColumnFromTable(string $tableName, string $columnName)
    {
        // Check if the column exists in the table
        if ($this->columnExistsInTable($tableName, $columnName)) {
            // Construct the SQL query to drop the column from the table
            $query = "ALTER TABLE $tableName DROP COLUMN $columnName";

            // Execute the SQL query
            $this->query($query);

            echo "\n\rColumn $columnName dropped from table $tableName";
        } else {
            echo "\n\rColumn $columnName does not exist in table $tableName. Nothing to drop.";
        }

        return $this;
    }

    private function columnExistsInTable(string $tableName, string $columnName)
    {
        // Query to check if the column exists in the table
        $query = "SELECT COUNT(*) as count FROM information_schema.columns WHERE table_name = :table AND column_name = :column";
        $params = [':table' => $tableName, ':column' => $columnName];
        $result = $this->query($query, $params);

        // Check if the count is greater than 0 (column exists)
        return !empty($result) && $result[0]['count'] > 0;
    }
}
