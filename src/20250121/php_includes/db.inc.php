<?php
require __DIR__ . '/../env.php';

function connectToDatabase($forceReConnect = false): PDO
{
    static $db; // persistent across function calls

    if ($forceReConnect || !$db) {
        try {
            $db_host = DB_HOST;
            $db_port = DB_PORT;
            $db_user = DB_USER;
            $db_password = DB_PASS;
            $db_db = DB_DATABASE;

            $db = new PDO(
                "mysql:host=$db_host; port=$db_port; dbname=$db_db",
                $db_user,
                $db_password
            );
        } catch (PDOException $e) {
            echo "Error!: " . $e->getMessage() . "<br />";
            die();
        }
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
    }

    return $db;
}

function getImages(): array
{
    $sql = "SELECT * FROM images";

    $stmt = connectToDatabase()->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insertImage(string $name, string $path): int|false
{
    $db = connectToDatabase();
    $sql = "INSERT INTO images(name, path) VALUES (:name, :path)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'path' => $path,
    ]);

    return $db->lastInsertId();
}
