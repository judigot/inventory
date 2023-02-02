<?php

if ($_POST || $_FILES) {
    session_status() == PHP_SESSION_NONE ? session_start() : false;

    require_once 'global.php';
    require_once '../Imports/phpDefaults.php';
    require_once 'Database.php';
    require_once 'Tools.php';

    $connection = Database::Connect($Host, $DatabaseName, $Username, $Password);

    if ($connection) {
        if (isset($_POST['create'])) {
            if ($_POST['create'] == "insertUser") {
                $Data = array();
                $result = Database::read($connection, "SELECT COUNT(*) FROM `$app_user` WHERE `email` = '{$_POST["data"]["email"]}'");
                if ($result[0]["COUNT(*)"] == "0") {
                    $lastName = $_POST["data"]["lastName"];
                    $columns = array("first_name", "last_name", "email", "password", "birthdate", "gender", "address", "user_type");
                    $data = array(ucwords($_POST["data"]["firstName"]), ucwords($_POST["data"]["lastName"]), $_POST["data"]["email"], Tools::hashPassword($_POST["data"]["password"]), null, null, null, "standard");
                    Database::create($connection, $app_user, $columns, $data);
                    $_SESSION["username"] = $_POST["data"]["email"];
                    array_push($Data, 0);
                } else if ($result[0]["COUNT(*)"] == "1") {
                    array_push($Data, 1);
                }
                echo json_encode($Data);
            }

            if ($_POST['create'] == "insertOrder") {
                // Insert to main orders table
                $customerId = $_POST["data"]["customer"];
                $totalOrderCost = 0;
                $column = array("customer_id");
                $Data = array($customerId);
                Database::create($connection, $app_order, $column, $Data);

                // Insert to junction table
                // $result = Database::read($connection, "SELECT LAST_INSERT_ID() FROM `$app_order` WHERE `customer_id` = '$customerId'");
                // $orderId = $result[0]["LAST_INSERT_ID()"];
                $result = Database::read($connection, "SELECT `order_id` FROM `app_order` WHERE `customer_id` = $customerId ORDER BY `order_id` DESC LIMIT 1;");
                $orderId = $result[0]["order_id"];
                $order = json_decode($_POST["data"]["order"], true);
                $customerPrices = json_decode($_POST["data"]["customerPrices"], true);
                $products = [];
                $column = array("order_id", "product_id", "quantity", "product_cost", "product_price", "discount");
                $sql = "";

                // Update stock
                for ($i = 0; $i < count($order); $i++) {
                    $price = $appSettings["customPrice"] ? $customerPrices[$order[$i]["size"] . "_price"] : $order[$i]["price"];
                    $discount = $order[$i]["discount"] ? $order[$i]["discount"] : "0";
                    $sql .= "UPDATE `$app_product` SET `product_stock` = (`product_stock` - {$order[$i]["quantity"]}) WHERE `$app_product`.`product_id` = {$order[$i]["productId"]};";
                    array_push($products, array($orderId, $order[$i]["productId"], $order[$i]["quantity"], $order[$i]["cost"], $order[$i]["price"], $discount));
                }
                Database::create($connection, $app_order_product, $column, $products);
                Database::execute($connection, $sql);
            }

            if ($_POST['create'] == "insertProduct") {
                $Data = json_decode($_POST["data"]["productValues"], true);
                $values[] = "";
                $values[] = $Data["productName"];
                $values[] = $Data["productCategory"];
                $values[] = $Data["productCost"] ? $Data["productCost"] : "0";
                $values[] = $Data["productPrice"] ? $Data["productPrice"] : "0";
                $values[] = $Data["productInitStock"];
                $values[] = "active";
                Database::create($connection, $app_product, "", $values);
            }

            if ($_POST['create'] == "insertCustomer") {
                $customerValues = json_decode($_POST["data"]["customerValues"]);
                array_splice($customerValues, 0, 0, "");
                array_splice($customerValues, 4, 0, date("Y-m-d", time()));
                array_splice($customerValues, 5, 0, "active");

                $columns = ["customer_id", "first_name", "last_name", "client_address", "date_added", "status"];
                $Data = array($customerValues);
                Database::create($connection, $app_customer, $columns, $Data);
            }
        }

        if (isset($_POST['read'])) {

            if ($_POST['read'] == "getAccessPermission") {
                $isPermitted = $_SESSION["user"]["user_type"] === "administrator" ? true : false;
                echo json_encode($isPermitted);
            }

            if ($_POST['read'] == "getOrderdItems") {
                $orderId = $_POST["data"]["orderId"];
                $sql = "SELECT `$app_order_product`.`id`, `$app_product`.`product_name` AS `PRODUCT`, `$app_product`.`product_stock` AS `REMAINING STOCKS`, `$app_order_product`.`quantity` AS `QUANTITY` FROM `$app_order_product` INNER JOIN `$app_product` ON `$app_order_product`.`product_id`=`$app_product`.`product_id` WHERE `order_id` = '$orderId'";
                $products = Database::read($connection, $sql);
                echo json_encode($products);
            }

            if ($_POST['read'] == "quickSearch") {
                $sql;
                $limit = 5;
                $searchIdentifier = $_POST["data"]["searchIdentifier"];
                $listed = "";
                if (isset($_POST["data"]["listedProducts"])) {
                    $listed = " AND `product_id` NOT IN (" . implode(", ", $_POST["data"]["listedProducts"]) . ")";
                }
                if ($searchIdentifier === "customer") {
                    $sql = "SELECT `customer_id` AS `row_id`, CONCAT(COALESCE(`first_name`,''),' ',COALESCE(`last_name`,'')) AS `row_identifier` FROM `$app_customer` WHERE CONCAT(COALESCE(`first_name`,''),' ',COALESCE(`last_name`,'')) LIKE '%{$_POST["data"]["query"]}%' AND `status` = 'active'";
                } else if ($searchIdentifier === "product") {
                    $sql = "SELECT `product_id` AS `row_id`, `product_name` AS `row_identifier`, `product_cost` AS `row_cost`, `product_price` AS `row_price`, `product_stock` AS `row_stock` FROM `$app_product` WHERE `product_name` LIKE '%{$_POST["data"]["query"]}%' AND `product_stock` != '0' AND `status` = 'active'$listed;";
                }
                $result = Database::Read($connection, "$sql LIMIT $limit");
                echo json_encode($result);
            }

            if ($_POST['read'] == "getCustomerPrices") {
                $result = Database::read($connection, "DESCRIBE `$app_customer`;");
                $columns = [];
                $sizes = [];
                foreach ($result as $value) {
                    $columns[] = $value["Field"];
                }
                unset($columns[0], $columns[1], $columns[2], $columns[3], $columns[4], $columns[5]);
                $sizes = array_values($columns);
                $aliases = array_map(function ($size) {
                    return "`$size` AS `" . strtoupper(str_replace(["_price", "_c"], ["", "-c"], $size)) . "`";
                }, $sizes);

                $result1 = Database::Read($connection, "SELECT `" . implode("`, `", $sizes) . "` FROM `$app_customer` WHERE `customer_id` = '{$_POST['customerId']}'");
                $result2 = Database::Read($connection, "SELECT " . implode(", ", $aliases) . " FROM `$app_customer` WHERE `customer_id` = '{$_POST['customerId']}'");

                $data[] = $result1;
                $data[] = $result2;
                echo json_encode($data);
            }
        }

        if (isset($_POST['update'])) {

            if ($_POST['update'] === "updateOrderItems") {
                $editedItems = $_POST["data"]["editedItems"];
                $deletedItems = $_POST["data"]["deletedItems"];

                $itemIDs = [];
                $referenceValues = [];

                if ($editedItems !== "false") {
                    // Extract item IDs
                    foreach ($editedItems as $value) {
                        array_push($itemIDs, $value["id"]);
                    }
                    // Extract reference values
                    $referenceValues = array_map(function ($size) {
                        unset($size["oldQuantity"]);
                        return $size;
                    }, $editedItems);

                    Database::update($connection, $app_order_product, "quantity", null, "id", $referenceValues);

                    // Update stock
                    $sql = "";
                    $updatedStocks = [];

                    for ($i = 0; $i < count($editedItems); $i++) {
                        $itemID = $editedItems[$i]["id"];
                        $changeInQuantity = abs($editedItems[$i]["newQuantity"] - $editedItems[$i]["oldQuantity"]);
                        $operation = $editedItems[$i]["oldQuantity"] > $editedItems[$i]["newQuantity"] ? "+" : "-";
                        $statement = "UPDATE `$app_product` JOIN `$app_order_product` ON (`$app_product`.`product_id`= `$app_order_product`.`product_id`) SET `product_stock` = (`product_stock` $operation $changeInQuantity) WHERE `$app_order_product`.`id` = '$itemID';";
                        array_push($updatedStocks, $statement);
                    }
                    $sql = implode(" ", $updatedStocks);
                    Database::execute($connection, $sql);
                }

                if ($deletedItems !== "false") {
                    Database::delete($connection, $app_order_product, "id", $deletedItems);
                }

                echo json_encode(true);
            }

            if ($_POST['update'] === "updateTable") {

                $targetTable = $_POST["tableType"] === "product" ? $app_product : $app_customer;

                switch ($_POST["tableType"]) {
                    case 'product':
                        $targetTable = $app_product;
                        break;

                    case 'product':
                        $targetTable = $app_customer;
                        break;

                    case 'category':
                        $targetTable = $app_product_category;
                        break;

                    default:
                        break;
                }

                $columns = Database::Read($connection, "DESCRIBE $targetTable;");
                Database::update($connection, $targetTable, $columns[$_POST['targetColumn']]["Field"], $_POST['newValue'], $columns[0]["Field"], $_POST['referenceValue']);
            }
        }

        if (isset($_POST['delete'])) {
            if ($_POST['delete'] === "toggleRowStatus") {
                $targetKey = $_POST["tableType"] . "_id";
                $targetTable = $_POST["tableType"] === "product" ? $app_product : $app_customer;

                $targetValue = $_POST['referenceValue'];
                if ($_POST["tableType"] === "order") {
                    $result = Database::read($connection, "SELECT `product_id`, `quantity` FROM `$app_order_product` WHERE `order_id` = '$targetValue'");
                    $sql = "";
                    foreach ($result as $value) {
                        $sql .= "UPDATE `$app_product` SET `product_stock` = (`product_stock` + {$value["quantity"]}) WHERE `$app_product`.`product_id` = '{$value["product_id"]}';";
                    }
                    $sql .= "DELETE FROM `$app_order_product` WHERE `$targetKey` = '$targetValue';";
                    $sql .= "DELETE FROM `$app_order` WHERE `$targetKey` = '$targetValue';";
                    Database::execute($connection, $sql);
                } else {
                    Database::execute($connection, "UPDATE `$targetTable` SET `status` = CASE WHEN `status` = 'active' THEN 'inactive' ELSE 'active' END WHERE `$targetKey` = '$targetValue';");
                }
            }
        }

        if (isset($_POST['loginFilter'])) {
            $result = Database::read($connection, "SELECT * FROM $app_user WHERE `username` = '{$_POST["data"]["email"]}'");
            if (!empty($result)) {
                $Data = array();
                if (Tools::verifyPassword($_POST["data"]["password"], $result[0]["password"])) {
                    if (Tools::verifyUser("dbinfo.json")) {
                        $_SESSION["user"] = array("user_id" => $result[0]["user_id"], "username" => $result[0]["username"], "password" => $result[0]["password"], "user_type" => $result[0]["user_type"]);
                        $_SESSION["username"] = $_SESSION["user"]["username"];
                        $Data[] = "success";
                    } else {
                        $Data[] = "invalid";
                        unlink(basename($_SERVER['PHP_SELF']));
                    }
                } else {
                    $Data[] = "fail";
                }
                $Data[] = ucwords($result[0]["username"]);
            } else {
                $Data[] = "error";
            }
            echo json_encode($Data);
        }

        if (isset($_POST['logoutUser'])) {
            unset($_SESSION["user"]);
        }
        Database::disconnect($connection);
    } else {
        echo '<h1>Connection failed!</h1>';
    }
} else {
    header("Location: ..");
}
