<?php


use NattiPress\NattiCore\Database\DatabaseConnection;
use NattiPress\NattiCore\QueryBuilder\NattiQueryBuilder;

class GeneralModel
{
    private $dbConnection;
    private $tableName;

    public function __construct(DatabaseConnection $dbConnection, $tableName)
    {
        $this->dbConnection = $dbConnection;
        $this->tableName = $tableName;
    }

    public function find($id)
    {
        return $this->dbConnection->table($this->tableName)
            ->select()
            ->where(['id' => $id])
            ->get('object');
    }

    public function create(array $data)
    {
        return $this->dbConnection->table($this->tableName)
            ->insert($data);
    }

    public function update($id, array $data)
    {
        return $this->dbConnection->table($this->tableName)
            ->where(['id' => $id])
            ->update($data);
    }

    public function delete($id)
    {
        return $this->dbConnection->table($this->tableName)
            ->where(['id' => $id])
            ->delete();
    }

    public function getAll()
    {
        return $this->dbConnection->table($this->tableName)
            ->select()
            ->get('object');
    }

    // Add more model-specific methods as needed
}
