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

            if ($_POST['read'] == "checkExistingCategory") {

                $categoryName = $_POST["data"]["categoryName"];

                $result = Database::read($connection, "SELECT `category_id` FROM `$app_product_category` WHERE `category_name` = '$categoryName'");

                $message;

                if (!count($result)) {
                    $message = true;
                    Database::create($connection, $app_product_category, ["category_name"], [$categoryName]);
                } else {
                    $message = false;
                }

                echo json_encode($message);
            }

            if ($_POST['read'] == "getCategories") {

                $categories = Database::read($connection, "SELECT `category_id`, `category_name` FROM `$app_product_category`;");

                echo json_encode($categories);
            }

            if ($_POST['read'] == "checkStocks") {

                $quantityThreshold = 10;
                $lowStocks = Database::read($connection, "SELECT `product_name` FROM `$app_product` WHERE `product_stock` BETWEEN 1 AND $quantityThreshold ORDER BY `$app_product`.`product_name` ASC;");
                $noStocks = Database::read($connection, "SELECT `product_name` FROM `$app_product` WHERE `product_stock` <= 0 ORDER BY `$app_product`.`product_name` ASC;");

                $lowHash = md5(serialize($lowStocks));
                $noHash = md5(serialize($noStocks));
                echo json_encode([
                    [
                        "hash" => $lowHash,
                        "count" => count($lowStocks),
                        "result" => $lowStocks,
                    ],
                    [
                        "hash" => $noHash,
                        "count" => count($noStocks),
                        "result" => $noStocks,
                    ]
                ]);
            }
        }
        Database::disconnect($connection);
    } else {
        echo '<h1>Connection failed!</h1>';
    }
} else {
    header("Location: ..");
}
