<?php

$results = $queryBuilder
    ->select(['users.name', 'orders.order_date'])
    ->join('orders', 'users.id = orders.user_id')
    ->where(['users.status' => 'active'])
    ->orderBy('orders.order_date', 'DESC')
    ->get();
