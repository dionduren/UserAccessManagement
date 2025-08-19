<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connection;
use PDO;

class DblibServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('db', function ($db) {
            $db->extend('dblib', function ($config, $name) {
                $host = $config['host'] ?? '127.0.0.1';
                $port = $config['port'] ?? 1433;
                $db   = $config['database'] ?? '';
                $charset = $config['charset'] ?? 'UTF-8';

                $dsn = "dblib:host={$host}:{$port};dbname={$db};charset={$charset}";
                $pdo = new PDO($dsn, $config['username'] ?? null, $config['password'] ?? null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                ]);

                return new Connection($pdo);
            });
        });
    }
}
