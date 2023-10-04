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
    private $foreignKeys = [];
    private $uniqueKeys = [];
    private $fullTextKeys = [];

    public function createTable(string $table)
    {
        if (!empty($this->columns)) {
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

            $query = trim($query, ",");

            $query .= ") ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4";

            $this->query($query);

            $this->columns = [];
            $this->keys = [];
            $this->data = [];
            $this->primaryKeys = [];
            $this->foreignKeys = [];
            $this->uniqueKeys = [];
            $this->fullTextKeys = [];

            echo "\n\rTable $table created successfully!";
        } else {
            echo "\n\rColumn data not found! Could not create table: $table";
        }
    }

    public function insert(string $table)
    {
        if (!empty($this->data) && is_array($this->data)) {
            foreach ($this->data as $row) {
                // Validate the data before insertion
                if ($this->validateData($table, $row)) {
                    $keys = array_keys($row);
                    $columns_string = implode(",", $keys);
                    $values_string = ':' . implode(",:", $keys);

                    $query = "INSERT INTO $table ($columns_string) VALUES ($values_string)";
                    $this->query($query, $row);
                } else {
                    echo "\n\rData validation failed for a row in table: $table";
                    // You can add custom error handling or logging here
                }
            }

            $this->data = [];
            echo "\n\rData inserted successfully in table: $table";
        } else {
            echo "\n\rRow data not found! No data inserted in table: $table";
        }
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

    public function varchar(string $columnName, int $length)
    {
        // Add a VARCHAR column
        $this->addColumn("$columnName VARCHAR($length)");
        return $this; // Return $this to enable method chaining
    }

    public function int(string $columnName)
    {
        // Add an INT column
        $this->addColumn("$columnName INT");
        return $this; // Return $this to enable method chaining
    }

    public function bigint(string $columnName)
    {
        // Add a BIGINT column
        $this->addColumn("$columnName BIGINT");
        return $this; // Return $this to enable method chaining
    }

    public function enum(string $columnName, array $enumValues)
    {
        // Validate enum values to prevent SQL injection
        $enumValuesStr = implode(',', array_map(function ($value) {
            return "'" . addslashes($value) . "'";
        }, $enumValues));

        // Add an ENUM column
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

    public function modifyColumn(string $columnName, string $newDefinition)
    {
        // Modify an existing column
        $query = "ALTER TABLE $table MODIFY COLUMN $columnName $newDefinition";
        $this->query($query);
        return $this; // Return $this to enable method chaining
    }

    public function addPrimaryKey(string $columnName)
{
    // Add a primary key constraint
    $query = "ALTER TABLE $this->currentTable ADD PRIMARY KEY ($columnName)";
    $this->query($query);
    return $this; // Return $this to enable method chaining
}

public function addUniqueIndex(string $columnName)
{
    // Add a unique index
    $query = "CREATE UNIQUE INDEX idx_unique_$columnName ON $this->currentTable ($columnName)";
    $this->query($query);
    return $this; // Return $this to enable method chaining
}

public function addIndex(string $columnName)
{
    // Add a regular (non-unique) index
    $query = "CREATE INDEX idx_$columnName ON $this->currentTable ($columnName)";
    $this->query($query);
    return $this; // Return $this to enable method chaining
}



    public function dropTable(string $table)
    {
        $query = "DROP TABLE IF EXISTS $table ";
        $this->query($query);

        echo "\n\rTable $table deleted successfully!";
    }

    private function validateData(string $table, array $data)
    {
        // Define validation rules for each table
        $validationRules = [
            'table1' => [
                'requiredFields' => ['field1', 'field2'],
                'uniqueFields' => ['field3'],
                'customValidation' => function ($data) {
                    // Implement custom validation logic specific to table1
                    // Return true if data is valid, false otherwise
                    return true;
                },
            ],
            'table2' => [
                'requiredFields' => ['field4', 'field5'],
                'uniqueFields' => ['field6'],
                'customValidation' => function ($data) {
                    // Implement custom validation logic specific to table2
                    // Return true if data is valid, false otherwise
                    return true;
                },
            ],
            // Add more tables and validation rules as needed
        ];

        // Check if the table exists in the validation rules
        if (!isset($validationRules[$table])) {
            return false; // Table not found in validation rules
        }

        // Validate required fields
        $requiredFields = $validationRules[$table]['requiredFields'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false; // Required field is missing or empty
            }
        }

        // Validate unique fields
        $uniqueFields = $validationRules[$table]['uniqueFields'];
        foreach ($uniqueFields as $field) {
            if ($this->isFieldAlreadyExists($table, $field, $data[$field])) {
                return false; // Unique field already exists in the table
            }
        }

        // Perform custom validation
        $customValidation = $validationRules[$table]['customValidation'];
        if (!$customValidation($data)) {
            return false; // Custom validation failed
        }

        return true; // All validation checks passed
    }

    private function isFieldAlreadyExists(string $table, string $field, $value)
    {
        // Implement a method to check if the value already exists in the specified field of the table
        // Return true if the value exists, false otherwise
        $query = "SELECT COUNT(*) as count FROM $table WHERE $field = :value";
        $params = [':value' => $value];
        $result = $this->query($query, $params);

        return !empty($result) && $result[0]['count'] > 0;
    }
}
