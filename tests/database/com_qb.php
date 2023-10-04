<?php


class QueryBuilder
{
    private $connection;
    private $table;
    private $query;
    private $bindValues = [];

    public function __construct($connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function select($columns = '*')
    {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        $this->query = "SELECT $columns FROM $this->table";
        return $this;
    }

    public function where(array $conditions)
    {
        $where = [];
        foreach ($conditions as $column => $value) {
            $where[] = "$column = :$column";
            $this->bindValues[":$column"] = $value;
        }

        $this->query .= " WHERE " . implode(' AND ', $where);
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->query .= " ORDER BY $column $direction";
        return $this;
    }

    public function limit($limit)
    {
        $this->query .= " LIMIT $limit";
        return $this;
    }

    public function get()
    {
        try {
            $stm = $this->connection->prepare($this->query);

            foreach ($this->bindValues as $param => $value) {
                $stm->bindValue($param, $value);
            }

            $stm->execute();
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle database error, e.g., log or throw an exception
            throw new DatabaseException($e->getMessage());
        }
    }
}
