<?php
function getDbConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $host   = 'MySQL-8.2';
        $dbname = 'glass_exhibition';
        $user   = 'root';
        $pass   = '';   
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('Ошибка подключения к БД: ' . $e->getMessage());
        }
    }
    return $pdo;
}

function escape($value) { 
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); 
}

function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

session_start();
?>
