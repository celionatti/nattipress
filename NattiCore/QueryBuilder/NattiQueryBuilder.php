<?php

declare(strict_types=1);

namespace NattiPress\NattiCore\QueryBuilder;

use NattiPress\NattiCore\Database\Database;
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
    public static $query_id = '';

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

        if (!is_array($columns) && !is_string($columns)) {
            throw new \InvalidArgumentException('Invalid argument for SELECT method. Columns must be an array or a comma-separated string.');
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

    public function update(array $data)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Invalid argument for UPDATE method. Data array must not be empty.');
        }

        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. UPDATE should come before other query building methods.');
        }

        $set = [];
        foreach ($data as $column => $value) {
            if (!is_string($column) || empty($column)) {
                throw new \InvalidArgumentException('Invalid argument for UPDATE method. Column names must be non-empty strings.');
            }

            $set[] = "$column = :$column";
            $this->bindValues[":$column"] = $value;
        }

        $this->query = "UPDATE $this->table SET " . implode(', ', $set);
        $this->currentStep = 'update';

        return $this;
    }

    public function delete()
    {
        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. DELETE should come before other query building methods.');
        }

        $this->query = "DELETE FROM $this->table";
        $this->currentStep = 'delete';

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

    public function count()
    {
        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. COUNT should come before other query building methods.');
        }

        $this->query = "SELECT COUNT(*) AS count FROM $this->table";
        $this->currentStep = 'count';

        return $this;
    }

    public function distinct($columns = '*')
    {
        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. DISTINCT should come before other query building methods.');
        }

        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $columns = implode(', ', $columns);
        $this->query = "SELECT DISTINCT $columns FROM $this->table";
        $this->currentStep = 'distinct';

        return $this;
    }

    public function truncate()
    {
        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. TRUNCATE should come before other query building methods.');
        }

        $this->query = "TRUNCATE TABLE $this->table";
        $this->currentStep = 'truncate';

        return $this;
    }

    // public function union(NattiQueryBuilder ...$queries)
    // {
    //     if ($this->currentStep !== 'initial') {
    //         throw new \Exception('Invalid method order. UNION should come before other query building methods.');
    //     }

    //     $queryStrings = [$this->query];
    //     foreach ($queries as $query) {
    //         $queryStrings[] = $query->getQuery();
    //     }

    //     $this->query = implode(' UNION ', $queryStrings);
    //     $this->currentStep = 'union';

    //     return $this;
    // }

    public function union(NattiQueryBuilder ...$queries)
    {
        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. UNION should come before other query building methods.');
        }

        // Store the current query and reset it
        $currentQuery = $this->query;
        $this->query = '';

        $queryStrings = [$currentQuery];
        foreach ($queries as $query) {
            $queryStrings[] = $query->query; // Assuming your query property is called "query"
        }

        $this->query = implode(' UNION ', $queryStrings);
        $this->currentStep = 'union';

        return $this;
    }


    public function rawQuery(string $sql, array $bindValues = [])
    {
        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. Raw query should come before other query building methods.');
        }

        $this->query = $sql;
        $this->bindValues = $bindValues;
        $this->currentStep = 'raw';

        return $this;
    }

    public function alias(string $alias)
    {
        if ($this->currentStep === 'initial') {
            throw new \Exception('Invalid method order. Alias should come after other query building methods.');
        }

        $this->query .= " AS $alias";

        return $this;
    }

    public function subquery(NattiQueryBuilder $subquery, string $alias)
    {
        if ($this->currentStep === 'initial') {
            throw new \Exception('Invalid method order. Subquery should come after other query building methods.');
        }

        $this->query .= " ($subquery) AS $alias";

        return $this;
    }

    public function between(string $column, $value1, $value2)
    {
        if ($this->currentStep !== 'select' && $this->currentStep !== 'where') {
            throw new \Exception('Invalid method order. BETWEEN should come after SELECT, WHERE, or a previous BETWEEN.');
        }

        $this->query .= " AND $column BETWEEN :value1 AND :value2";
        $this->bindValues[':value1'] = $value1;
        $this->bindValues[':value2'] = $value2;

        $this->currentStep = 'between';

        return $this;
    }

    public function having(array $conditions)
    {
        if ($this->currentStep !== 'group') {
            throw new \Exception('Invalid method order. HAVING should come after GROUP BY.');
        }

        $having = [];
        foreach ($conditions as $column => $value) {
            $having[] = "$column = :$column";
            $this->bindValues[":$column"] = $value;
        }

        $this->query .= " HAVING " . implode(' AND ', $having);

        return $this;
    }


    // public function get($data_type = 'object')
    // {
    //     try {
    //         $this->query = $this->query . '' . implode(' ', $this->joinClauses);
    //         $stm = $this->connection->prepare($this->query);

    //         foreach ($this->bindValues as $param => $value) {
    //             $stm->bindValue($param, $value);
    //         }

    //         $stm->execute();

    //         if ($data_type === 'object') {
    //             return $stm->fetchAll(PDO::FETCH_OBJ);
    //         } elseif ($data_type === 'assoc') {
    //             return $stm->fetchAll(PDO::FETCH_ASSOC);
    //         } else {
    //             return $stm->fetchAll(PDO::FETCH_CLASS);
    //         }
    //     } catch (PDOException $e) {
    //         // Handle database error, e.g., log or throw an exception
    //         throw new DatabaseException($e->getMessage());
    //     }
    // }

    public function get($data_type = 'object')
    {
        try {
            $this->connection->beginTransaction();
            $this->query = $this->query . '' . implode(' ', $this->joinClauses);
            $stm = $this->connection->prepare($this->query);

            foreach ($this->bindValues as $param => $value) {
                $stm->bindValue($param, $value);
            }

            $stm->execute();

            if ($data_type === 'object') {
                return $stm->fetchAll(PDO::FETCH_OBJ);
            } elseif ($data_type === 'assoc') {
                return $stm->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return $stm->fetchAll(PDO::FETCH_CLASS);
            }
            // Commit the transaction if the query was successful
            $this->connection->commitTransaction();
        } catch (PDOException $e) {
            // Rollback the transaction on error
            $this->connection->rollbackTransaction();
            // Handle database error, e.g., log or throw an exception
            throw new DatabaseException($e->getMessage());
        }
    }

    public function get_query($data_type = 'object')
    {
        $this->query = do_filter('before_query_query', $this->query);
        $this->bindValues = do_filter('before_query_data', $this->bindValues);

        $this->connection->error = '';
        $this->connection->has_error = false;

        try {
            $this->connection->beginTransaction();
            $this->query = $this->query . '' . implode(' ', $this->joinClauses);
            $stm = $this->connection->prepare($this->query);

            foreach ($this->bindValues as $param => $value) {
                $stm->bindValue($param, $value);
            }

            $result = $stm->execute();

            $this->connection->affected_rows = $stm->rowCount();
            $this->connection->insert_id = $this->connection->lastInsertId();

            if ($result) {
                if ($data_type === 'object') {
                    $rows = $stm->fetchAll(PDO::FETCH_OBJ);
                } elseif ($data_type === 'assoc') {
                    $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $rows = $stm->fetchAll(PDO::FETCH_CLASS);
                }
            }
            // Commit the transaction if the query was successful
            $this->connection->commit();
        } catch (PDOException $e) {
            // Rollback the transaction on error
            $this->connection->rollback();
            $this->connection->error = $e->getMessage();
            $this->connection->has_error = true;
        }

        $arr = [];
        $arr['query'] = $this->query;
        $arr['data'] = $this->bindValues;
        $arr['result'] = $rows ?? [];
        $arr['query_id'] = self::$query_id;
        self::$query_id = '';

        $result = do_filter('after_query', $arr);

        if (is_array($result['result']) && count($result['result']) > 0) {
            return $result['result'];
        }

        return false;
    }

    public function executeQuery()
    {
        try {
            $this->query = $this->query . '' . implode(' ', $this->joinClauses);
            $result = $this->connection->query($this->query, $this->bindValues);
            return $result;
        } catch (PDOException $e) {
            // Handle database error, e.g., log or throw an exception
            throw new DatabaseException($e->getMessage());
        }
    }
}
