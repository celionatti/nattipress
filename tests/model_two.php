<?php

namespace Model;

use NattiPress\NattiCore\QueryBuilder\NattiQueryBuilder;

defined('ROOT') or die("Direct script access denied");

/**
 * Base Model class
 */
class BaseModel
{
    protected $table;
    protected $db;
    protected $queryBuilder;

    public function __construct()
    {
        $this->db = new Database(); // Replace with your database configuration
        $this->queryBuilder = new NattiQueryBuilder($this->db, $this->table);
    }

    // ... other methods ...

    public function insert(array $data): bool
    {
        try {
            $this->db->beginTransaction(); // Start a transaction

            $result = $this->queryBuilder
                ->insert($data) // Use the NattiQueryBuilder's insert method
                ->into($this->table)
                ->execute();

            $this->db->commit(); // Commit the transaction

            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack(); // Rollback the transaction on error

            // Handle the exception, e.g., log or throw an exception
            throw new DatabaseException($e->getMessage());
        }
    }
}
