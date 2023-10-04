<?php

declare(strict_types=1);

namespace NattiThemes\np_admin\models;


use NattiPress\NattiCore\Database\DatabaseModel;


class Users extends DatabaseModel
{
    public static function tableName(): string
    {
        return "users";
    }

    public function allUsers()
    {
        return $this->query("select * from users");
    }
}