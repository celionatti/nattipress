<?php

declare(strict_types=1);

namespace NattiPress\NattiCore\Database;

use NattiPress\NattiCore\QueryBuilder\NattiQueryBuilder;

/**
 * Database Model Class
 */

class DatabaseModel
{
    protected $tableName;
    protected $db;
    protected $queryBuilder;

    public function __construct()
    {
        $this->db = new Database();
        $this->queryBuilder = new NattiQueryBuilder($this->db, $this->tableName);
    }

    public function find()
    {

    }

    public function findById($column = "*", $id)
    {
        return $this->queryBuilder
            ->select($column)
            ->where(['id' => $id])
            ->get('object');
    }
}