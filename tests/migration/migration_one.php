<?php

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
    private $foreignKeys = [];
    private $uniqueKeys = [];
    private $fullTextKeys = [];

    public function createTable(string $table)
    {
        // ... Existing createTable method ...

        echo "\n\rTable $table created successfully!";
        return $this;
    }

    public function insert(string $table)
    {
        // ... Existing insert method ...

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
        // ... Existing dropTable method ...

        echo "\n\rTable $table deleted successfully!";
        return $this;
    }

    // Additional methods ...

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

    // You can add more methods for more complex operations as needed
}




// Usage


$migration = new Migration();

$migration
    ->createTable('users')
    ->addColumn('id INT AUTO_INCREMENT PRIMARY KEY')
    ->addColumn('username VARCHAR(255) NOT NULL')
    ->addColumn('email VARCHAR(255) NOT NULL')
    ->addPrimaryKey('id')
    ->insert('users')
    ->addData(['username' => 'user1', 'email' => 'user1@example.com'])
    ->addData(['username' => 'user2', 'email' => 'user2@example.com'])
    ->addForeignKey('orders', 'user_id', 'users', 'id')
    ->renameTable('old_table', 'new_table')
    ->removeColumn('users', 'email');
