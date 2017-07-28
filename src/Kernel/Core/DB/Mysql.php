<?php
namespace Kernel\Core\DB;

use Illuminate\Database\Capsule\Manager;

class Mysql
{
        public static function init($conf, $type)
        {
                $dbManager = new Manager();
                $dbManager->addConnection($conf);
                $dbManager->bootEloquent();
        }
}