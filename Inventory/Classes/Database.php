<?php

class Database
{

    public static function connect($host, $databaseName, $username, $password)
    {
        try {
            $connection = new PDO("mysql:host={$host};dbname={$databaseName}", $username, $password, [PDO::MYSQL_ATTR_LOCAL_INFILE => true]);
            return $connection;
        } catch (PDOException $exception) {
            return null;
        }
    }

    public static function disconnect($connection)
    {
        $connection = null;
    }

    public static function execute($connection, $sql)
    {
        $preparedStatement = $connection->prepare($sql); //
        $preparedStatement->execute();
    }

    public static function create($connection, $tableName, $columnNames, $data)
    {
        $columns = "";
        $values = "";
        if (is_array($columnNames)) {
            $columnNames = array_map(function ($value) {
                return "`$value`";
            }, $columnNames);
            $columns = " (" . implode(", ", $columnNames) . ")";
        }
        if (!is_array($data[0])) {
            $data = array_map(function ($value) use ($connection) {
                return $value != null ? $connection->quote(utf8_encode($value)) : "NULL";
            }, $data);
            $values = "(" . implode(", ", $data) . ")";
        } else {
            $arrayIndex = array();
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = array_map(function ($value) use ($connection) {
                    return $value != null ? $connection->quote(utf8_encode($value)) : "NULL";
                }, $data[$i]);
                array_push($arrayIndex, implode(", ", $data[$i]));
            }
            $values = "(" . implode("), (", $arrayIndex) . ")";
        }
        $sql = "INSERT INTO `$tableName`$columns VALUES $values;";
        self::execute($connection, $sql);
    }

    public static function read($connection, $sql)
    {
        $preparedStatement = $connection->prepare($sql);
        $preparedStatement->execute();
        $result = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function update($connection, $tableName, $targetColumn, $newValue, $referenceColumn = null, $referenceValue = null)
    {
        if ($newValue != null) {
            $newValue = $connection->quote($newValue);
        } else {
            $newValue = "NULL";
        }
        if ($referenceColumn == null) {
            $sql = "UPDATE `$tableName` SET `$targetColumn` = $newValue;";
        } else {
            $reference = $referenceValue;
            // Check if new  value is array
            if (is_array($referenceValue)) {
                // Check if array is associative
                if (!count(array_filter(array_keys($referenceValue), 'is_string')) > 0) {
                    $keys = array_keys($reference[0]);
                    $reference = array_map(function ($value) use ($keys) {
                        return $value[$keys[0]];
                    }, $reference);
                    $reference = "'" . implode("', '", $reference) . "'";
                    $conditions = array_map(function ($condition) use ($keys, $referenceColumn, $referenceValue) {
                        return "WHEN `$referenceColumn` = '" . $condition[$keys[0]] . "' THEN '" . $condition[$keys[1]] . "'";
                    }, $referenceValue);
                    $newValue = "CASE " . implode(" ", $conditions) . " END";
                } else {
                    $reference = "'" . implode("', '", $referenceValue) . "'";
                }
            }

            $sql = "UPDATE `$tableName` SET `$targetColumn` = $newValue WHERE `$tableName`.`$referenceColumn` IN ($reference);";
        }
        self::execute($connection, $sql);
    }

    public static function delete($connection, $tableName, $referenceColumn, $referenceValue)
    {
        $reference = is_array($referenceValue) ? implode("', '", $referenceValue) : $referenceValue;
        $sql = "DELETE FROM `" . $tableName . "` WHERE `" . $referenceColumn . "` IN ('$reference');";
        $preparedStatement = $connection->prepare($sql);
        $preparedStatement->execute();
        return $preparedStatement->rowCount();
    }

    public static function duplicate($connection, $tableName, $referenceColumn, $referenceValue, $incrementColumn, $incrementString)
    {
        $sql = "CREATE TEMPORARY TABLE `temp` SELECT * FROM `{$tableName}` WHERE `{$referenceColumn}` = '{$referenceValue}';
		SELECT @newPK := (SELECT `{$referenceColumn}` FROM `{$tableName}` ORDER BY `{$referenceColumn}` DESC LIMIT 1)+1, @oldValue := (SELECT `{$incrementColumn}` FROM `{$tableName}` WHERE `{$referenceColumn}` = '{$referenceValue}');
                UPDATE `temp` SET `{$referenceColumn}`=@newPK, `{$incrementColumn}`=CONCAT('{$incrementString}', @oldValue) WHERE `{$referenceColumn}` = '{$referenceValue}';
                INSERT INTO `{$tableName}` SELECT * FROM `temp` WHERE `{$referenceColumn}`=@newPK;";
        self::execute($connection, $sql);
    }
}
