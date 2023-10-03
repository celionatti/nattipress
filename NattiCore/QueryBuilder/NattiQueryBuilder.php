<?php

declare(strict_types=1);

namespace NattiPress\NattiCore\QueryBuilder;

use PDO;
use PDOException;
use NattiPress\NattiCore\Database\DatabaseException;

/**
 * Natti Query Builder.
 */

class NattiQueryBuilder
{
    private $connection;
    private $table;
    private $query;
    private $bindValues = [];
    private $joinClauses = [];
    private $currentStep = 'initial';

    public function __construct($connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function select($columns = '*')
    {
        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. SELECT should come first.');
        }

        if (!is_array($columns) || empty($columns)) {
            throw new \InvalidArgumentException('Invalid argument for SELECT method.');
        }

        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        $this->query = "SELECT $columns FROM $this->table";
        $this->currentStep = 'select';

        return $this;
    }

    public function insert(array $data)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Invalid argument for INSERT method. Data array must not be empty.');
        }

        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. INSERT should come before other query building methods.');
        }

        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        $this->query = "INSERT INTO $this->table ($columns) VALUES ($values)";
        $this->bindValues = $data;
        $this->currentStep = 'insert';

        return $this;
    }

    public function where(array $conditions)
    {
        if ($this->currentStep !== 'select' && $this->currentStep !== 'where') {
            throw new \Exception('Invalid method order. WHERE should come after SELECT or a previous WHERE.');
        }

        if (empty($conditions)) {
            throw new \InvalidArgumentException('Invalid argument for WHERE method. Conditions array must not be empty.');
        }

        $where = [];
        foreach ($conditions as $column => $value) {
            if (!is_string($column) || empty($column)) {
                throw new \InvalidArgumentException('Invalid argument for WHERE method. Column names must be non-empty strings.');
            }

            $where[] = "$column = :$column";
            $this->bindValues[":$column"] = $value;
        }

        $this->query .= " WHERE " . implode(' AND ', $where);
        $this->currentStep = 'where';

        return $this;
    }


    public function orderBy($column, $direction = 'ASC')
    {
        if ($this->currentStep !== 'select' && $this->currentStep !== 'where') {
            throw new \Exception('Invalid method order. ORDER BY should come after SELECT, WHERE, or a previous ORDER BY.');
        }

        if (!is_string($column) || empty($column)) {
            throw new \InvalidArgumentException('Invalid argument for ORDER BY method. Column name must be a non-empty string.');
        }

        $this->query .= " ORDER BY $column $direction";
        $this->currentStep = 'order';

        return $this;
    }

    public function groupBy($column)
    {
        if ($this->currentStep !== 'select' && $this->currentStep !== 'where' && $this->currentStep !== 'order') {
            throw new \Exception('Invalid method order. GROUP BY should come after SELECT, WHERE, ORDER BY, or a previous GROUP BY.');
        }

        if (!is_string($column) || empty($column)) {
            throw new \InvalidArgumentException('Invalid argument for GROUP BY method. Column name must be a non-empty string.');
        }

        $this->query .= "GROUP BY $column";
        $this->currentStep = 'group';

        return $this;
    }

    public function limit($limit)
    {
        if ($this->currentStep !== 'select' && $this->currentStep !== 'where' && $this->currentStep !== 'order' && $this->currentStep !== 'group') {
            throw new \Exception('Invalid method order. LIMIT should come after SELECT, WHERE, ORDER BY, GROUP BY, or a previous LIMIT.');
        }

        if (!is_numeric($limit) || $limit < 1) {
            throw new \InvalidArgumentException('Invalid argument for LIMIT method. Limit must be a positive numeric value.');
        }

        $this->query .= " LIMIT $limit";
        $this->currentStep = 'limit';

        return $this;
    }

    public function join($table, $onClause, $type = 'INNER')
    {
        if ($this->currentStep !== 'select' && $this->currentStep !== 'where' && $this->currentStep !== 'order' && $this->currentStep !== 'group') {
            throw new \Exception('Invalid method order. JOIN should come after SELECT, WHERE, ORDER BY, GROUP BY, or a previous JOIN.');
        }

        if (!is_string($table) || empty($table)) {
            throw new \InvalidArgumentException('Invalid argument for JOIN method. Table name must be a non-empty string.');
        }

        if (!is_string($onClause) || empty($onClause)) {
            throw new \InvalidArgumentException('Invalid argument for JOIN method. ON clause must be a non-empty string.');
        }

        if ($type !== 'INNER' && $type !== 'LEFT' && $type !== 'RIGHT' && $type !== 'OUTER') {
            throw new \InvalidArgumentException('Invalid argument for JOIN method. Invalid join type.');
        }

        if (!is_string($table) || !is_string($onClause)) {
            throw new \InvalidArgumentException('Invalid arguments for JOIN method.');
        }

        $this->joinClauses[] = "$type JOIN $table ON $onClause";
        return $this;
    }

    public function leftJoin($table, $onClause)
    {
        return $this->join($table, $onClause, 'LEFT');
    }

    public function rightJoin($table, $onClause)
    {
        return $this->join($table, $onClause, 'RIGHT');
    }

    public function outerJoin($table, $onClause)
    {
        return $this->join($table, $onClause, 'OUTER');
    }

    public function get($data_type = 'assoc')
    {
        try {
            $this->query = $this->query . ' ' . implode(' ', $this->joinClauses);
            $stm = $this->connection->prepare($this->query);

            foreach ($this->bindValues as $param => $value) {
                $stm->bindValue($param, $value);
            }

            $stm->execute();

            if ($data_type === 'object') {
                return $stm->fetchAll(PDO::FETCH_OBJ);
            } else {
                return $stm->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            // Handle database error, e.g., log or throw an exception
            throw new DatabaseException($e->getMessage());
        }
    }
}
