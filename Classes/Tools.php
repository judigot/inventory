<?php

class Tools
{

    private static $helloWorld = "Hello, world!";

    public static function helloWorld()
    {
        echo self::$helloWorld;
    }

    public static function getCurrentWeekNumber()
    {
        return date("l") === "Sunday" ? date("W") + 1 : date("W");
    }

    public static function addLeadingZero($number)
    {
        return $number < 10 ? "0" . $number : $number;
    }

    public static function getWeekDates($year, $weekNumber)
    {
        $sanitized = self::addLeadingZero($weekNumber);
        $currentWeekDates = array();
        for ($i = 0; $i < 7; $i++) {
            $currentWeekDates[] = date("Y-m-d", strtotime("$year-W$sanitized-0 + $i days"));
        }
        return $currentWeekDates;
    }

    public static function getCurrentWeekDates($givenTime = false)
    {
        return self::getWeekDates(date("Y"), self::getCurrentWeekNumber());
    }

    public static function verifyUser($filePath)
    {
        $jsonPath = (file_exists($filePath)) ? $filePath : $filePath;
        $json = json_decode(file_get_contents($jsonPath), true);
        $licenseKey = self::getMACAddress();
        $isVerified = null;
        if (array_key_exists("licenseKey", $json)) {
            if (is_array($json["licenseKey"])) {
                for ($i = 0; $i < count($json["licenseKey"]); $i++) {
                    if (self::verifyPassword($licenseKey, $json["licenseKey"][$i])) {
                        $isVerified = true;
                        break;
                    } else {
                        $isVerified = false;
                    }
                }
            } else {
                $isVerified = self::verifyPassword($licenseKey, $json["licenseKey"]);
            }
        } else {
            $json["licenseKey"] = self::hashPassword($licenseKey);
            $fp = fopen($filePath, "wb");
            fwrite($fp, json_encode($json));
            $isVerified = true;
        }
        return $isVerified;
    }

    public static function verifyUserRemote($filePath)
    {
        $filePath = "https://raw.githubusercontent.com/judigot/main/master/data.json";
        $jsonPath = (file_exists($filePath)) ? $filePath : $filePath;
        $json = json_decode(file_get_contents($jsonPath), true);
        $allowed = $json["users"]["igot"];
        return in_array(self::getMACAddress(), $allowed);
    }

    public static function buildTree($connection, $source)
    {
        /*
        $result = buildTree($connection, 1);
        $result = buildTree($connection, ["parent" => 1, "generations" => 5]);
         */
        $tree = [];
        $initial = func_num_args() === 2 ? true : false;
        $generation = $initial ? 2 : func_get_args()[2] + 1;
        $branch = null;
        if (is_array($source)) {
            if (($generation - 1) < $source["generations"] + 1) {
                $branch = Database::read($connection, "SELECT * FROM `app_person` WHERE `mother` = '{$source["parent"]}' OR `father` = '{$source["parent"]}'");
            }
        } else {
            $branch = Database::read($connection, "SELECT * FROM `app_person` WHERE `mother` = '$source' OR `father` = '$source'");
        }
        if ($branch) {
            foreach ($branch as $leaf) {
                $tree[] = array(
                    "id" => $leaf["person_id"],
                    "name" => $leaf["first_name"],
                    "mother" => $leaf["mother"],
                    "father" => $leaf["father"],
                    "generation" => $generation,
                    "children" => self::buildTree($connection, is_array($source) ? ["parent" => $leaf["person_id"], "generations" => $source["generations"]] : $leaf["person_id"], $generation),
                );
            }
        }
        if ($initial) {
            $divider = count($tree[0]) + 1; // Total attributes (e.g: id, name, etc.) + 1
            return $tree ? array(
                "total" => (sizeof($tree, true) / $divider),
                "tree" => $tree
            ) : false;
        } else {
            return $tree ? $tree : false;
        }
    }

    public static function getMACAddress()
    {
        ob_start();
        system('getmac');
        $MACAddress = ob_get_contents();
        ob_clean();
        return substr($MACAddress, strpos($MACAddress, '\\') - 20, 17);
    }

    public static function monetize($addComma, $number)
    {
        //        $number = bcdiv($number, 1, 2);
        return $addComma ? number_format($number, strlen(substr(strrchr($number, "."), 1))) : $number;
    }

    public static function login($Connection, $userTable, $accountName, $accountPassword)
    {
        require_once 'Database.php';
        $Result = Database::read($Connection, "SELECT * FROM $userTable WHERE `email` = '$accountName'");
        if (!empty($Result)) {
            $Data = array();
            if (self::verifyPassword($accountPassword, $Result[0]["password"])) {
                $Data[] = 0;
            } else {
                $Data[] = 1;
            }
            array_push($Data, $Result);
        } else {
            $Data[] = "null";
        }
        return $Data;
    }

    public static function log($content)
    {
        $outputLocation = "C:/Users/" . getenv("USERNAME") . "/Desktop/";
        $fp = fopen($outputLocation . "Log.txt", "wb");
        fwrite($fp, $content);
    }

    public static function logLegacy($content, $outputLocation)
    {
        if ($outputLocation != null && file_exists($outputLocation)) {
            $outputLocation = str_replace(chr(92), "/", $outputLocation);
            if (substr($outputLocation, -1) != "/") {
                $outputLocation .= "/";
            }
        } else if ($outputLocation == "current") {
            $outputLocation = "";
        } else {
            $outputLocation = "C:/Users/" . getenv("USERNAME") . "/Desktop/";
        }
        $fp = fopen($outputLocation . "Log.txt", "wb");
        fwrite($fp, $content);
    }

    public static function utf8Encode($string)
    {
        return preg_match('!!u', $string) ? $string : utf8_encode($string);
    }

    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function resetRowCount($Data, $Index)
    {
        for ($i = 0; $i < sizeof($Data); $i++) {
            $Data[$i][array_keys($Data[0])[$Index]] = $i + 1;
        }
        return $Data;
    }

    public static function downloadCSV($fileName, $Data, $rowCount, $includeColumnNames)
    {
        if ($rowCount) {
            $Data = self::resetRowCount($Data, 0);
        }
        if ($includeColumnNames) {
            // Insert Column Names into the Array ($Data)
            array_unshift($Data, array_keys($Data[0]));
        }
        self::createFile(true, $Data, null, $fileName ? $fileName : "Untitled", "csv");
    }

    public static function exportCSV($fileName, $Data, $rowCount, $includeColumnNames)
    {
        if ($rowCount) {
            $Data = self::resetRowCount($Data, 0);
        }
        if ($includeColumnNames) {
            // Insert Column Names into the Array ($Data)
            array_unshift($Data, array_keys($Data[0]));
        }
        $Path = getenv("HOMEDRIVE") . getenv("HOMEPATH") . "/Desktop/";
        self::createFile(false, $Data, $Path, $fileName ? $fileName : "Untitled", "csv");
    }

    public static function createFile($isDownloadable, $Content, $Location, $FileName, $FileType)
    {
        $FileType = strtolower($FileType);
        if ($isDownloadable == true) {
            header("Content-type: application/$FileType");
            header("Content-disposition: attachment; filename=$FileName.$FileType");
            $File = fopen('php://output', 'w');
        } else {
            $File = fopen("$Location\\$FileName.$FileType", 'w');
        }

        switch ($FileType) {
            case "csv":
                foreach ($Content as $Value) {
                    //MySQL Data to CSV
                    fputcsv($File, $Value);
                    //Array Data to CSV
                    //fputcsv($File,explode(',',$Value));
                }
                break;
        }
        fclose($File);
    }
}
