<?php

if ($_POST || $_FILES) {
    session_status() == PHP_SESSION_NONE ? session_start() : false;

    require_once 'global.php';
    require_once '../Imports/phpDefaults.php';
    require_once 'Database.php';
    require_once 'Tools.php';

    $connection = Database::Connect($Host, $DatabaseName, $Username, $Password);

    if ($connection) {
        if (isset($_POST['read'])) {
            if ($_POST['read'] == "changeUserPassword") {
                $userId = ($_POST["data"]["userType"] == "admin" ? 1 : 2);
                $result = Database::read($connection, "SELECT `password` FROM `$app_user` WHERE `user_id` = $userId;");
                $password = $result[0]["password"];

                $updateResult = null;

                if (Tools::verifyPassword($_POST["data"]["oldPassword"], $password)) {
                    if ($_POST["data"]["oldPassword"] !== $_POST["data"]["newPassword"]) {
                        $newPassword = Tools::hashPassword($_POST["data"]["newPassword"]);
                        Database::update($connection, $app_user, "password", $newPassword, "user_id", $userId);
                        $updateResult = "success";
                    } else {
                        $updateResult = "error";
                    }
                } else {
                    $updateResult = "fail";
                }

                echo json_encode($updateResult);
            }
        }
        Database::disconnect($connection);
    } else {
        echo '<h1>Connection failed!</h1>';
    }
} else {
    header("Location: ..");
}
