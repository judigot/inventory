<?php

function connect($host, $databaseName, $username, $password) {
    try {
        $connection = new PDO("mysql:host={$host};dbname={$databaseName}", $username, $password, [PDO::MYSQL_ATTR_LOCAL_INFILE => true]);
        return $connection;
    } catch (PDOException $exception) {
        return null;
    }
}

function disconnect($connection) {
    $connection = null;
}

function read($connection, $sql) {
    $preparedStatement = $connection->prepare($sql);
    $preparedStatement->execute();
    $result = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}
