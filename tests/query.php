<?php

class QueryBuilder {
    // ... Other methods and properties ...

    protected function executeQuery($query, $params = []) {
        // Replace these settings with your actual database connection details
        $dbHost = 'your_database_host';
        $dbName = 'your_database_name';
        $dbUser = 'your_database_user';
        $dbPass = 'your_database_password';

        $pdo = null;
        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Begin a transaction
            $pdo->beginTransaction();

            // Prepare the SQL statement with placeholders
            $statement = $pdo->prepare($query);

            // Bind parameters if provided
            foreach ($params as $param => $value) {
                $statement->bindValue($param, $value);
            }

            // Execute the prepared statement
            $success = $statement->execute();

            if ($success) {
                // Commit the transaction
                $pdo->commit();

                // Fetch results as an associative array
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } else {
                // Rollback the transaction in case of an error
                $pdo->rollBack();

                // Handle query execution error
                return "Error executing query: " . $pdo->errorInfo()[2];
            }
        } catch (PDOException $e) {
            if ($pdo) {
                // Rollback the transaction on any exception
                $pdo->rollBack();
            }

            // Handle database connection or query preparation error
            return "Database error: " . $e->getMessage();
        }
    }
}
