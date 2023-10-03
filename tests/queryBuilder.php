<?php

class QueryBuilder {
    protected $table;
    protected $select = '*';
    protected $joins = [];
    protected $where = [];
    protected $orderBy = [];
    protected $groupBy = [];
    protected $limit;
    protected $offset;

    public function table($table) {
        $this->table = $table;
        return $this;
    }

    public function select($columns) {
        $this->select = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    public function join($table, $foreign, $operator, $local) {
        $this->joins[] = "JOIN $table ON $foreign $operator $local";
        return $this;
    }

    public function where($column, $operator, $value) {
        $this->where[] = "$column $operator '$value'";
        return $this;
    }

    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function groupBy($columns) {
        $this->groupBy[] = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function get() {
        $query = "SELECT $this->select FROM $this->table";

        if (!empty($this->joins)) {
            $query .= ' ' . implode(' ', $this->joins);
        }

        if (!empty($this->where)) {
            $query .= ' WHERE ' . implode(' AND ', $this->where);
        }

        if (!empty($this->groupBy)) {
            $query .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }

        if (!empty($this->orderBy)) {
            $query .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if (!empty($this->limit)) {
            $query .= ' LIMIT ' . $this->limit;
        }

        if (!empty($this->offset)) {
            $query .= ' OFFSET ' . $this->offset;
        }

        // Execute the query and return the result
        $result = $this->executeQuery($query);

        return $result;
    }

    protected function executeQuery($query) {
       // Replace these settings with your actual database connection details
       $dbHost = 'your_database_host';
       $dbName = 'your_database_name';
       $dbUser = 'your_database_user';
       $dbPass = 'your_database_password';

       try {
           $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
           $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

           $statement = $pdo->query($query);

           if ($statement) {
               // Fetch results as an associative array
               $result = $statement->fetchAll(PDO::FETCH_ASSOC);
               return $result;
           } else {
               // Handle query execution error
               return "Error executing query: " . $pdo->errorInfo()[2];
           }
       } catch (PDOException $e) {
           // Handle database connection error
           return "Database connection failed: " . $e->getMessage();
       }
    }
}

// Usage example:
$queryResult = (new QueryBuilder())
    ->table('users')
    ->select(['id', 'name'])
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->where('users.active', '=', '1')
    ->groupBy(['users.id', 'users.name'])
    ->orderBy('users.created_at', 'DESC')
    ->limit(10)
    ->offset(0)
    ->get();

echo $queryResult;
