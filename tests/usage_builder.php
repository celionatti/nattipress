<?php

$results = $queryBuilder
    ->select(['users.name', 'orders.order_date'])
    ->join('orders', 'users.id = orders.user_id')
    ->where(['users.status' => 'active'])
    ->orderBy('orders.order_date', 'DESC')
    ->get();


$query1 = $queryBuilder->select()->where(['status' => 'active']);
$query2 = $queryBuilder->select()->where(['status' => 'inactive']);

$unionResults = $queryBuilder->union($query1, $query2)->get();


$sql = "SELECT * FROM my_table WHERE column = :value";
$bindValues = [':value' => 'some_value'];

$rawResults = $queryBuilder->rawQuery($sql, $bindValues)->get();

$results = $queryBuilder->select()
    ->from('my_table')
    ->alias('t')
    ->get();


$queryBuilder->truncate()->get();


$distinctResults = $queryBuilder->distinct('column1')->get();


$count = $queryBuilder->count()->where(['status' => 'active'])->get()[0]->count;

$subquery = $queryBuilder->select(['sub_column'])
    ->from('sub_table')
    ->where(['sub_condition' => 'sub_value']);

$results = $queryBuilder->select(['main_column'])
    ->from('main_table')
    ->subquery($subquery, 'sub')
    ->get();


$results = $queryBuilder->select()
    ->from('my_table')
    ->where(['status' => 'active'])
    ->between('created_at', '2023-01-01', '2023-12-31')
    ->get();


$results = $queryBuilder->select(['column1', 'SUM(column2) as total'])
    ->from('my_table')
    ->groupBy('column1')
    ->having(['total' => ['>' => 100]])
    ->get();
