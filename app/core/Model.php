<?php

defined('BASE_PATH') || exit('Acesso direto não permitido.');

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }
}
