<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectionFactory;
use PDO;

class DblibServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->resolving('db', function ($db) {
            $db->extend('dblib', function ($config, $name) {
                $dsn = "dblib:host={$config['host']}:{$config['port']};dbname={$config['database']}";
                $options = $config['options'] ?? [];
                return new Connection(new PDO($dsn, $config['username'], $config['password'], $options));
            });
        });
    }
}
