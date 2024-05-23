<?php

namespace App\Lib\Database;

use Dibi\Exception;

class Connection
{

    /**
     * @throws Exception
     */
    public function get(): \Dibi\Connection
    {
        return new \Dibi\Connection([
            'driver'   => getenv('DB_DRIVER'),
            'host'     => getenv('DB_HOST'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'database' => getenv('DB_NAME'),
        ]);
    }

}