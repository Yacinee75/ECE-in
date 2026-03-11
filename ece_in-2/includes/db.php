<?php
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dbName = getenv('DB_NAME') ?: 'ece_in';
    $dbHost = getenv('DB_HOST');
    $dbPort = getenv('DB_PORT');
    $dbUser = getenv('DB_USER');
    $dbPass = getenv('DB_PASS');

    $configs = [];

    if ($dbHost !== false && $dbHost !== null && $dbHost !== '') {
        $configs[] = [
            'host' => $dbHost,
            'port' => ($dbPort !== false && $dbPort !== null && $dbPort !== '') ? $dbPort : '3306',
            'user' => ($dbUser !== false && $dbUser !== null && $dbUser !== '') ? $dbUser : 'root',
            'pass' => ($dbPass !== false && $dbPass !== null) ? $dbPass : '',
        ];
    }

    // Common local dev defaults (MAMP then standard MySQL)
    $configs[] = ['host' => '127.0.0.1', 'port' => '8889', 'user' => 'root', 'pass' => 'root'];
    $configs[] = ['host' => '127.0.0.1', 'port' => '3306', 'user' => 'root', 'pass' => ''];

    $lastException = null;

    foreach ($configs as $config) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $dbName
        );

        try {
            $pdo = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            return $pdo;
        } catch (PDOException $e) {
            $lastException = $e;
        }
    }

    throw new RuntimeException(
        'Impossible de se connecter à la base de données ece_in. '
        . 'Vérifie DB_HOST, DB_PORT, DB_USER, DB_PASS et que MySQL est démarré.',
        0,
        $lastException
    );
}
