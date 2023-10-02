<?php

declare(strict_types=1);

namespace NattiPress\NattiCore\Database;

use NattiPress\NattiCore\QueryBuilder\NattiQueryBuilder;
use PDO;
use PDOException;

/**
 * Database Class
 */

class Database
{
    public static $query_id = '';
    public int $affected_rows = 0;
    public int $insert_id = 0;
    public $error = '';
    public bool $has_error = false;
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
        $np_vars['DB_NAME'] = DB_NAME;
        $np_vars['DB_USER'] = DB_USER;
        $np_vars['DB_PASSWORD'] = DB_PASSWORD;
        $np_vars['DB_HOST'] = DB_HOST;
        $np_vars['DB_DRIVER'] = DB_DRIVER;

        $np_vars = do_filter('before_db_connect', $np_vars);
        $this->table_exists_db = $np_vars['DB_NAME'];

        $string = "$np_vars[DB_DRIVER]:hostname=$np_vars[DB_HOST];dbname=$np_vars[DB_NAME]";

        try {
            $con = new PDO($string, $np_vars['DB_USER'], $np_vars['DB_PASSWORD']);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {

            np_die("Failed to connect to the database with error ", $e->getMessage());
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
        return new NattiQueryBuilder($this->connection, $table);
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
        $query = do_filter('before_query_query', $query);
        $data = do_filter('before_query_data', $data);

        $this->error = '';
        $this->has_error = false;

        try {
            $stm = $this->connection->prepare($query);

            $result = $stm->execute($data);
            $this->affected_rows = $stm->rowCount();
            $this->insert_id = $this->connection->lastInsertId();

            if ($result) {
                if ($data_type == 'object') {
                    $rows = $stm->fetchAll(PDO::FETCH_OBJ);
                } else {
                    $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            $this->has_error = true;
        }

        $arr = [];
        $arr['query'] = $query;
        $arr['data'] = $data;
        $arr['result'] = $rows ?? [];
        $arr['query_id'] = self::$query_id;
        self::$query_id = '';

        $result = do_filter('after_query', $arr);

        if (is_array($result['result']) && count($result['result']) > 0) {
            return $result['result'];
        }

        return false;
    }


    public function table_exists(string|array $mytables): bool
    {
        global $np_app;
        $this->missing_tables = [];

        if (empty($np_app['tables'])) {

            $this->error = '';
            $this->has_error = false;

            $query = "SELECT TABLE_NAME AS tables FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $this->table_exists_db . "'";

            $res = $this->query($query);
            $result = $np_app['tables'] = $res;
        } else {
            $result = $np_app['tables'];
        }

        if ($result) {
            $all_tables = array_column($result, 'tables');

            if (is_string($mytables))
                $mytables = [$mytables];

            $count = 0;
            foreach ($mytables as $key => $table) {
                if (in_array($table, $all_tables)) {
                    $count++;
                } else {
                    $this->missing_tables[] = $table;
                }
            }

            if ($count == count($mytables))
                return true;
        }

        return false;
    }
}
