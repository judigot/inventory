<?php

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
session_status() == PHP_SESSION_NONE ? session_start() : false;

require_once 'global.php';
require_once 'DatabasePublic.php';
require_once 'Tools.php';

// DB table to use
$table = $app_order;

// Table's primary key
$primaryKey = 'order_id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

$Connection = connect($Host, $DatabaseName, $Username, $Password);

$dateRange = "1";
if (isset($_POST["read"])) {
    $json = json_decode($_POST["data"]["dateRange"]);
    if ($json[0] && $json[1]) {
        $dateRange = "`order_date` BETWEEN '$json[0]' AND '$json[1]'";
    } else if ($json[0] && !$json[1]) {
        $dateRange = "`order_date` >= '$json[0]'";
    } else if (!$json[0] && $json[1]) {
        $dateRange = "`order_date` <= '$json[1]'";
    }
}

$currentWeek = Tools::getCurrentWeekDates();

$where = "$dateRange ORDER BY `order_id` DESC";
$columns = array(
    array(
        'db' => 'order_id', 'dt' => 0,
        'formatter' => function ($d, $row) {
            return "<div data-table-type='product' class='click-search' data-row-id='$d'>$d</div>";
        }
    ),
    array(
        'db' => 'customer_id', 'dt' => 1,
        'formatter' => function ($d, $row) use ($Connection, $app_customer) {
            $Result = read($Connection, "SELECT CONCAT(`$app_customer`.`first_name`, ' ', `$app_customer`.`last_name`) AS customer_name FROM `$app_customer` WHERE `customer_id` = '$d';");
            $customer = "<div class='click-search'>{$Result[0]["customer_name"]}</div>";
            disconnect($Connection);
            return $customer;
        }
    ),
    array(
        'db' => 'order_id', 'dt' => 2,
        'formatter' => function ($d, $row) use ($Connection, $app_order_product, $app_product) {
            $orderId = $row["order_id"];
            $products = read($Connection, "SELECT `$app_order_product`.*, `$app_product`.`product_name` FROM `$app_order_product` INNER JOIN `$app_product` ON `$app_order_product`.`product_id`=`$app_product`.`product_id` WHERE `order_id` = '$orderId'");
            disconnect($Connection);

            $totalItems = 0;
            $totalCost = 0;
            $totalProfit = 0;

            if ($_SESSION["user"]["user_type"] === "administrator") {
                $transaction = "<table class='main-order-table'>
                <thead>
                    <tr>
                        <th>Quantity</th>
                        <th>Product</th>
                        <th>Cost</th>
                        <th>Price</th>
                        <th>Amount</th>
                        <th>Profit</th>
                    </tr>
                </thead>";
            } else {
                $transaction = "<table class='main-order-table'>
                <thead>
                    <tr>
                        <th>Quantity</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>";
            }

            $transaction .= "<tbody>";
            foreach ($products as $value) {
                $transaction .= "<tr>";

                // Quantity
                $transaction .= "<td><span>{$value["quantity"]}</span></td>";

                // Size
                $transaction .= "<td>";
                $transaction .= "<span>" . strtoupper($value["product_name"]);
                $transaction .= "</span>";
                $transaction .= "</td>";

                if ($_SESSION["user"]["user_type"] === "administrator") {
                    // Cost
                    $transaction .= "<td><span>₱ " . Tools::monetize(true, $value["product_cost"]) . "</span></td>";
                }

                // Price
                $transaction .= "<td><span>₱ " . Tools::monetize(true, $value["product_price"]) . "</span></td>";

                // Amount
                $totalItems += $value["quantity"];
                $discount = $value["discount"];
                $itemTotalCost = $value["product_price"] * $value["quantity"];
                $totalCost += $itemTotalCost - $discount;

                $transaction .= "<td><span>₱ " . Tools::monetize(true, $itemTotalCost) . "</span>";
                $transaction .= $discount > 0 ? " (₱ " . Tools::monetize(true, $discount) . " discount)" : false;
                $transaction .= "</td>";

                if ($_SESSION["user"]["user_type"] === "administrator") {
                    // Profit
                    if (true) {
                        $productProfit = ($value["quantity"] * ($value["product_price"] - $value["product_cost"])) - $discount;
                    } else {
                        $productProfit = $value["quantity"] * ($value["product_price"] - $value["product_cost"]);
                    }

                    $totalProfit += $productProfit;
                    $transaction .= "<td><span>₱ " . Tools::monetize(true, $productProfit) . "</span></td>";
                }

                $transaction .= "</tr>";
            }
            if ($_SESSION["user"]["user_type"] === "administrator") {
                // Total profit
                $transaction .= "<tr><td>Total: $totalItems</td><td></td><td></td><td></td><td>Total: <span class='order-price' data-total-profit='$totalProfit' value='" . Tools::monetize(false, $totalCost) . "'>₱ " . Tools::monetize(true, $totalCost) . "</span></td><td>Total: ₱ " . Tools::monetize(true, $totalProfit) . "</td></tr>";
                // Centered total profit
                //    $transaction .= "<tr><td colspan='6'>Total Profit: ₱ " . Tools::monetize(true, $totalProfit) . "</td></tr>";
            } else if ($_SESSION["user"]["user_type"] !== "administrator") {
                $transaction .= "<tr><td>Total: $totalItems</td><td></td><td></td><td>Total: ₱ " . Tools::monetize(true, $totalCost) . "</td></tr>";
            }

            $transaction .= "</tbody>";

            $transaction .= "</table>";
            return $transaction;
        },
    ),
    array(
        'db' => 'order_date', 'dt' => 3,
        'formatter' => function ($d, $row) {
            $orderDate = date('F j, Y', strtotime($d));
            $currentDate = date('F j, Y', strtotime(date("Y-m-d")));
            $displayDate = $orderDate === $currentDate ? "Today" : date('F j, Y', strtotime($d));
            return "<div class='click-search'>$displayDate</div>";
        },
    ),
    array(
        'db' => 'order_date', 'dt' => 4,
        'formatter' => function ($d, $row) {
            // 2000-01-01
            return "<span>" . $d . "</span>";
        }
    ),
    array(
        'db' => 'order_date', 'dt' => 5,
        'formatter' => function ($d, $row) {
            // 2000-1-1
            return "<span>" . date('Y-n-j', strtotime($d)) . "</span>";
        }
    ),
    array(
        'db' => 'order_date', 'dt' => 6,
        'formatter' => function ($d, $row) {
            // 01/01/2000
            return "<span>" . date('m/d/Y', strtotime($d)) . "</span>";
        }
    ),
    array(
        'db' => 'order_date', 'dt' => 7,
        'formatter' => function ($d, $row) {
            // 1/1/2000
            return "<span>" . date('n/j/Y', strtotime($d)) . "</span>";
        }
    ),
    array(
        'db' => 'order_date', 'dt' => 8,
        'formatter' => function ($d, $row) {
            // January 01, 2000
            return "<span>" . date('F d, Y', strtotime($d)) . "</span>";
        }
    ),
    array(
        'db' => 'order_date', 'dt' => 9,
        'formatter' => function ($d, $row) use ($currentWeek) {
            $orderDate = date('Y-m-d', strtotime($d));
            return in_array($orderDate, $currentWeek) ? "This week" : "";
        }
    ),
    array(
        'db' => 'order_date', 'dt' => 10,
        'formatter' => function ($d, $row) use ($currentWeek) {
            return "orderid='{$row["order_id"]}'";
        }
    ),
);

// SQL server connection information
$sql_details = array(
    'user' => $Username,
    'pass' => $Password,
    'db' => $DatabaseName,
    'host' => $Host,
);

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require 'ssp.class.php';

echo json_encode(
    SSP::complex($_POST, $sql_details, $table, $primaryKey, $columns, $where)
);
