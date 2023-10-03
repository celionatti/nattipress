<?php

class Database
{
    public static $query_id = '';
    public $affected_rows = 0;
    public $insert_id = 0;
    public $error = '';
    public $has_error = false;
    public $table_exists_db = '';
    public $missing_tables = [];

    private $connection;
    private $transactionLevel = 0;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $VARS['DB_NAME'] = DB_NAME;
        $VARS['DB_USER'] = DB_USER;
        $VARS['DB_PASSWORD'] = DB_PASSWORD;
        $VARS['DB_HOST'] = DB_HOST;
        $VARS['DB_DRIVER'] = DB_DRIVER;

        $VARS = do_filter('before_db_connect', $VARS);
        $this->table_exists_db = $VARS['DB_NAME'];

        $string = "$VARS[DB_DRIVER]:hostname=$VARS[DB_HOST];dbname=$VARS[DB_NAME]";

        try {
            $con = new PDO($string, $VARS['DB_USER'], $VARS['DB_PASSWORD']);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {

            die("Failed to connect to the database with error " . $e->getMessage());
        }

        $this->connection = $con;
    }

    public function beginTransaction()
    {
        if ($this->transactionLevel === 0) {
            try {
                $this->connection->beginTransaction();
            } catch (PDOException $e) {
                $this->handleDatabaseError($e->getMessage());
            }
        }
        $this->transactionLevel++;
    }

    public function commitTransaction()
    {
        if ($this->transactionLevel === 1) {
            try {
                $this->connection->commit();
            } catch (PDOException $e) {
                $this->handleDatabaseError($e->getMessage());
            }
        }
        $this->transactionLevel = max(0, $this->transactionLevel - 1);
    }

    public function rollbackTransaction()
    {
        if ($this->transactionLevel === 1) {
            try {
                $this->connection->rollBack();
            } catch (PDOException $e) {
                $this->handleDatabaseError($e->getMessage());
            }
        }
        $this->transactionLevel = max(0, $this->transactionLevel - 1);
    }

    public function queryBuilder($table)
    {
        return new QueryBuilder($this->connection, $table);
    }

    private function handleDatabaseError($errorMessage)
    {
        $this->error = $errorMessage;
        $this->has_error = true;

        // Example: Log error to a file
        error_log("Database Error: $errorMessage");

        // You can also throw an exception if desired
        throw new DatabaseException($errorMessage);
    }

    public function get_row(string $query, array $data = [], string $data_type = 'object')
    {
        $result = $this->query($query, $data, $data_type);
        if (is_array($result) && count($result) > 0) {
            return $result[0];
        }

        return false;
    }

    public function query(string $query, array $data = [], string $data_type = 'object')
    {
        // ... Existing query method ...

        // Rest of the code...
    }

    public function table_exists(string|array $mytables): bool
    {
        // ... Existing table_exists method ...

        // Rest of the code...
    }
}

class QueryBuilder
{
    private $connection;
    private $table;

    public function __construct($connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    // Implement query building methods here
}
